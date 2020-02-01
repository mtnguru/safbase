<?php
/**
* @file
* Display top Navigation menu for groups depending on the URL.
*/


/**
 * Displays children pages as a block
 */

namespace Drupal\az_domain\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Path;
use Drupal\Core\Menu;

/**
 * Provides a 'Next Previous' block.
 *
 * @Block(
 *   id = "sa_top_nav_block",
 *   admin_label = @Translation("Top Navigation Menu"),
 *   category = @Translation("AZ Domain")
 * )
 */
class SaTopNavBlock extends BlockBase {

  // @TODO move this into database with config form

  public function build() {
    $menu_tree_service = \Drupal::service('menu.link_tree');

    // Build the typical default set of menu tree parameters.
    $parameters = new \Drupal\Core\Menu\MenuTreeParameters();
    $parameters->setMaxDepth(3);
    $parameters->setMinDepth(1);

    // Load the tree based on this set of parameters.
    $tree = $menu_tree_service->load('top-nav-sa', $parameters);
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

      $build['account_menu'] = [
        '#type' => 'theme',
        '#theme' => 'user_popup_menu',
        '#uid' => \Drupal::currentUser()->id(),
      ];

      /*
      $items[] = ['#markup' => '<a href="/dashboard">My Dashboard</a>'];
      $items[] = ['#markup' => '<a href="/user">My Profile Page</a><br>'];
      $items[] = ['#markup' => 'Edit Account Settings'];
      $items[] = ['#markup' => '&nbsp;&nbsp;<a href="/user/' . \Drupal::currentUser()->id() . '/edit?display=account">Identification</a>'];
      $items[] = ['#markup' => '&nbsp;&nbsp;<a href="/user/' . \Drupal::currentUser()->id() . '/edit?display=email_notifications">Email Notifications</a>'];
      $items[] = ['#markup' => '&nbsp;&nbsp;<a href="/user/' . \Drupal::currentUser()->id() . '/edit?display=social_media_and_website">Social Media Links</a>'];
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
      ]; */
    }
    return $build;
  }
}

