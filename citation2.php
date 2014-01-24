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
$page->addContent(HTMLLib::element('canvas', '', array('id'=>'downloadChart', width=>400, height=>400)));

$statistics = new MongoCollection($db, 'statistics');
$downloads = array();
$cursor = $statistics->find(array('dcIdentifier'=>$citation['dcIdentifier'][0]), array('_id'=>0, 'downloads'=>1, 'dlDate'=>1));
foreach($cursor as $statistic) $downloads[] = '["' . date('Y-m-d', $statistic['dlDate']->sec) . '", ' . $statistic['downloads'] . ']';

$page->addScript('/stats/js/chartJS/Chart.min.js');
$page->addScript('
var context2d = $("#downloadChart").get(0).getContext("2d");
var dlChart = new Chart(context2d);

var statistics = [' . implode(',', $downloads) . '];
new Chart(context2d).Line(statistics,options);
', 'load');
echo $page;
?>
