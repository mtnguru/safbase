#!/usr/bin/env php

<?php

/**
 * @file
 * A command line application to generate proxy classes.
 */

// use Drupal\atomizer\AtomizerInit;
// use Drupal\az_content\AzContentQuery;
// use Drupal\Core\Session\UserSession;
// use Drupal\node\Entity\Node;
// use http\Client\Request;
use Drupal\file\Entity\File;
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

//
//Open the

$inv = fopen('/home/sf/drupal/docroot/bin/investors.data', 'r');
while ($line = fgets($inv)) {
  if (strlen($line) > 1) {
    $args = explode('/', $line);
    $investor = str_replace(' ', '_', $args[0]);
    $filename = $args[1];

    print ("$line");

    $file = File::create([
      'uri' => "public://investors/$investor/$filename",
      'uid' => 1,
      'status' => FILE_STATUS_PERMANANT,
      'filename' => 'dude.pdf'
    ]);
    $file->save();

    /*

    // Create the media entity.
    $media = $this->entityTypeManager->getStorage('media')->create(['bundle' => 'image']);

    // Assign the media name
    $media->name->setValue($data['atomName']);

    // Get the image and thumbnail values - not sure this does anything.
    $image = $media->get('image')->getValue();
    $thumb = $media->get('thumbnail')->getValue();

    // if we are overwriting then duplicate the old media entity.
    $media = ($data['overwrite'] == "TRUE") ? $media : $media->createDuplicate();

    // Set published status and moderation state.
    $media->status->setValue(NODE_PUBLISHED);
    $media->moderation_state->setValue('published');

    // Set the image
    $image[0]['target_id'] = $file->id();
    $media->get('image')->setValue(($image));

    // Set the thumbnail.
    $thumb[0]['target_id'] = $file->id();
    $media->get('thumbnail')->setValue($thumb);

    // Set the changed time to current time.
    $media->get('changed')->setValue(REQUEST_TIME);

    // Save the media entity.
    $media->save();
    */

  }
}

fclose($inv);


exit(0);

