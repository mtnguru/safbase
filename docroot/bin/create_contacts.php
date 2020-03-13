#!/usr/bin/env php

<?php

/**
 * @file
 * A command line application to generate proxy classes.
 */

use Drupal\az_contact\AzContact;
use Drupal\Core\Session\UserSession;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\DrupalKernel;
use Drupal\Core\Site\Settings;

// Get the current date
$date = date('Y/m/d h:i:s');

if (PHP_SAPI !== 'cli') {
  return;
}

// Bootstrap Drupal
$autoloader = require_once '../autoload.php';
require_once '../core/includes/bootstrap.inc';
$request = Request::createFromGlobals();
Settings::initialize(dirname(dirname(__DIR__)), DrupalKernel::findSitePath($request), $autoloader);
$kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod')->boot();
$kernel->boot();
$kernel->prepareLegacyRequest($request);

// Switch from anonymous user to admin
$accountSwitcher = \Drupal::service('account_switcher');
$accountSwitcher->switchTo(new UserSession(['uid' => 1]));

$settings = [];
$results = AzContact::querySubmissions($settings);
foreach ($results['results'] as $row) {
  $contact = AzContact::queryEmail($row->mail);
  if ($contact['results'] == false) {
    $contact = AzContact::createContact($row);
    exit ();
  }
  print "Done, aborting\n";
//AzContact::addSubmission($contact->id, $row);

  /*
  $address =
    $row->line1 . "\n" .
//  (($row->line2) ? $row->line2 . "\r" : "") .
    $row->locality . ", " . $row->area . "   " . $row->code . "\n" .
    $row->country . "\n";

  $fields = [
    $date,
    $row->name,
    $row->mail,
    $row->phone,
    $address,
    $row->subject,
    $row->message,
  ];
  */
}


exit(0);

function queryMessages($settings) {
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

//$query->condition('nfd.status', $set['status'], (is_array($set['status'])) ? 'IN' : '=');

  $totalRows = $query->countQuery()->execute()->fetchField();
  $results = $query->execute()->fetchAllAssoc('id');

  return [
    'results' => $results,
    'numRows' => count($results),
    'totalRows' => (int)$totalRows,
  ];
}
