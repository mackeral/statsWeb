<?php
require('/var/www/phpIncludes/config.php');

$lines = file('avgDownloads.txt');
foreach($lines as $line) {
    $chunks = explode("\t", trim($line));
    $avgDownloads[$chunks[0]] = $chunks[1];
}
//print_r($avgDownloads);

$totals = array();
$downloadString = array('date,downloads,average');
$statement = $mysql->prepare('select dlDate, sum(dlN)as downloads from stats where dcID=? group by dlDate order by dlDate asc;');
$statement->bind_param('s', $request['dcIdentifier']);
$statement->execute();
$statement->bind_result($dlDate, $downloads);
while($statement->fetch())
    $totals[$dlDate] = $downloads;
ksort($totals);
$age = 0;

//$ingestDate = substr($request['ingestDate'], 0, 10);
//echo $ingestDate;

foreach($totals as $dlDate=>$dlTotal) {
    $downloadString[] = $dlDate . ',' . $dlTotal . ',' . $avgDownloads[$age++];
}
echo implode("\n", $downloadString);
?>