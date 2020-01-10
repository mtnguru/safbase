<?php

namespace Drupal\az_book;

use Drupal\book\BookManager;

// Override BookManager service.
//   - Include unpublished books in book outline edit form.

class AzBookManager extends BookManager {
 
  /**
   * {@inheritdoc}
   *
   * Override core and Load books that are not published.
   */
  public function bookTreeCheckAccess(&$tree, $node_links = []) {
    if ($node_links) {
      $nids = array_keys($node_links);
      if (\Drupal::routeMatch()->getRouteName() != 'book.admin_edit') {
        // @todo Extract that into its own method.

        // @todo This should be actually filtering on the desired node status
        //   field language and just fall back to the default language.
        $query = \Drupal::entityQuery('node')
          ->condition('nid', $nids, 'IN');

        $route = \Drupal::routeMatch()->getRouteName();
        $query->condition('status', 1);
        $nids = $query->execute();
      }

      foreach ($nids as $nid) {
        foreach ($node_links[$nid] as $mlid => $link) {
          $node_links[$nid][$mlid]['access'] = TRUE;
        }
      }
    }
    $this->doBookTreeCheckAccess($tree);
  }
}


