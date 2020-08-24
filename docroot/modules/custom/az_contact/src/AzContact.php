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
    $query->addField('cmfi', 'field_interest_target_id', 'interestId');
    $query->addField('ttfd', 'name', 'interest');

//$query->condition('nfd.status', $set['status'], (is_array($set['status'])) ? 'IN' : '=');

    $totalRows = $query->countQuery()->execute()->fetchField();
    $results = $query->execute()->fetchAll();

    return [
      'results' => $results,
      'numRows' => count($results),
      'totalRows' => (int)$totalRows,
    ];
  }

  static public function queryEmail($email) {
    $query = \Drupal::database()->select('node__field_email', 'nfm');
    $query->addField('nfm', 'field_email_value', 'mail');
    $query->addField('nfm', 'entity_id', 'id');
    $query->condition('nfm.field_email_value', $email, (is_array($email)) ? 'IN' : '=');
    $results = $query->execute()->fetch();

    return $results;
  }

  static public function addContactMessage($cm) {
    $res = AzContact::queryEmail($cm->mail->value);
    if ($res == false) {
      $contact = AZContact::createContact($cm, 'Contact Form');
      $contactId = $contact->id();
    }
    else {
      $contactId = $res->id;
      AzContact::addSubmission($contactId, $cm);
    }
    $cm->field_contact = $contactId;
    $cm->save();
  }

  static public function createContact($cm, $source) {

    $interestv = $cm->field_interest->getValue();
    $interest = $cm->field_interest->getString();
    $addressv = $cm->field_address->getValue();
    $address = $cm->field_address->getString();
    $node = Node::create([
      'type'           => 'contact',
      'status'         => 1,
      'title'          => $cm->name->value,
      'field_email'    => $cm->mail->value,
      'field_interest' => $cm->field_interest->getValue(),
      'field_phone'    => $cm->field_phone->value,
      'field_source'   => $source,
      'field_first_contact_date' => date('Y-m-d', $cm->created->value),
      'field_last_contact_date'  => date('Y-m-d', $cm->created->value),
      'field_address'  => $cm->field_address->getValue(),
    ]);

    $node->save();
    return $node;

    /*
      'field_address'  => [
        'address_line1' => $row->line1,
        'address_line2' => $row->line2,
        'locality' => $row->locality,
        'administrative_area' => $row->area,
        'postal_code' => $row->code,
        'country_code' => $row->country,
      ]
    ]);
    */
  }

  static public function addSubmission($contactId, $cm) {
    // So what to do here?  Overwrite existing data, that way it gets corrected?
    // Read in the contact;
    $contact = \Drupal::entityTypeManager()->getStorage('node')->load($contactId);

    if (!$cm->field_interest->isEmpty()) {
      $existingInterests = array_column($contact->field_interest->getValue(), 'target_id');
//    if (!in_array($cm->field_interest->getValue(), $existingInterests)) {
//      $contact->field_interest[] = $cm->field_interest;
//    }
    }
    $contact->field_last_contact_date->setValue(date('Y-m-d', $cm->created->value));

    $contact->setTitle($cm->label());

    if (!$cm->field_phone->isEmpty()) {
      $contact->field_phone->setValue($cm->field_phone->value);
    }
    if (!$cm->field_address->isEmpty()) {
      $contact->field_address->setValue($cm->field_address->getValue());
    }
    if (!$cm->field_interest->isEmpty()) {
      $contact->field_interest->setValue($cm->field_interest->getValue());
    }

    $contact->save();
    return;
  }
}

