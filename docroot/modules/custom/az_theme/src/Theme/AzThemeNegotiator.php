<?php

/**
 * @file
 * Contains \Drupal\az_theme\Theme\AzThemeswitcherNegotiator.
 */
 
namespace Drupal\az_theme\Theme;
 
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
 
class AzThemeNegotiator implements ThemeNegotiatorInterface {

  // If this is the user edit form then apply the sa_neato theme.
  public function applies(RouteMatchInterface $route_match) {
    return ($route_match->getRouteName() == 'entity.user.edit_form');
  }
 
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    return 'em_neato';
  }
}
