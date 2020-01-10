<?php

namespace Drupal\az_em\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;

/**
 * Class SwitchThemes.
 *
 * @package Drupal\landingpage
 */
class SwitchThemes implements ThemeNegotiatorInterface {
  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $route = \Drupal::routeMatch()->getRouteObject();
    $is_admin = \Drupal::service('router.admin_context')->isAdminRoute($route);
    if ($is_admin) {
      return FALSE;
    }
    $host = \Drupal::request()->getHost();
    if ($host == 'em') {
      return TRUE;
    } else {
      return FALSE;
    }


//  if (...) {
      return TRUE;
//  }
//  return FALSE;    	
  }
 
  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    return 'em_neato';
  }
}

