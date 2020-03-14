<?php
/**
* @file
*/


/**
 * Displays children pages as a block
 */

namespace Drupal\az_content;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides query for recent content
 *   Array $settings configures query parameters.
 */
class AzContentQuery {

  static public function nodeQuery(Array $set) {
    $sorted = false; // Prevent duplicate sorts.

    $query = \Drupal::database()->select('node_field_data', 'nfd');
//  $query->distinct();

    ////////// Set the output fields
    if (isset($set['fields'])) {
      foreach ($set['fields'] as $name => $fields) {
        $query->fields(($name == 'null') ? null : $name, $fields);
      }
    } else {
      $query->fields('nfd', ['nid', 'title', 'status']);
    }

    //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    //FILTERS
    //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    ////////// Filter on core Drupal published value
    if (isset($set['status'])) {
      $query->condition('nfd.status', $set['status'], (is_array($set['status'])) ? 'IN' : '=');
    }

    ////////// Content Types
    if (isset($set['types'])) {
      $query->condition('nfd.type', $set['types'], (is_array($set['types'])) ? 'IN' : '=');
    }

    ////////// Promoted to Front Page
    if (isset($set['promoted'])) {
      $query->condition('nfd.promote', $set['promoted'], '=');
    }

    ////////// Sticky to top of list
    if (isset($set['sticky'])) {
      $query->condition('nfd.sticky', $set['sticky'], '=');
    }

    ////////// Exclude NID's
    if (isset($set['exclude'])) {
      $query->condition('nfd.nid', $set['exclude'], (is_array($set['exclude'])) ? 'NOT IN' : '!=');
    }

    ////////// Author
    if (isset($set['author'])) {
      $query->condition('nfd.uid', $set['author'], (is_array($set['author'])) ? 'IN' : '=');
    }

    ////////// Ticket - Assigned To
    if (isset($set['assigned'])) {
      $query->join('node__field_assigned_to', 'nfat', 'nfd.nid = nfat.entity_id');
      $query->condition('nfat.field_assigned_to_target_id', $set['assigned'], (is_array($set['assigned'])) ? 'IN' : '=');
    }

    ////////// Topics
    if (isset($set['topics'])) {
      $query->join('node__field_topics', 'nft', 'nfd.nid = nft.entity_id');
      $query->condition('nft.field_topics_target_id', $set['topics'], (is_array($set['topics'])) ? 'IN' : '=');
    }

    ////////// Interest
    if (isset($set['interest'])) {
      $query->join('node__field_interest', 'nfi', 'nfd.nid = nfi.entity_id');
      $query->condition('nfi.field_interest_target_id', $set['interest'], (is_array($set['interest'])) ? 'IN' : '=');
    }

    ////////// Groups
    if (isset($set['groups'])) {
      $query->join('group_content_field_data', 'gcfd', 'gcfd.entity_id = nfd.nid');
      $query->condition('gcfd.type', 'theories-group_membership', '!=');
      $query->condition('gcfd.gid', $set['groups'], (is_array($set['groups'])) ? 'IN' : '=');
    }

    ////////// Private
    if (isset($set['private'])) {
      // $set['private'] = groups the current user belongs to
      // join in the private field and if true
      //   join in the group field to find the gid
      //   condition passes if gid in $set['private']
    }

    ////////// Exclude content from this/these groups.
    if (isset($set['groupsExclude'])) {
      $query->join('group_content_field_data', 'gcfd', 'gcfd.entity_id = nfd.nid');
      $query->condition('gcfd.type', 'theories-group_membership', '!=');
      $query->condition('gcfd.gid', $set['groupsExclude'], (is_array($set['groupsExclude'])) ? 'NOT IN' : '!=');
    }

    ////////// Page - Ticket CT only - Tickets record which page they relate to.
    if (isset($set['pages'])) {
      $query->join('node__field_page', 'nfp', 'nfd.nid = nfp.entity_id');
      $query->condition('nfp.field_page_target_id', $set['pages'], (is_array($set['pages'])) ? 'IN' : '=');
    }

    ////////// Publish Date
    if (isset($set['publishAge'])) {
      $seconds = DrupalDateTime::createFromTimestamp((int)\Drupal::time()->getRequestTime() - $set['publishAge'] * 60);
      $date = format_date($seconds->getTimestamp() , 'custom', 'Y-m-d\TH:i:s', 'UTC');

      $query->join('node__field_publish_date', 'nfpd', 'nfd.nid = nfpd.entity_id');
//    $query->condition('nfpd.field_publish_date_value', $nseconds, $operator);
      $query->condition('nfpd.field_publish_date_value', $date, '>');
    }

    // If requested query for total rows matching all filters.
    if (isset($set['count']) && $set['count']) {
      return $query->countQuery()->execute()->fetchField();
    }
    if (isset($set['getTotalRows']) && $set['getTotalRows']) {
      $totalRows = $query->countQuery()->execute()->fetchField();
    } else {
      $totalRows = -1;
    }

    //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    //SORTS
    //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    if (!$sorted) {
      // First sort by sticky to top of lists flag
      if (isset($set['sticky'])) {
        $query->orderBy('nfd.sticky', 'DESC');
      }

      $sort = (isset($set['sort'])) ? $set['sort'] : 'changed';
      $order = (isset($set['sortOrder'])) ? $set['sortOrder'] : 'DESC';
      switch ($sort) {
        case 'none':
          $sorted = true;
          break;
        case 'changed':
          $query->orderBy('nfd.changed', $order);
          $sorted = true;
          break;
        case 'created':
          $query->orderBy('nfd.created', $order);
          $sorted = true;
          break;
        case 'select-atom':
          // Join in the element, Join in atomic number, Join # protons
          // Order by Atomic #, # Protons, Title

          // Join Element entity
          $query->join('node__field_element', 'nfe', 'nfd.nid = nfe.entity_id');

          // Join Atomic Number field and order by it first.
          $query->leftJoin('node__field_atomic_number', 'nfan', 'nfan.entity_id = nfe.field_element_target_id');
          $query->orderBy('nfan.field_atomic_number_value', 'ASC');

          // Join # Protons field and order by it second.
          $query->leftJoin('node__field__protons', 'nfp', 'nfd.nid = nfp.entity_id');
          $query->orderBy('nfp.field__protons_value', 'ASC');

          // Join Abundance in
          $query->leftJoin('node__field_abundance', 'nfab', 'nfd.nid = nfab.entity_id');

          // Order by the Atom name third.
          $query->orderBy('nfd.title', 'ASC');

          // Kludge alert - the following fields are merged in - needed for the select atom list and periodic table.
          $query->leftJoin('node__field_stability', 'nfs', 'nfd.nid = nfs.entity_id'); // Needed to build select atom list
          $query->leftJoin('taxonomy_term_field_data', 'ttfd', 'ttfd.tid = nfs.field_stability_target_id');
          $query->leftJoin('node__field_media', 'nfm', 'nfd.nid = nfm.entity_id');
          $query->leftJoin('node__field_image', 'nfi', 'nfd.nid = nfi.entity_id');
          $query->leftJoin('node__field_approval', 'nfap', 'nfd.nid = nfap.entity_id');

          // Rows and columns of the official PTE and SAM PTE
//        $query->leftJoin('node__field_pte_row',    'nfpr', 'nfd.nid = nfpr.entity_id');
//        $query->leftJoin('node__field_pte_column', 'nfpc', 'nfd.nid = nfpc.entity_id');
//        $query->leftJoin('node__field_sam_row',    'nfsr', 'nfd.nid = nfpr.entity_id');
//        $query->leftJoin('node__field_sam_column', 'nfsc', 'nfd.nid = nfpc.entity_id');

          $sorted = true;
          break;
        case 'element':
          $query->leftJoin('node__field_atomic_number', 'nfan', 'nfan.entity_id = nfd.nid');
          $query->orderBy('nfan.field_atomic_number_value', 'ASC');
          break;
        case 'title':
//        $query->leftJoin('node__field_atomic_number', 'nfan', 'nfan.entity_id = nfd.nid');
          $query->orderBy('nfd.title', 'ASC');
          break;
      }
    }

    // Execute the query depending on 'more' setting.
    switch ((isset($set['more'])) ? $set['more'] : 'none') {
      case 'none':
        $results = $query->execute()->fetchAllAssoc('nid');
        break;

      case 'limit':
        $query->range(0, (isset($set['limit'])) ? $set['limit'] : 10);
        $results = $query->execute()->fetchAllAssoc('nid');
        break;

      case 'ajax':
        // If using AJAX then we keep track of page number and items per page.
        if (isset($set['pageNum'])) {
          $query->range($set['pageNum'] * $set['pageNumItems'], $set['pageNumItems']);
        }
        $results = $query->execute()->fetchAllAssoc('nid');

        break;

      case 'pager':
        $result = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')
          ->limit((isset($set['pageNumItems'])) ? $set['pageNumItems'] : 20)
          ->element((isset($set['pagerId'])) ? $set['pagerId'] : 0)
          ->execute();
        $results = $result->fetchAllAssoc('nid');
        break;
    }
    return [
      'results' => $results,
      'numRows' => count($results),
      'totalRows' => (int)$totalRows,
    ];
  }

  static public function commentQuery(Array $set) {
    $sorted = false; // Prevent duplicate sorts.

    ////////// Connect to database - root table comment_field_data
    $query = \Drupal::database()->select('comment_field_data', 'cfd');

    ////////// Set the output fields
    if (isset($set['fields'])) {
      foreach ($set['fields'] as $name => $fields) {
        $query->fields(($name == 'null') ? null : $name, $fields);
      }
    } else {
      $query->fields('cfd', ['cid']);
//    $query->fields('nfd', ['nid', 'title', 'status']);
    }

    //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    // COMMENT FILTERS
    //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    ////////// Filter on core Drupal published value
    if (isset($set['statusComment'])) {
      $query->condition('cfd.status', $set['statusComment'], (is_array($set['statusComment'])) ? 'IN' : '=');
    }

    ////////// Author
    if (isset($set['authorComment'])) {
      $query->condition('cfd.uid', $set['authorComment'], (is_array($set['authorComment'])) ? 'IN' : '=');
    }

    //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    // PARENT NODE FILTERS
    //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    ////////// Join in the parent node table.
    $query->join('node_field_data', 'nfd', 'nfd.nid = cfd.entity_id');

    ////////// Filter on core Drupal published value
    if (isset($set['status'])) {
      $query->condition('nfd.status', $set['status'], '=');
    }

    ////////// Content Types
    if (isset($set['types'])) {
      $query->condition('nfd.type', $set['types'], (is_array($set['types'])) ? 'IN' : '=');
    }

    ////////// Author
    if (isset($set['author'])) {
      $query->condition('nfd.uid', $set['author'], (is_array($set['author'])) ? 'IN' : '=');
    }

    ////////// Topics
    if (isset($set['topics'])) {
      $query->join('node__field_topics', 'nft', 'nfd.nid = nft.entity_id');
      $query->condition('nft.field_topics_target_id', $set['topics'], (is_array($set['topics'])) ? 'IN' : '=');
    }

    ////////// Groups
    if (isset($set['groups'])) {
      $query->join('group_content_field_data', 'gcfd', 'gcfd.entity_id = nfd.nid');
      $query->condition('gcfd.type', 'theories-group_membership', '!=');
      $query->condition('gcfd.gid', $set['groups'], (is_array($set['groups'])) ? 'IN' : '=');
    }

    ////////// Exclude content from this/these groups.
    if (isset($set['groupsExclude'])) {
      $query->join('group_content_field_data', 'gcfd', 'gcfd.entity_id = nfd.nid');
      $query->condition('gcfd.type', 'theories-group_membership', '!=');
      $query->condition('gcfd.gid', $set['groupsExclude'], (is_array($set['groupsExclude'])) ? 'NOT IN' : '!=');
    }

    //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    // EXECUTE COUNT QUERY
    //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    if ((isset($set['getTotalRows']) && $set['getTotalRows']) ||
        (isset($set['count']) && $set['count'])) {
      $totalRows = $query->countQuery()->execute()->fetchField();
      if (isset($set['count'])) {
        return $totalRows;
      }
    } else {
      $totalRows = -1;
    }

    //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    // SORTS
    //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    if (!$sorted) {
      $sort = (isset($set['sort'])) ? $set['sort'] : 'changed';
      $order = (isset($set['sortOrder'])) ? $set['sortOrder'] : 'DESC';
      switch ($sort) {
        case 'none':
          $sorted = true;
          break;
        case 'changed':
          $query->orderBy('cfd.changed', $order);
          $sorted = true;
          break;
        case 'created':
          $query->orderBy('cfd.created', $order);
          $sorted = true;
          break;
      }
    }

    // Execute the query depending on 'more' setting.
    switch ((isset($set['more'])) ? $set['more'] : 'none') {
      case 'none':
        $results = $query->execute()->fetchAllAssoc('nid');
        break;

      case 'ajax':
        // If using AJAX then we keep track of page number and items per page.
        if (isset($set['pageNum'])) {
          $query->range($set['pageNum'] * $set['pageNumItems'], $set['pageNumItems']);
        }
        $result = $query->execute();
        $results = $result->fetchAllAssoc('cid');

        break;

      case 'pager':
        $result = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')
          ->limit((isset($set['pageNumItems'])) ? $set['pageNumItems'] : 20)
          ->element((isset($set['pagerId'])) ? $set['pagerId'] : 0)
          ->execute();
        $results = $result->fetchAllAssoc('cid');
        break;
    }
    return [
      'results' => $results,
      'numRows' => count($results),
      'totalRows' => (int)$totalRows,
    ];
  }

  static public function mediaQuery(Array $set) {
    $sorted = false; // Prevent duplicate sorts.

    ////////// Connect to database - root table comment_field_data
    $query = \Drupal::database()->select('media_field_data', 'mfd');

    ////////// Set the output fields
    if (isset($set['fields'])) {
      foreach ($set['fields'] as $name => $fields) {
        $query->fields(($name == 'null') ? null : $name, $fields);
      }
    } else {
      $query->fields('mfd', ['mid']);
    }

    ////////// Filter on core Drupal published value
    if (isset($set['status'])) {
      $query->condition('mfd.status', $set['status'], (is_array($set['status'])) ? 'IN' : '=');
    }

    ////////// Author
    if (isset($set['author'])) {
      $query->condition('mfd.uid', $set['author'], (is_array($set['author'])) ? 'IN' : '=');
    }

    ////////// Content Types
    if (isset($set['types'])) {
      $query->condition('mfd.bundle', $set['types'], (is_array($set['types'])) ? 'IN' : '=');
    }

    ////////// Topics
    if (isset($set['topics'])) {
      $query->join('media__field_topics', 'mft', 'mfd.mid = mft.entity_id');
      $query->condition('mft.field_topics_target_id', $set['topics'], (is_array($set['topics'])) ? 'IN' : '=');
    }

    ////////// Groups
    if (isset($set['groups'])) {
      $query->join('media__field_group', 'mft', 'mfd.mid = mft.entity_id');
      $query->condition('mft.field_group_target_id', $set['groups'], (is_array($set['groups'])) ? 'IN' : '=');
    }

    ////////// Exclude content from this/these groups.
    if (isset($set['groupsExclude'])) {
      $query->join('media__field_group', 'mft', 'mfd.mid = mft.entity_id');
      $query->condition('mft.field_group_target_id', $set['groupsExclude'], (is_array($set['groupsExclude'])) ? 'IN' : '=');
    }

    //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    // EXECUTE COUNT QUERY
    //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    if ((isset($set['getTotalRows']) && $set['getTotalRows']) ||
      (isset($set['count']) && $set['count'])) {
      $totalRows = $query->countQuery()->execute()->fetchField();
      if (isset($set['count'])) {
        return $totalRows;
      }
    } else {
      $totalRows = -1;
    }

    //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    // SORTS
    //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    if (!$sorted) {
      $sort = (isset($set['sort'])) ? $set['sort'] : 'changed';
      $order = (isset($set['sortOrder'])) ? $set['sortOrder'] : 'DESC';
      switch ($sort) {
        case 'none':
          $sorted = true;
          break;
        case 'changed':
          $query->orderBy('mfd.changed', $order);
          $sorted = true;
          break;
        case 'created':
          $query->orderBy('mfd.created', $order);
          $sorted = true;
          break;
      }
    }

    // Execute the query depending on 'more' setting.
    switch ((isset($set['more'])) ? $set['more'] : 'none') {
      case 'none':
        $results = $query->execute()->fetchAllAssoc('mid');
        break;

      case 'ajax':
        // If using AJAX then we keep track of page number and items per page.
        if (isset($set['pageNum'])) {
          $query->range($set['pageNum'] * $set['pageNumItems'], $set['pageNumItems']);
        }
        $result = $query->execute();
        $results = $result->fetchAllAssoc('mid');

        break;

      case 'pager':
        $result = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')
          ->limit((isset($set['pageNumItems'])) ? $set['pageNumItems'] : 20)
          ->element((isset($set['pagerId'])) ? $set['pagerId'] : 0)
          ->execute();
        $results = $result->fetchAllAssoc('mid');
        break;
    }
    return [
      'results' => $results,
      'numRows' => count($results),
      'totalRows' => (int)$totalRows,
    ];
  }

  static public function groupQuery(Array $set) {
    $sorted = false; // Prevent duplicate sorts.

    ////////// Build database query = root table group_field_data
    $query = \Drupal::database()->select('group_field_data', 'gfd');

    ////////// Set the output fields
    if (isset($set['fields'])) {
      foreach ($set['fields'] as $name => $fields) {
        $query->fields(($name == 'null') ? null : $name, $fields);
      }
    } else {
      $query->fields('gfd', ['gid']);
    }

    ////////// Filter on core Drupal published value
    if (isset($set['status'])) {
      $query->condition('gfd.status', $set['status'], (is_array($set['status'])) ? 'IN' : '=');
    }

    ////////// Content Types
    if (isset($set['types'])) {
      $query->condition('mfd.bundle', $set['types'], (is_array($set['types'])) ? 'IN' : '=');
    }

    ////////// Topics
    if (isset($set['topics'])) {
      $query->join('media__field_topics', 'mft', 'mfd.mid = mft.entity_id');
      $query->condition('mft.field_topics_target_id', $set['topics'], (is_array($set['topics'])) ? 'IN' : '=');
    }

    ////////// Groups
    if (isset($set['groups'])) {
      $query->join('media__field_group', 'mft', 'mfd.mid = mft.entity_id');
      $query->condition('mft.field_group_target_id', $set['groups'], (is_array($set['groups'])) ? 'IN' : '=');
    }

    ////////// Exclude content from this/these groups.
    if (isset($set['groupsExclude'])) {
      $query->join('media__field_group', 'mft', 'mfd.mid = mft.entity_id');
      $query->condition('mft.field_group_target_id', $set['groupsExclude'], (is_array($set['groupsExclude'])) ? 'IN' : '=');
    }

    //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    // EXECUTE COUNT QUERY
    //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    if ((isset($set['getTotalRows']) && $set['getTotalRows']) ||
      (isset($set['count']) && $set['count'])) {
      $totalRows = $query->countQuery()->execute()->fetchField();
      if (isset($set['count'])) {
        return $totalRows;
      }
    } else {
      $totalRows = -1;
    }

    //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    // SORTS
    //\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
    if (!$sorted) {
      $sort = (isset($set['sort'])) ? $set['sort'] : 'changed';
      $order = (isset($set['sortOrder'])) ? $set['sortOrder'] : 'DESC';
      switch ($sort) {
        case 'none':
          $sorted = true;
          break;
        case 'changed':
          $query->orderBy('mfd.changed', $order);
          $sorted = true;
          break;
        case 'created':
          $query->orderBy('mfd.created', $order);
          $sorted = true;
          break;
      }
    }

    // Execute the query depending on 'more' setting.
    switch ((isset($set['more'])) ? $set['more'] : 'none') {
      case 'none':
        $results = $query->execute()->fetchAllAssoc('mid');
        break;

      case 'ajax':
        // If using AJAX then we keep track of page number and items per page.
        if (isset($set['pageNum'])) {
          $query->range($set['pageNum'] * $set['pageNumItems'], $set['pageNumItems']);
        }
        $result = $query->execute();
        $results = $result->fetchAllAssoc('mid');

        break;

      case 'pager':
        $result = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')
          ->limit((isset($set['pageNumItems'])) ? $set['pageNumItems'] : 20)
          ->element((isset($set['pagerId'])) ? $set['pagerId'] : 0)
          ->execute();
        $results = $result->fetchAllAssoc('mid');
        break;
    }
    return [
      'results' => $results,
      'numRows' => count($results),
      'totalRows' => (int)$totalRows,
    ];
  }
}

