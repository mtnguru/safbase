<?php

namespace Drupal\az_wysiwyg\Plugin\Filter;

// Base class for the filter.
use Drupal\az_wysiwyg\WysiwygBase;
use Drupal\Component\Utility\Unicode;
use Drupal\filter\Plugin\FilterBase;

// Necessary for settings forms.
use Drupal\Core\Form\FormStateInterface;

// Necessary for result of process().
use Drupal\filter\FilterProcessResult;

use Drupal\Component\Utility\Xss;

/**
 * Provides a base filter for Special Filters in ckeditor.
 *
 * @Filter(
 *   id = "filter_az_wysiwyg",
 *   module = "az_wysiwyg",
 *   title = @Translation("Wysiwyg filter"),
 *   description = @Translation("You can insert footnote directly into texts."),
 *   type = \Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 *   cache = FALSE,
 *   settings = {
 *     "footnote_collapse" = FALSE
 *   },
 *   weight = 0
 * )
 */
class WysiwygFilter extends FilterBase {

  /**
   * Object with configuration for az_wysiwyg.
   *
   * @var object
   */
  protected $config;

  /**
   * Object with configuration for az_wysiwyg, where we need editable..
   *
   * @var object
   */
  protected $configEditable;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->config = \Drupal::config('az_wysiwyg.settings');
    $this->configEditable = \Drupal::configFactory()
      ->getEditable('az_wysiwyg.settings');
  }

  /**
   * Get the tips for the filter.
   *
   * @param bool $long
   *   If get the long or short tip.
   *
   * @return string
   *   The tip to show for the user.
   */
  public function tips($long = FALSE) {
    if ($long) {
      return t('You can insert special markup directly into texts such as footnotes, topics and page breaks.');
    }
    else {
      return t('Use [fn]...[/fn] (or &lt;fn&gt;...&lt;/fn&gt;) to insert automatically numbered footnotes.');
    }
  }

  private function processText($text, &$glossary, &$footnotes) {

    // If az_wysiwyg is null then leave text unchanged.  az_wysiwyg is set in the group and node preprocess functions.
    $az_wysiwyg = &drupal_static('az_wysiwyg', null);
    if (!isset($az_wysiwyg)) return $text;

    $view_mode = $az_wysiwyg['view_mode'];
    $deleteMarkup = (!isset($view_mode) || ($view_mode != 'main_content' && $view_mode != 'full')) ? true : false;

    // Replace with a space any &nbsp; characters do not have a space before or after them.
    // 'For&nbsp;example' => 'for example'
    // Ckeditor likes to leave these everywhere.
    // @TODO - there are other anomolies with &nbsp; this code should also address.
    $text = preg_replace('/([^ ])\&nbsp;([^ ])/', '$1 $2', $text);

    // Regex finds occurences of <.*>.*<.*>
    $reg = '&lt;([a-z]+) *(.*?)&gt;(.*?)&lt;\/([a-z]+?)&gt;';
    $pagebreakReg = '&lt;(pb)&gt;';
    $pattern = '/' . $pagebreakReg . '|' . $reg . '/';
    $hitcount = preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);
    // $matches[0] - full string
    // $matches[1] - opening tag
    // $matches[2] - attributes
    // $matches[3] - text
    // $matches[4] - closing tag

    if ($hitcount == 0) return $text;

    $c = 0;
    $ntext = null;

    foreach ($matches[0] as $key => $match) {
      $ntext .= substr($text, $c, $match[1] - $c);
      $c = $match[1] + strlen($match[0]);

      if (!empty($matches[1][$key][0]) && $matches[1][$key][0] == 'pb') {
        // This was put here to implement page breaks, leave for now.
      }
      else {
        $type = $matches[2][$key][0];
        switch ($type) {
          case 'topic':
          case 'glossary':
            $topic = null;
            $name = strtolower($matches[4][$key][0]);
            if (!empty($matches[3][$key][0])) {
              if (preg_match('/name=\"*([a-z ]+)\"*/', $matches[3][$key][0], $attributes)) {
                $name = strtolower($attributes[1]);
              }
            }

            if ($deleteMarkup ) {
              $ntext .= $matches[4][$key][0];
            }
            else {
              if (!empty($glossary[$name])) {
                $topic = $glossary[$name];
              }
              else {
                $sname =substr($name, 0, -1);   // Strip off the last character - 's'?
                if (!empty($glossary[$sname])) {
                  $name = $sname;
                  $topic = $glossary[$name];
                }
                else {
                  $ntext .= $matches[4][$key][0];

                  // Log that we didn't find this term.
                  $request = \Drupal::request();
                  if ($route = $request->attributes->get(\Symfony\Cmf\Component\Routing\RouteObjectInterface::ROUTE_OBJECT)) {
                    $title = \Drupal::service('title_resolver')->getTitle($request, $route);
                  } else {
                    $title = 'Wassup doc';
                  }
                  $alias = \Drupal::service('path.alias_manager')->getAliasByPath(\Drupal::service('path.current')->getPath());
                  \Drupal::logger('my_module')->notice('Topic not found: ' . $name . '  Title: ' . $title . '  Path: ' .  $alias);
                }
              }
            }

            if ($topic) {
              $glossary[$name]->in_text = true;
              $tip = $this->sanitizeTip($topic->tooltip);
              $ntext .= '<a href="' . $topic->url . '" ' .
                'class="taxonomy-tooltip-link az-' . $type . '" ' .
                'title="' . $tip . '">' .
                $matches[4][$key][0] .
                '</a>';
            }

            break;

          case 'footnote':
            if (!$deleteMarkup) {
              // Create a sanitized version of $text that is suitable for using as HTML
              // attribute value. (In particular, as the title attribute to the footnote
              // link).
              $number = count($footnotes) + 1;
              $allowed_tags = array();
              $text_clean = Xss::filter($matches[4][$key][0], $allowed_tags);
              // HTML attribute cannot contain quotes.
              $text_clean = str_replace('"', "&quot;", $text_clean);
              $text_clean = str_replace("\n", " ", $text_clean);
              $text_clean = str_replace("\r", "", $text_clean);
              $randstr = $this->randstr();

              $fn = array(
                'value' => $number,
                'text' => $matches[4][$key][0],
                'text_clean' => $text_clean,
                'fn_id' => 'footnote_' . $number . '_' . $randstr,
                'ref_id' => 'footnoteref_' . $number . '_' . $randstr,
              );
              $footnotes[] = $fn;
              $build = [
                '#theme' => 'az_footnote_link',
                'fn' => $fn,
              ];
              // Replace text with link to footnote, add footnote to array.  Position in array determines index.
              $ntext .= \Drupal::service('renderer')->render($build, FALSE);
            }
            break;
        }
      }
    }

    if (isset($az_wysiwyg['node'])) {
      WysiwygBase::addTopicsToNode($az_wysiwyg['node'], $glossary, true);
    }

    return ($ntext) ? $ntext . substr($text, $c) : $text;
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $glossary = WysiwygBase::loadGlossary();
    $footnotes = [];
    $text = $this->processText($text, $glossary, $footnotes);

    // Create the footnote footer, render it and append it to the text.
    if (count($footnotes)) {
      $footer = array(
        '#theme' => 'az_footnote_list',
        '#footnotes' => $footnotes,
      );
      $text .= "\n\n" . \Drupal::service('renderer')->render($footer, FALSE);
    }

    $result = new FilterProcessResult($text);
//  $result->setAttachments([
//    'library' => [
//      'az_wysiwyg/footnotes',
//    ]
//  ]);
    return $result;
  }

  /**
   * Create the settings form for the filter.
   *
   * @param array $form
   *   A minimally prepopulated form array.
   * @param FormStateInterface $form_state
   *   The state of the (entire) configuration form.
   *
   * @return array
   *   The $form array with additional form elements for the settings of
   *   this filter. The submitted form values should match $this->settings.
   *
   * @todo Add validation of submited form values, it already exists for
   *       drupal 7, must update it only.
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $settings['footnote_collapse'] = array(
      '#type' => 'checkbox',
      '#title' => t('Collapse footnote with identical content'),
      '#default_value' => $this->settings['footnote_collapse'],
      '#description' => t('If two footnotes have the exact same content, they will be collapsed into one as if using the same value="" attribute.'),
    );
    return $settings;
  }

  /**
   * Cleanup and truncate tip text.
   */
  private function sanitizeTip($tip) {

    // Get rid of HTML.
    $tip = strip_tags($tip);

    // Maximise tooltip text length.
    $tip = Unicode::truncate($tip, 300, TRUE, TRUE);

    return $tip;
  }

  /**
   * Helper function to return a random text string.
   *
   * @return string
   *   Random (lowercase) alphanumeric string.
   */
  private function randstr() {
    $chars = "abcdefghijklmnopqrstuwxyz1234567890";
    $str = "";

    // Seeding with srand() not neccessary in modern PHP versions.
    for ($i = 0; $i < 7; $i++) {
      $n = rand(0, strlen($chars) - 1);
      $str .= substr($chars, $n, 1);
    }
    return $str;
  }


}
