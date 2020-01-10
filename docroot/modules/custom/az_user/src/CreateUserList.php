<?php

namespace Drupal\az_user;

class CreateUserList {

  static public function queryUsers($set) {
    $query = \Drupal::database()->select('users_field_data', 'ufd');
    $query->addfield('ufd', 'uid');
    $query->addfield('ufd', 'name');
    $query->addfield('ufd', 'mail');

    // Check users preference for receiving email notification of new content.
    if (isset($set['email_new_content'])) {
      $query->join('user__field_email_new_content', 'fenc', 'fenc.entity_id = ufd.uid');
      $query->condition('fenc.field_email_new_content_value', $set['email_new_content'], is_array($set['email_new_content'] ? 'IN' : '='));
    }

    // Check user role on the site
    if (isset($set['role'])) {
      $query->join('user__roles', 'ur', 'ur.entity_id = ufd.uid');
      $query->condition('ur.roles_target_id', $set['role'], is_array($set['role'] ? 'IN' : '='));
    }

    // Check if user is member of a group
    if (isset($set['group_member'])) {
      $query->join('group_content_field_data', 'gcfd',
        'gcfd.entity_id = ufd.uid AND gcfd.type = :type', ['type' => 'theories-group_membership']);
      $query->addfield('gcfd', 'type');
      $query->condition('gcfd.gid', $set['group_member'], is_array($set['group_member']) ? 'IN' : '=');

      // Check if member has the correct role in the group.
      if (isset($set['group_role'])) {
        $query->join('group_content__group_roles', 'gcgr',
          'gcgr.entity_id = gcfd.id AND gcgr.bundle = :type', ['type' => 'theories-group_membership']);
        $query->condition('gcgr.group_roles_target_id', $set['group_role'], is_array($set['group_role']) ? 'IN' : '=' );
      }
    }

    return $query->execute()->fetchAllAssoc('name');
  }
}

