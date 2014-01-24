<?php
require('/var/www/phpIncludes/config.php');

$page = new StatsPage("About", $logInOut);
$page->addContent(HTMLLib::p('one liner', array('class'=>'lead')));
$page->addContent(HTMLLib::p('full description'));

echo $page;
?>
