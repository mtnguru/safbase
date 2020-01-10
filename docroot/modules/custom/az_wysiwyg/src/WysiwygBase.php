<?php

namespace Drupal\az_wysiwyg;

use Drupal\Component\Utility\Html;
use DOMXPath;
use Drupal\Component\Utility\Unicode;

/**
 * Base implementation of tooltip filter type plugin.
 */
class WysiwygBase {


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

  static public function loadGlossary() {
    $glossary = &drupal_static(__FUNCTION__);
    if (isset($glossary)) {
      return $glossary;
    }
    $glossary = [];
    // DB Query for all glossary terms.
    $query = \Drupal::database()->select('node_field_data', 'nfd');
    $query->addfield('nfd', 'nid');
    $query->addfield('nfd', 'title');
    $query->condition('nfd.type', 'glossary');

    // Join in the tooltip text field.
    $query->join('node__field_tooltip', 'nft', 'nft.entity_id = nfd.nid');
    $query->addfield('nft', 'field_tooltip_value', 'tooltip');

    $results = $query->execute()->fetchAllAssoc('title');
    // Build terms array.
    foreach ($results as $result) {
      $result->url = \Drupal::service('path.alias_manager')->getAliasByPath('/node/' . $result->nid);
      $glossary[strtolower($result->title)] = $result;
    }
    return $glossary;
  }

  /**
   * Add topics to node
   *
   * @param $node
   * @param $matched
   */
  static public function addTopicsToNode(&$node, $topics, $saveNode = false) {
    if ($node->hasField('field_topics')) {
      $nids = $node->field_topics->getValue();
      $nfound = 0;
      foreach ($topics as $name => $topic) {
        if (isset($topic->in_text)) {
          $found = false;
          foreach ($nids as $i => $val) {
            if ($val['target_id'] == $topic->nid) {
              $found = TRUE;
            }
          }
          if (!$found) {
            $nfound++;
            $node->field_topics->appendItem($topic->nid);
          }
        }
      }

      if ($saveNode && $nfound) {
        $node->save();
      }
    }
  }

  /**
   * Find the topics in a block of text and mark ones that are found.
   *
   * @param $text
   * @param $glossary
   */
  static public function extractTerms($text, &$glossary) {
    $topicReg = '/&lt;topic(.*?)&gt;(.*?)&lt;\/topic&gt;/';
//  $topicReg = '/&lt;([a-z]+) *(.*?)&gt;(.*?)&lt;\/([a-z]+?)&gt;/';

    // Replace &nbsp; characters that do not have a space before or after them with a space.
    // ckeditor likes to leave a lot of these in the text.
    // This makes matching topics terms more difficult.
    $text = preg_replace('/([^ ])\&nbsp;([^ ])/', '$1 $2', $text);

    $hitcount = preg_match_all($topicReg, $text, $matches, PREG_OFFSET_CAPTURE);

    foreach ($matches[0] as $key => $match) {
      $name = strtolower($matches[2][$key][0]);
      if (!empty($matches[1][$key][0])) {
        if (preg_match('/name=\"*([a-z ]+)\"*/', $matches[1][$key][0], $attributes)) {
          $name = strtolower($attributes[1]);
        }
      }
      $found = false;
      if (empty($glossary[$name])) {
        $len = strlen($name);
        if ($name[$len - 1] == 's') {
          $sname = substr($name, 0, $len - 1);
          if (!empty($glossary[$sname])) {
            $glossary[$sname]->in_text = true;
            $found = true;
          }
        }
      } else {
        $glossary[$name]->in_text = true;
        $found = true;
      }

      if (!$found) {
        \Drupal::logger('my_module')->notice(
          'Topic not found: ' . $name . '  Page: ' . \Drupal::service('path.current')->getPath());
      }
    }
  }
}
