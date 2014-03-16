<?php
function tlog($entry) {
$head = date('m/d/y H:i:s') . ' :   ';
$file = 'log.log';
// Open the file to get existing content
$current = file_get_contents($file);
// Append a new person to the file
$current .= $head. $entry. "\n";
// Write the contents back to the file
file_put_contents($file, $current);
}
 ?>