<?php
/**
* @file
* Display top Navigation menu for groups depending on the URL.
*/


/**
 * Displays children pages as a block
 */

namespace Drupal\az_nav_menu\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Path;
use Drupal\Core\Menu;

/**
 * Provides a 'Next Previous' block.
 *
 * @Block(
 *   id = "top_nav_block",
 *   admin_label = @Translation("Top Navigation Block"),
 *   category = @Translation("AZ Nav Menu")
 * )
 */
class TopNavBlock extends BlockBase {

  // @TODO move this into database with config form

  public function build() {
    $menu_tree_service = \Drupal::service('menu.link_tree');

    // Build the typical default set of menu tree parameters.
    $parameters = new \Drupal\Core\Menu\MenuTreeParameters();
    $parameters->setMaxDepth(3);
    $parameters->setMinDepth(1);

    // Load the tree based on this set of parameters.
    $tree = $menu_tree_service->load('aureon-main', $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkNodeAccess'],
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $menu_tree_service->transform($tree, $manipulators);
    $topMenu = $menu_tree_service->build($tree);

    $build = [
      '#type' => 'container',
      '#attributes' => ['class' => ['az-top', 'az-nav']],
      'top_menu' => $topMenu,
    ];

    /*
    $uid = \Drupal::currentUser()->id();
    if ($uid > 0) {

      $build['account_menu'] = [
        '#type' => 'theme',
        '#theme' => 'user_popup_menu',
        '#uid' => \Drupal::currentUser()->id(),
      ];
    } */

    return $build;
  }

  public function getCacheMaxAge() {
    return 0;
  }
}

