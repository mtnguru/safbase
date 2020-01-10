<?php
/**
* @file
* Display top Navigation menu for groups depending on the URL.
*/


/**
 * Displays children pages as a block
 */

namespace Drupal\az_groups;
use Drupal\Core\Path;
use Drupal\Core\Menu;

/**
 * Provides a 'Next Previous' block.
 *
 * @Block(
 *   id = "az_top_nav_block",
 *   admin_label = @Translation("AZ Top Nav Block"),
 *   category = @Translation("Atomizer")
 * )
 */
class AzGroupConfig {

  // @TODO move this into database with config form
  protected static $sites = [
    'em' => [
      'hosts' => [
        'etherealmatters.org',
        'em',
      ],
      'name' => 'Ethereal Matters',
      'theme' => 'ethereal-matters',
      'menu' => 'ethereal-matters',
      'class' => 'em',
      'icon' => 'public://icons/em_icon.gif',
    ],
    'sam' => [
      'hosts' => [
        'master-7rqtwti-ocf5zhiadchks.us.platform.sh',
        'structuredatom.org',
        'atom',
      ],
      'name' => 'Structured Atom Model',
      'theme' => 'structured-atom-model',
      'menu' => 'structured-atom-model',
      'class' => 'sam',
      'icon' => 'public://icons/carbon_40x40.gif',
    ],
    'geo' => [
      'hosts' => [
        'eugeology.rocks',
        'eg',
      ],
      'name' => 'EU Geology',
      'theme' => 'eu-geology',
      'menu' => 'eu-geology',
      'class' => 'geo',
      'icon' => 'public://icons/valles_marineris.jpg',
    ],
  ];

  static public function getConfig($host) {
    $host = \Drupal::request()->getHttpHost();
    foreach (self::$sites as $sitename => $site) {
      foreach ($site['hosts'] as $hostname) {
        if ($host == $hostname) {
          return  $site;
        }
      }
    }
    return null;
  }
}

