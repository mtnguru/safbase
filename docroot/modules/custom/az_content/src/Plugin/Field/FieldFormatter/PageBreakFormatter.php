<?php

/**
 * @file
 * Contains \Drupal\az_content\Plugin\Field\FieldFormatter\PageBreakFormatter 
 */

namespace Drupal\az_content\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'atom' formatter.
 *
 * @FieldFormatter(
 *   id = "page_break_formatter",
 *   label = @Translation("Page Break embed"),
 *   description = @Translation("Insert a div which clears floats thereby creating a break in the page flow."),
 *   field_types = {
 *     "string", "string_long"
 *   }
 * )
 */
class PageBreakFormatter extends FormatterBase {

//private static $fileList;

  /**
   * {@inheritdoc}
   */
/*public static function defaultSettings() {
    return array(
      'atomizer' => 'atom_builder',
      'atomizer_class' => '',
    );
  } */

  /**
   * {@inheritdoc}
   */
/*public function settingsForm(array $form, FormStateInterface $form_state) {
    $fileList = AtomizerFiles::createFileList(drupal_get_path('module', 'atomizer') . '/config/atomizers', '/\.yml/');
    $atomizer = $this->getSetting('atomizer');
    $atomizer_class = $this->getSetting('atomizer_class');

    $elements['atomizer'] = array(
      '#type' => 'select',
      '#options' => $fileList,
      '#title' => t('Atomizer mode'),
      '#default_value' => ($atomizer) ? $atomizer : $this->defaultSettings()['atomizer'],
      '#required' => TRUE,
    );
    $elements['atomizer_class'] = array(
      '#type' => 'textfield',
      '#title' => t('Class'),
      '#default_value' => ($atomizer_class) ? $atomizer_class : $this->defaultSettings()['atomizer_class'],
      '#required' => TRUE,
    );

    return $elements;
  } */

  /**
   * {@inheritdoc}
   */
/*public function settingsSummary() {
    $summary = array();
    $fileList = AtomizerFiles::createFileList(drupal_get_path('module', 'atomizer') . '/config/atomizers', '/\.yml/');

    $atomizer = $this->getSetting('atomizer');
    $atomizer = ($atomizer) ? $atomizer : defaultSettings()['atomizer'];
    $summary[] = t('Atomizer: @atomizer', array('@atomizer' => $fileList[$atomizer]));

    $atomizer_class = $this->getSetting('atomizer_class');
    $summary[] = t('Class: @class', array('@class' => $atomizer_class));

    return $summary;
  } */


  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = array();

    $elements['page-break'] = [
    ];

    return $elements;
  }

}
