<?php

namespace Drupal\az_wysiwyg\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginInterface;
use Drupal\ckeditor\CKEditorPluginButtonsInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "CodeButton" plugin.
 *
 * @CKEditorPlugin(
 *   id = "az_wysiwyg",
 *   label = @Translation("FootnotesButton")
 * )
 */
class Footnotes extends PluginBase implements CKEditorPluginInterface, CKEditorPluginButtonsInterface {

  /**
   * Implements CKEditorPluginInterface::getDependencies().
   */
  public function getDependencies(Editor $editor) {
    return array('fakeobjects');
  }

  /**
   * Implements CKEditorPluginInterface::getLibraries().
   */
  public function getLibraries(Editor $editor) {
    return array();
  }

  /**
   * Implements CKEditorPluginInterface::isInternal().
   */
  public function isInternal() {
    return FALSE;
  }

  /**
   * Implements CKEditorPluginInterface::getFile().
   */
  public function getFile() {
    return drupal_get_path('module', 'az_wysiwyg') . '/assets/js/ckeditor/plugin.js';
  }

  /**
   * Implements CKEditorPluginButtonsInterface::getButtons().
   */
  public function getButtons() {
    return array(
      'az_wysiwyg' => array(
        'label' => t('Footnotes'),
        'image' => drupal_get_path('module', 'az_wysiwyg') . '/assets/js/ckeditor/icons/az_wysiwyg.png',
      ),
    );
  }

  /**
   * Implements CKEditorPluginInterface::getConfig().
   */
  public function getConfig(Editor $editor) {
    return array();
  }

}
