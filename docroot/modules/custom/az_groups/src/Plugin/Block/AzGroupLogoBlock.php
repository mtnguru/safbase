<?php
/**
* @file
* Display top Navigation menu for groups depending on the URL.
*/


/**
 * Displays children pages as a block
 */

namespace Drupal\az_groups\Plugin\Block;
use Drupal\az_groups\AzGroupConfig;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Path;
use Drupal\Core\Menu;

/**
 * Provides a 'Next Previous' block.
 *
 * @Block(
 *   id = "az_group_logo_block",
 *   admin_label = @Translation("AZ Group Logo Block"),
 *   category = @Translation("Atomizer")
 * )
 */
class AzGroupLogoBlock extends BlockBase {

  public function build() {
    $host = \Drupal::request()->getHttpHost();
    $site = AzGroupConfig::getConfig($host);

    $variables = array(
      'style_name' => 'thumbnail',
      'uri' => $site['icon'],
    );

    $variables['width'] = $variables['height'] = NULL;

    $build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [$site['class']],
      ],
      'logo' => [
        '#theme' => 'image_style',
        '#width' => null,
        '#height' => null,
        '#style_name' => $variables['style_name'],
        '#uri' => $variables['uri'],
      ],
      'name' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['name'],
        ],
        'text'=> [
          '#markup' => $site['name'],
        ],
      ],
    ];
    return $build;
  }
}

