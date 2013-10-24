<?php
require('/home/mackeral/Web/phpIncludes/config.php');
$m = new MongoClient('mongodb://lawlibrary:unclezeb@ds063287.mongolab.com:63287/repos');
$db = $m->selectDB('repos');

$lines = file('avgDownloads.txt');
foreach($lines as $line) {
    $chunks = explode("\t", trim($line));
    $avgDownloads[$chunks[0]] = $chunks[1];
}

$statistics = new MongoCollection($db, 'statistics');

$downloadString = array('date,downloads,average');
//$cursor = $statistics->find(array('dcIdentifier'=>$request['dcIdentifier']), array('_id'=>0, 'downloads'=>1, 'dlDate'=>1));
$result = $statistics->group(array('dlDate'=>1), array('dlTotal'=>0), "function (obj, prev) { prev.dlTotal += obj.downloads; }", array('condition'=>array('dcIdentifier'=>$request['dcIdentifier'])));
$totals = array();
foreach($result['retval'] as $statMonth){
    $totals[$statMonth['dlDate']->sec] = $statMonth['dlTotal'];
}
ksort($totals);
$age = 0;
foreach($totals as $dlDate=>$dlTotal) {
    $downloadString[] = date('Y-m-d', $dlDate) . ',' . $dlTotal . ',' . $avgDownloads[$age++];
}
echo implode("\n", $downloadString);
?>