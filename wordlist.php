<?php
$lines = file('words.txt');
foreach($lines as $line)
	if (preg_match('/^[A-Z].{5}$/', trim($line))) $words[] = trim($line);
	
echo json_encode($words);
?>