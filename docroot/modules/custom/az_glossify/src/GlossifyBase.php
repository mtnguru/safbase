<?php

namespace Drupal\az_glossify;

use Drupal\Component\Utility\Html;
use Drupal\Core\Url;
use DOMXPath;
use Drupal\Component\Utility\Unicode;

/**
 * Base implementation of tooltip filter type plugin.
 */
class GlossifyBase {

  /**
   * Convert terms in text to links.
   *
   * @param string $text
   *   The HTML text upon which the filter is acting.
   * @param array $terms
   *   The terms (array) to be replaced with links.
   *   structure: [$termname_lower => [
   *     ['id' => $id],
   *     ['name' => $term],
   *     ['name_norm' => $termname_lower],
   *     ['tip' => $tooltip],
   *   ]]
   *
   * @return string
   *   The original HTML with the term string replaced by links.
   */
  static public function parseTooltipMatch($text, $terms, $case_sensitivity, $first_only, $displaytype, $urlpattern, &$matched) {

    $text = preg_replace('/<a.*?class="taxonomy\-tooltip.*?>(.*?)<\/a.*?>/', '${1}', $text);
    // Create dom document.
    $html_dom = Html::load($text);
    $xpath = new DOMXPath($html_dom);
    $pattern_parts = [];
    $matched = [];

    // Transform terms into normalized search pattern.
    foreach ($terms as $term) {
      $term_norm = preg_replace('/\s+/', ' ', preg_quote(trim($term->name_norm)));
      $term_norm = preg_replace('#/#', '\/', $term_norm);
      $pattern_parts[] = preg_replace('/ /', '\\s+', $term_norm);
    }
    $pattern  = '/\b(' . implode('|', $pattern_parts) . ')\b/';
    $pattern_link  = '/>(' . implode('|', $pattern_parts) . ')</';
    if (!$case_sensitivity) {
      $pattern .= 'i';
    }

    // Process HTML.
    $text_nodes = $xpath->query('//text()[not(ancestor::a)]');
    foreach ($text_nodes as $original_node) {
      $text = $original_node->nodeValue;
      $hitcount = preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);

      if ($hitcount > 0) {

        $offset = 0;
        $parent = $original_node->parentNode;
        $refnode = $original_node->nextSibling;
        
        $current_path = \Drupal::service('path.current')->getPath();
        $parent->removeChild($original_node);
        foreach ($matches[0] as $i => $match) {
          $term_txt = $match[0];
          $term_pos = $match[1];
          $term_norm = preg_replace('/\s+/', ' ', $term_txt);
          $terms_key = $case_sensitivity ? $term_txt : strtolower($term_txt);
          $term = $terms[$terms_key];

          // Insert any text before the term instance.
          $prefix = substr($text, $offset, $term_pos - $offset);
          $parent->insertBefore($html_dom->createTextNode($prefix), $refnode);

          $dom_fragment = $html_dom->createDocumentFragment();

          if ($current_path == str_replace('[id]', $term->id, $urlpattern)) {
            // Reinsert the found match if whe are on the page
            // this match points to.
            $dom_fragment->appendXML($term_txt);
          }
          elseif ($first_only && !empty($matched[$case_sensitivity ? $term_txt : strtolower($term_txt)])) {
            // Reinsert the found match if only first match must be parsed.
            $dom_fragment->appendXML($term_txt);
          }
          else {
            $tip = '';
            if ($term->description != null) {
              $tip = self::sanitizeTip($term->tooltip);
              $tipurl = str_replace('[id]', $term->id, $urlpattern);
              $word = '<a href="' . $tipurl . '" class="taxonomy-tooltip-link" title="' . $tip . '">' . $term_txt . '</a>';
            }
            else {
              $tip = self::sanitizeTip($term->tooltip);
              $word = '<abbr title="' . $tip . '" class="taxonomy-tooltip-tip">' . $term_txt . '</abbr>';
            }
            if (!$word) {
              // Dont let $word be empty, else appendXML method fails
              // with warning.
              $word = $term_txt;
            }
            $dom_fragment->appendXML($word);
            $matched[$terms_key] = $term->id;
          }
          $parent->insertBefore($dom_fragment, $refnode);

          $offset = $term_pos + strlen($term_txt);

          // Last match, append remaining text.
          if ($i == $hitcount - 1) {
            $suffix = substr($text, $offset);
            $parent->insertBefore($html_dom->createTextNode($suffix), $refnode);
          }
        }
      }
    }
    $text = Html::serialize($html_dom);

    return $text;
  }

  /**
   * Cleanup and truncate tip text.
   */
  static private function sanitizeTip($tip) {

    // Get rid of HTML.
    $tip = strip_tags($tip);

    // Maximise tooltip text length.
    $tip = Unicode::truncate($tip, 300, TRUE, TRUE);

    return $tip;
  }

}
