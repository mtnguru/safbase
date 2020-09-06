#!/usr/bin/env php

<?php

$in = fopen('/home/sf/drupal/docroot/bin/investors.data', 'r');
$out = fopen('/home/sf/drupal/docroot/bin/investors.out', 'w');
while ($line = fgets($in)) {
  if (strlen($line) > 1) {
    $line = preg_replace( "/\r|\n/", "", $line );
    $args = explode(',', $line);
    $
    $investor = str_replace(' ', '_', $args[0]);
    $filename = $args[1];

    fread 
    fprintf($out, "%-70s %s %s\n", $filename, $investor, $filename);

  }
}

fclose($in);
fclose($out);


exit(0);

