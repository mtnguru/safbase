<?php
/**
* @file
* Display top Navigation menu for groups depending on the URL.
*/


/**
 * Displays children pages as a block
 */

namespace Drupal\az_groups;

/**
 * Provides a 'Next Previous' block.
 *
 * @Block(
 *   id = "az_top_nav_block",
 *   admin_label = @Translation("AZ Top Nav Block"),
 *   category = @Translation("Atomizer")
 * )
 */
class AzGroupQuery {

  static public function nodeInGroup($node) {
    $query = \Drupal::database()->select('group_content_field_data', 'gcfd');
    $query->fields('gcfd', ['gid', 'entity_id', 'type']);
    $query->condition('gcfd.entity_id', $node->id());
    $query->condition('gcfd.type', 'theories-group_node-' . $node->getType());
    $results = $query->execute()->fetchAll();
    return (count($results)) ? $results[0]->gid : null;
  }

//static public function userInGroup($user) {
//  $query = \Drupal::database()->select('group_content_field_data', 'gcfd');
//  $query->fields('gcfd', ['gid', 'entity_id', 'type']);
//  $query->condition('gcfd.entity_id', $user->id());
//  $query->condition('gcfd.type', 'theories-group_node-' . $node->getType());
//  $results = $query->execute()->fetchAll();
//  return (count($results)) ? $results[0]->gid : null;
//}

  static public function findGroups($set) {
    $query = \Drupal::database()->select('groups_field_data', 'gfd');

    ////////// Set the output fields
    if (isset($set['fields'])) {
      foreach ($set['fields'] as $name => $fields) {
        $query->fields(($name == 'null') ? null : $name, $fields);
      }
    } else {
      $query->fields('gfd', ['gid']);
    }

    $query->join('group_content_field_data', 'gcfd', 'gcfd.gid = gfd.id');
    //$query->groupBy('gcfd.gid');

    // Query for groups this user is a member of
    if (isset($set['member'])) {
      $query->condition('gcfd.entity_id', $set['member'], (is_array($set['member'])) ? 'IN' : '=');
      $query->condition('gcfd.type', 'theories-group_membership');
    }

    // Group roles
    if (isset($set['roles'])) {
      $query->leftJoin('group_content__group_roles', 'gcgr', 'gcfd.id = gcgr.entity_id');
      $query->condition('gcgr.bundle', 'theories-group_membership', '=');
      $query->condition('gcgr.group_roles_target_id', $set['roles'], (is_array($set['roles'])) ? 'IN' : '=');
    }

    if (isset($set['sort'])) {
      switch ($set['sort']) {
        case 'label':
          $query->orderBy('gfd.label', 'ASC');
      }
    }

    $results = $query->execute()->fetchAllAssoc('id');

    return $results;
  }
}

