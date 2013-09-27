<?php
require('/home/mackeral/Web/phpIncludes/config.php');
$m = new MongoClient();
$db = $m->selectDB('repos');
$collection = new MongoCollection($db, 'citations');
$citation = $collection->findOne(array('identifier'=>$request['identifier']));
$itemDetails = '';
foreach($citation as $k=>$v) {
    if(is_array($v)) $v = implode('<br>', $v);
    $itemDetails .= HTMLLib::element('dt', $k) . HTMLLib::element('dd', $v);
}
$page = new StatsPage($citation['dcTitle']);
$page->addContent(HTMLLib::element('dl', $itemDetails, array('class'=>'dl-horizontal')));
$page->addContent(HTMLLib::p('metadata from any kind of harvest', array('class'=>'lead')));
echo $page;
?>
