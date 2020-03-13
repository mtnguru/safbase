<?php

namespace Drupal\az_contact;


use Drupal\node\Entity\Node;

/**
 * Class AzContactCreate.
 *
 * @package Drupal\az_contact
 */
class AzContact {
  static public function create(Array $settings) {

  }

  static public function querySubmissions(Array $settings) {
    $query = \Drupal::database()->select('contact_message', 'cm');
    $query->fields('cm', ['id', 'name', 'mail', 'subject', 'message', 'created']);

    $query->leftJoin('contact_message__field_address', 'cmfa', 'cm.id = cmfa.entity_id');
    $query->addField('cmfa', 'field_address_address_line1', 'line1');
    $query->addField('cmfa', 'field_address_address_line2', 'line2');
    $query->addField('cmfa', 'field_address_locality', 'locality');
    $query->addField('cmfa', 'field_address_administrative_area', 'area');
    $query->addField('cmfa', 'field_address_postal_code', 'code');
    $query->addField('cmfa', 'field_address_country_code', 'country');

    $query->leftJoin('contact_message__field_phone', 'cmfp', 'cm.id = cmfp.entity_id');
    $query->addField('cmfp', 'field_phone_value', 'phone');

    $query->leftJoin('contact_message__field_contact', 'cmfc', 'cm.id = cmfc.entity_id');
// ??  $query->addField('cmfp', 'field_phone_value', 'phone');

    $query->leftJoin('contact_message__field_interest', 'cmfi', 'cm.id = cmfi.entity_id');
    $query->leftJoin('taxonomy_term_field_data', 'ttfd', 'cmfi.field_interest_target_id = ttfd.tid');
    $query->addField('cmfi', 'field_interest_target_id', 'interest id');
    $query->addField('ttfd', 'name', 'interest');

//$query->condition('nfd.status', $set['status'], (is_array($set['status'])) ? 'IN' : '=');

    $totalRows = $query->countQuery()->execute()->fetchField();
    $results = $query->execute()->fetchAllAssoc('id');

    return [
      'results' => $results,
      'numRows' => count($results),
      'totalRows' => (int)$totalRows,
    ];
  }

  static public function queryEmail($email) {
    $query = \Drupal::database()->select('node__field_email', 'nfm');
    $query->addField('nfm', 'field_email_value', 'mail');
    $query->condition('nfm.field_email_value', $email, (is_array($email)) ? 'IN' : '=');
    $results = $query->execute()->fetch();

    return [
      'results' => $results,
      'numRows' => count($results),
    ];
  }

  static public function createContact($row) {
    /*
    $node = Node::create([
      'type' => 'article_internal',
      'title' => 'shit',
      'field_media' => '5',
    ]);
    $node->save();
    */

    $node = Node::create([
      'type'           => 'contact',
      'status'         => 1,
      'title'          => $row->name,
      'field_email'    => $row->mail,
      'field_interest' => [$row->interestid],
      'field_phone'    => $row->phone,
      'field_address'  => [
        'address_line1' => $row->line1,
        'address_line2' => $row->line2,
        'locality' => $row->locality,
        'administrative_area' => $row->area,
        'postal_code' => $row->code,
        'country_code' => $row->country,
      ]
    ]);
    $node->save();
    return $node;

    /*
    $row->id;
    $row->name;        title
    $row->mail;        field_email
    $row->created;     field_first_contact_date
    $row->line1;       field_address
    $row->line2;       field_address
    $row->locality;    field_address
    $row->area;        field_address
    $row->code;        field_address
    $row->country;     field_address
    $row->phone;       field_phone
    $row->interestid;  field_interest
    $row->interest;
    */
    return null;
  }

  static public function addSubmission($contactId, $data) {
  }
}

