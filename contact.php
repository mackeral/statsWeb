<?php
require('/home/mackeral/Web/phpIncludes/config.php');

$page = new StatsPage("Contact");
$page->addContent(HTMLLib::p('one liner', array('class'=>'lead')));
$page->addContent(HTMLLib::p('full description'));

echo $page;
?>
