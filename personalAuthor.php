<?php
require('/home/mackeral/Web/phpIncludes/config.php');
$chunks = explode(', ', $request['q']);
$author = "{$chunks[1]} {$chunks[0]}";

$m = new MongoClient();
$db = $m->selectDB('repos');
$collection = new MongoCollection($db, 'citations');
$citations = array();
$authorRE = new MongoRegEx("/" . $chunks[0] . ", " . $chunks[1][0] . "/");
$cursor = $collection->find(array('dcCreator'=>$authorRE));
$cursor->sort(array('dcTitle'=>1));
foreach($cursor as $citation) $citations[] = HTMLLib::a($citation['dcTitle'], "/stats/citation.php?identifier={$citation['identifier']}");

$page = new StatsPage("Personal Author: $author");
$page->addContent(HTMLLib::p('profile-like. includes info from authority service, photo (?)', array('class'=>'lead')));
$page->addContent(HTMLLib::ol($citations));
echo $page;
?>
