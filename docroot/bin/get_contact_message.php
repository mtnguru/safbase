#!/usr/bin/env php

<?php

/**
 * @file
 * A command line application to generate proxy classes.
 */

use Drupal\atomizer\AtomizerInit;
use Drupal\az_content\AzContentQuery;
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
// $accountSwitcher->switchTo(new UserSession(['uid' => 1]));

// Retrieve Atoms from DB
$settings = [];
$results = queryMessages($settings);

$out = fopen('/tmp/out.csv', 'w');
fputs($out, "Date,Name,Email,Phone,Address,Interest,Subject,Message\n");
foreach ($results['results'] as $row) {
  $date = date('Y/d/m H:i', $row->created);
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
    $row->interest,
    $row->subject,
    $row->message,
  ];
/*
  print(
    $date . "," .
    $row->name . "," .
    $row->mail . ",'" .
    $row->phone . ",'" .
    $address . ", '" .
    $row->subject . "','" .
    $row->message . "'\n"
  );
*/
  fputcsv($out, $fields);
}

fclose($out);


exit(0);

function queryMessages($settings) {
  $query = \Drupal::database()->select('contact_message', 'cm');
  $query->fields('cm', ['id', 'name', 'mail', 'subject', 'message', 'created']);
  $query->orderBy('cm.created', 'DESC');

  $query->leftJoin('contact_message__field_address', 'cmfa', 'cm.id = cmfa.entity_id');
  $query->addField('cmfa', 'field_address_address_line1', 'line1');
  $query->addField('cmfa', 'field_address_address_line2', 'line2');
  $query->addField('cmfa', 'field_address_locality', 'locality');
  $query->addField('cmfa', 'field_address_administrative_area', 'area');
  $query->addField('cmfa', 'field_address_postal_code', 'code');
  $query->addField('cmfa', 'field_address_country_code', 'country');

  $query->leftJoin('contact_message__field_interest', 'cmfi', 'cm.id = cmfi.entity_id');

  $query->leftJoin('taxonomy_term_field_data', 'ttfd', 'cmfi.field_interest_target_id = ttfd.tid');
  $query->addField('ttfd', 'name', 'interest');
//$query->addExpression('GROUP_CONCAT(DISTINCT cmfi.field_interest_target_id)', 'tag_list');
//$query->groupBy('cm.id');


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
