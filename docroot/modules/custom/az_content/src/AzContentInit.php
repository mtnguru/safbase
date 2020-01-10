<?php

namespace Drupal\az_content;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Xss;

/**
 * Class AzContentInit.
 *
 * @package Drupal\az_content
 */
class AzContentInit {

  static public function start($set, &$element) {

    $element['#attached']['drupalSettings']['azcontent'] = [
      $set['id'] => $set,
    ];

    $loaded = &drupal_static('contentLoaded', false);
    if (!$loaded) {
      $loaded = true;
      $element['#attached']['library'] = 'az_content/az-content';
    }
  }
}

