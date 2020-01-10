<?php
/**
* @file
* Display top Navigation menu for groups depending on the URL.
*/


/**
 * Displays children pages as a block
 */

namespace Drupal\az_content\Plugin\Block;
use Drupal\Core\Block\BlockBase;
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
class AzTopNavBlock extends BlockBase {

  // @TODO move this into database with config form

  public function build() {
    $menu_tree_service = \Drupal::service('menu.link_tree');

    // Build the typical default set of menu tree parameters.
    $parameters = new \Drupal\Core\Menu\MenuTreeParameters();
    $parameters->setMaxDepth(3);
    $parameters->setMinDepth(1);

    // Load the tree based on this set of parameters.
    $tree = $menu_tree_service->load('top', $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkNodeAccess'],
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $menu_tree_service->transform($tree, $manipulators);
    $topMenu = $menu_tree_service->build($tree);

    $build = [
      '#type' => 'container',
      '#attributes' => ['class' => ['az-top']],
      'top_menu' => $topMenu,
    ];

    $uid = \Drupal::currentUser()->id();
    if ($uid > 0) {
      $items[] = ['#markup' => '<a href="/dashboard">View My Dashboard</a>'];
      $items[] = ['#markup' => '<a href="/user">View My Profile Page</a>'];
      $items[] = ['#markup' => '<a href="/user/' . \Drupal::currentUser()->id() . '/edit">Edit Account Settings</a>'];
      $items[] = ['#markup' => '<a href="/user/logout">Logout</a>'];
      // Create the top menu
      $build['account_menu'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['user-menu']],
        'menu' => [
          '#theme' => 'item_list',
          '#attributes' => ['class' => ['menu', 'popup']],
          '#items' => $items,
        ],
      ];
    }
    return $build;
  }
}

