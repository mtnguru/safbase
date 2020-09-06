#!/usr/bin/env php

<?php

if (!chdir ('/home/sf/drupal/docroot/sites/default/files/investors')) {
  print ("failed\n");
  exit(0);
}


$in = fopen('/home/sf/drupal/docroot/bin/investors.data', 'r');
//$out = fopen('/home/sf/drupal/docroot/bin/investors.out', 'w');
while ($line = fgets($in)) {
  if (strlen($line) > 1) {
    $line = preg_replace( "/\r|\n/", "", $line );
    $args = explode(',', $line);
    $start = trim($args[0]);
    $end =  trim($args[1]);

    $args = explode(' ',$end);
    $investor = str_replace('_', ' ', $args[0]);

    print ("investor: $investor\n");
    print ("start:    $start\n");
    print ("end:      $endr\n");
    print ("$investor/$start ---- new/$end\n");
    $cmd = "cp '$investor/$start' 'new/$end'";
    print ("cmd:      $cmd\n");
    `$cmd`;
  }
}

fclose($in);
//fclose($out);


exit(0);

