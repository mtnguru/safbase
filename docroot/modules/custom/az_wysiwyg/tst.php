<?php

// Not sure what this file is for?  Testing something

$text = 'name="chitty chitty bang bang"';

if (preg_match('/name=\"*([a-z ]+)\"*/', $text, $matches)) {
  print ('matches[0] ' . $matches[0] . "\n");
  print ('matches[1] ' . $matches[1] . "\n");
}

return;

// $text = "This is a sentence <footnote>This is a sentence footnote</footnote> with a couple real cool <topic>things</topic>. " .
//         "<pb>This is another <topic>sentence</topic> with a <footnote>This is the second footnote</footnote> and this is a <topic>Birkeland Current</topic> so what do you think of that?";
$text = 'This is a topic <topic name="chitty chitty bang bang">dude</topic> is this cool.';

$reg = '<([a-z]+) *(.*?)>(.*?)<\/([a-z]+?)>';
$pagebreakReg = '<pb>';
// $pattern = '/\b(' . $topicReg . '|' . $footnoteReg . '|' . $pagebreakReg . ')\b/';
$pattern = '/(' . $reg . '|' . $pagebreakReg . ')/';

print('Text: ' . $text . "\n");
$hitcount = preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);

print('Pattern: ' . $pattern . "\n");
print('Text: ' . $text . "\n");
print("hitcount: " . $hitcount . "\n");

$footnotes = [];
$topics = [];
$c = 0;
$ntext = '';

foreach ($matches[0] as $key => $match) {
  $bit = substr($text, $c, $match[1] - $c);
  print("Before: " . $bit . "\n\n");
  $ntext .= substr($text, $c, $match[1] - $c);
  $c = $match[1] + strlen($match[0]);

  if (preg_match('/' . $reg . '/', $match[0], $imatches)) {
    // Replace text with link to topic, add topic to array
    $ntext .= '<shit>' . $imatches[2] . '</shit>';
    var_dump($imatches);
  }

//if (preg_match('/' . $footnoteReg . '/', $match[0], $imatches)) {
//  // Replace text with link to footnote, add footnote to array.  Position in array determines index.
//  $ntext .= '<crap>' . $imatches[2] . '</crap>';
//  var_dump($imatches);
//}

  if (preg_match('/' . $pagebreakReg . '/', $match[0])) {
    // Put in whatever a fucking page break is - a paragraph?
    $ntext .= '<dump>';
  }
  print("$key $c " . strlen($match[0]) . " $match[1] --- $match[0]\n");
  print("NText: " . $ntext . "\n\n");
}

$ntext .= substr($text, $c);

print("Text: " . $text . "\n\n");
print("NText: " . $ntext . "\n\n");
