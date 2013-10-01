<?php
require('/home/mackeral/Web/phpIncludes/config.php');
$m = new MongoClient();
$db = $m->selectDB('repos');
$citations = new MongoCollection($db, 'citations');
$citation = $citations->findOne(array('identifier'=>$request['identifier']));
$itemDetails = '';
foreach($citation as $k=>$v) {
    if(is_array($v)) $v = implode('<br>', $v);
    $itemDetails .= HTMLLib::element('dt', $k) . HTMLLib::element('dd', $v);
}
$page = new StatsPage($citation['dcTitle']);
$page->addContent(HTMLLib::element('dl', $itemDetails, array('class'=>'dl-horizontal')));
$page->addContent(HTMLLib::p('metadata from any kind of harvest', array('class'=>'lead')));

$statistics = new MongoCollection($db, 'statistics');
//db.statistics.find({dcIdentifier: 'http://scholarship.law.berkeley.edu/californialawreview/vol88/iss6/1'}, {'_id': 0, 'downloads': 1, 'dlDate': 1}) 
$downloads = array();
$cursor = $statistics->find(array('dcIdentifier'=>$citation['dcIdentifier'][0]), array('_id'=>0, 'downloads'=>1, 'dlDate'=>1));
foreach($cursor as $statistic) $downloads[date('Y/m/d', $statistic['dlDate']->sec)] = $statistic['downloads'];
$page->addScript('var statistics = ' . json_encode($downloads) . ';');
$page->addScript('/stats/js/d3/d3.v3.min.js');
$page->addScript('console.log(statistics)', 'load');
echo $page;
?>
