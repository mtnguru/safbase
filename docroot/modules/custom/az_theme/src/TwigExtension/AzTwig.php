<?php

namespace Drupal\az_theme\TwigExtension;
use Twig_Extension;
use Drupal\Core\Render\Element;
use Drupal\Component\Utility\Html;

/**
 * Helpers for Atomizer twig templates.
 */
class AzTwig extends Twig_Extension {

  /**
   * Generates a list of all Twig filters that this extension defines.
   */
  public function getFilters() {
    return [
      new \Twig_SimpleFilter('children', array($this, 'elementChildren')),
      new \Twig_SimpleFilter('htmlid', array($this, 'htmlId')),
    ];
  }

  /**
   * Gets a unique identifier for this Twig extension.
   */
  public function getName() {
    return 'az_theme.twig_extension';
  }

  /**
   * Get element's children.
   */
  public static function elementChildren($renderObject) {
    if (!is_array($renderObject)) {
      return FALSE;
    }
    return Element::children($renderObject);
  }

  /**
   * Get the unique htmlId from a string.
   */
  public static function htmlId($string) {
    if (!is_string($string)) {
      return FALSE;
    }
    return Html::getUniqueId($string);
  }

}
