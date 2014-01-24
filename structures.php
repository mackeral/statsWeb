<?php
require('/var/www/phpIncludes/config.php');

$institution = $request['institution'];

$m = new MongoClient();
$db = $m->selectDB('repos');
$citations = new MongoCollection($db, 'citations');
$statistics = new MongoCollection($db, 'citations');

$page = new StatsPage("Structures", $logInOut);

$counts = $citations->group(
    array("setSpec"=>1),
    array("count"=>0),
    "function(cur, result){ result.count += 1 }"
);

// bjil => SetSpec object
$setSpecs = array();
foreach($counts['retval'] as $setSpec) {
    $chunks = explode(':', $setSpec['setSpec']);
    $setSpecs[$chunks[1]] = new SetSpec($chunks[1], $setSpecLabels[$chunks[1]]);
    $setSpecs[$chunks[1]]->setCitationCount($setSpec['count']);
}

// dcIdentifier => setSpec
$cursor = $citations->find(array(), array('dcIdentifier'=>true, 'setSpec'=>true));
$dcIdentifiers = array();
foreach($cursor as $citation) $dcIdentifiers[$citation['dcIdentifier'][0]] = substr($citation['setSpec'], 12);

// dcIdentifier => downloadCount
$statistics = new MongoCollection($db, 'statistics');
$results = $statistics->aggregate(
    array(
        '$group' => array(
            '_id' => '$dcIdentifier',
           'total' => array('$sum' => '$downloads')
        )
    )
);
$downloads = array();
foreach($results['result'] as $result) $downloads[$result['_id']] = $result['total'];

foreach($downloads as $dcIdentifier=>$downloadCount) $setSpecs[$dcIdentifiers[$dcIdentifier]]->addDownloadCount($downloadCount);

$journals = array('aalj', 'bblj', 'bjalp', 'bjil', 'bjcl', 'bjesl', 'californialawreview', 'bglj', 'jmeil', 'clrcircuit');
$facpubs = array('facpubs');
$symposia = array('bjalp_symposia','riesenfeld');
$books = array('books');
$structures = array();
$structures['Journals'] = new Structure('Journals');
$structures['Faculty Publications'] = new Structure('Faculty Publications');
$structures['Symposia'] = new Structure ('Symposia');
$structures['Books'] = new Structure ('Books');
foreach($setSpecs as $id=>$label){
    if(in_array($id, $journals)) {
        $structures['Journals']->addCitationCount($label->getCitationCount());
        $structures['Journals']->addDownloadCount($label->getDownloadCount());
    }
    else if(in_array($id, $facpubs)) {
        $structures['Faculty Publications']->addCitationCount($label->getCitationCount());
        $structures['Faculty Publications']->addDownloadCount($label->getDownloadCount());
    }
	else if(in_array($id, $symposia)) {
        $structures['Symposia']->addCitationCount($label->getCitationCount());
        $structures['Symposia']->addDownloadCount($label->getDownloadCount());
    }
	else if(in_array($id, $books)) {
        $structures['Books']->addCitationCount($label->getCitationCount());
        $structures['Books']->addDownloadCount($label->getDownloadCount());
    }
}

$trs = array();
foreach($structures as $structure){
    $trs[] = HTMLLib::tr(array(
        HTMLLib::a($structure->label, "structure.php?label={$structure->label}"),
        $structure->citationCount,
        $structure->downloadCount
    ));
}

$page->addContent(HTMLLib::table(
    $trs,
    array('class'=>'sortable'),
    HTMLLib::tr(array(
        HTMLLib::td('Structure', array('data-defaultsort'=>'asc'), true),
        HTMLLib::td('# documents', null, true),
        HTMLLib::td('# downloads', null, true)
    ), false, null, true)
));

/*

// most recent ingestDate:  db.citations.find({}, {ingestDate: 1}).sort({ingestDate: -1}).limit(1)
// earliest ingestDate:     db.citations.find({}, {ingestDate: 1}).sort({ingestDate: 1}).limit(1)
$cursor = $citations->find(array(), array('ingestDate'=>true));
$cursor->sort(array('ingestDate'=>1));
$earliestIngestDateM = $cursor->getNext();
$earliestIngestDate = new DateTime();
$earliestIngestDate->setTimeStamp($earliestIngestDateM['ingestDate']->sec);
//echo $earliestIngestDate->format('Ym');

$cursor->reset();
$cursor->sort(array('ingestDate'=>-1));
$mostRecentIngestDateM = $cursor->getNext();
$mostRecentIngestDate = new DateTime();
$mostRecentIngestDate->setTimeStamp($mostRecentIngestDateM['ingestDate']->sec);
//echo $mostRecentIngestDate->format('Ym');

$ingests = array();
$downloads = array();

$ingestDate = $mostRecentIngestDate;
$monthInterval = new DateInterval('P1M');
//$earliestIngestDate->sub($monthInterval);
do {
    $mDate = new MongoDate(strtotime($ingestDate->format('Y-m-t') . " 00:00:00")); // this is the endcap, last day of the month
    $mDate0 = new MongoDate(strtotime($ingestDate->format('Y-m') . "-01 00:00:00")); // this is the endcap, last day of the month
    $keys = array("setSpec"=>1);
    $initial = array("count"=>0);
    $reduce = "function(cur, result){ result.count += 1 }";
    $condition = array("condition"=>array("ingestDate"=>array('$lte'=>$mDate)));
    $result = $citations->group($keys, $initial, $reduce, $condition);
    $facPubCount = 0;
    $journalCount = 0;
    foreach($result as $row)    {
        foreach($row as $setSpec) {
            if(in_array(substr($setSpec['setSpec'], 12), $journals)) $ingests[$ingestDate->format('Ym')]['j'] += $setSpec['count'];
            elseif($setSpec['setSpec'] == 'publication:facpubs') $ingests[$ingestDate->format('Ym')]['p'] += $setSpec['count'];
        }
    }

    $keys = array("setSpec"=>1);
    $initial = array('downloads'=>0);
    $reduce = "function(cur, result){ result.downloads += cur.downloads }";
    $condition = array("condition"=>array("dlDate"=>array('$lte'=>$mDate, '$gte'=>$mDate0)));
    $result = $statistics->group($keys, $initial, $reduce, $condition);
    $facPubCount = 0;
    $journalCount = 0;
    foreach($result as $row)    {
        foreach($row as $setSpec) {
            if(in_array(substr($setSpec['setSpec'], 12), $journals)) $downloads[$ingestDate->format('Ym')]['j'] += $setSpec['downloads'];
            elseif($setSpec['setSpec'] == 'publication:facpubs') $downloads[$ingestDate->format('Ym')]['p'] += $setSpec['downloads'];
        }
    }
    $ingestDate->sub($monthInterval);
} while($ingestDate > $earliestIngestDate);

$months = array_keys($ingests);
sort($months);
//print_r($months);
$totalJournals = 0;
$totalFacPubs = 0;
foreach($months as $month){
    $totalJournals += $ingests[$month]['j'];
    $totalFacPubs += $ingests[$month]['p'];
    echo "$month\t" . 
        ($downloads[$month]['j'] / $totalJournals) . "\t" . 
        ($downloads[$month]['p'] / $totalFacPubs) . "\t" . 
        (($downloads[$month]['j'] + $downloads[$month]['p'])/($totalJournals + $totalFacPubs)) . "\n";
}

//print_r($ingests);
//print_r($downloads);

*/

$page->addScript('/stats/js/d3/d3.v3.min.js');
$page->addInternalCSS('
#downloadsChart { font: 10px sans-serif; }
.axis path, .axis line {
    fill: none;
    stroke: #000;
    shape-rendering: crispEdges;
}
.x.axis path { display: none; }
.line {
    fill: none;
    stroke: steelblue;
    stroke-width: 1.5px;
}');
$page->addContent(HTMLLib::h(3, 'downloads per item'));
$page->addContent(HTMLLib::div('', array('id'=>'downloadsChart')));
$page->addScript('
var margin = {top: 20, right: 80, bottom: 30, left: 50},
    width = 960 - margin.left - margin.right,
    height = 500 - margin.top - margin.bottom;

var parseDate = d3.time.format("%Y%m").parse;

var x = d3.time.scale()
    .range([0, width]);

var y = d3.scale.linear()
    .range([height, 0]);

var color = d3.scale.category10();

var xAxis = d3.svg.axis()
    .scale(x)
    .orient("bottom");

var yAxis = d3.svg.axis()
    .scale(y)
    .orient("left");
    
var line = d3.svg.line()
    .interpolate("basis")
    .x(function(d) { return x(d.date); })
    .y(function(d) { return y(d.counts); });

var svg = d3.select("#downloadsChart").append("svg")
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom)
  .append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

d3.csv("structuresData3.txt?2", function(error, data){
    color.domain(d3.keys(data[0]).filter(function(key){ return key !== "date"; }));
    data.forEach(function(d){
        d.date = parseDate(d.date);
    });
    
    var counts = color.domain().map(function(name){
        return {
            name: name,
            values: data.map(function(d){
                return {
                    date: d.date,
                    counts: +d[name]
                }
            })
        }
    });

    x.domain(d3.extent(data, function(d){ return d.date }));
    y.domain([
        d3.min(counts, function(c){ return d3.min(c.values, function(v){ return v.counts }); }),
        d3.max(counts, function(c){ return d3.max(c.values, function(v){ return v.counts }); })
    ]);
    
    
    svg.append("g")
      .attr("class", "x axis")
      .attr("transform", "translate(0," + height + ")")
      .call(xAxis);

    svg.append("g")
      .attr("class", "y axis")
      .call(yAxis)
    .append("text")
      .attr("transform", "rotate(-90)")
      .attr("y", 6)
      .attr("dy", ".71em")
      .style("text-anchor", "end")
      .text("counts");

    var count = svg.selectAll(".count")
        .data(counts)
        .enter()
        .append("g")
        .attr("class", "count");
    count.append("path")
        .attr("class", "line")
        .attr("d", function(d){ return line(d.values); })
        .style("stroke", function(d){ return color(d.name); });
    count.append("text")
        .datum(function(d) { return {name: d.name, value: d.values[d.values.length - 1]}; })
        .attr("transform", function(d) { return "translate(" + x(d.value.date) + "," + y(d.value.counts) + ")"; })
        .attr("x", 10)
        .attr("dy", ".2em")
        .text(function(d) { return d.name; });
});', 'load');
echo $page;
?>
