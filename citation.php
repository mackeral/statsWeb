<?php
require('/home/mackeral/Web/phpIncludes/config.php');
$m = new MongoClient('mongodb://lawlibrary:unclezeb@ds063287.mongolab.com:63287/repos');
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
$page->addContent(HTMLLib::p('metadata from any kind of harvest, e.g. name authority', array('class'=>'lead')));

$statistics = new MongoCollection($db, 'statistics');
$downloads = array();
$cursor = $statistics->find(array('dcIdentifier'=>$citation['dcIdentifier'][0]), array('_id'=>0, 'downloads'=>1, 'dlDate'=>1));
$cursor->sort(array('dlDate'=>1));
foreach($cursor as $statistic) $downloads[] = '["' . date('Y-m-d', $statistic['dlDate']->sec) . '", ' . $statistic['downloads'] . ']';
$page->addScript('var statistics = [' . implode(',', $downloads) . '];');
$page->addScript('/stats/js/d3/d3.v3.min.js');
$page->addInternalCSS('
#downloadsChart {
  font: 10px sans-serif;
}

.axis path, .axis line {
  fill: none;
  stroke: #000;
  shape-rendering: crispEdges;
}

.x.axis path {
  display: none;
}

.line {
  fill: none;
  stroke: steelblue;
  stroke-width: 1.5px;
}');
$page->addContent(HTMLLib::div('', array('id'=>'downloadsChart')));
$page->addScript('
var margin = {top: 20, right: 20, bottom: 30, left: 50},
    width = 960 - margin.left - margin.right,
    height = 200 - margin.top - margin.bottom;

var parseDate = d3.time.format("%Y-%m-%d").parse;

var x = d3.time.scale()
    .range([0, width])

var y = d3.scale.linear()
    .range([height, 0]);

var xAxis = d3.svg.axis()
    .scale(x)
    .orient("bottom");

var yAxis = d3.svg.axis()
    .scale(y)
    .orient("left");

var line = d3.svg.line()
    .x(function(d) { return x(d.date); })
    .y(function(d) { return y(d.close); });

var svg = d3.select("body").append("svg")
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom)
  .append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

var data = statistics.map(function(d) {
  return {
     date: parseDate(d[0]),
     close: d[1]
  };
  
});
  
x.domain(d3.extent(data, function(d) { return d.date; }));
y.domain(d3.extent(data, function(d) { return d.close; }));

svg.append("g")
  .attr("class", "x axis")
  .attr("transform", "translate(0," + height + ")")
  .call(xAxis);

svg.append("g")
  .attr("class", "y axis")
  .call(yAxis)
.append("text")
  .attr("y", 6)
  .attr("x", 10)
  .attr("dy", ".71em")
  .text("downloads");

svg.append("path")
  .datum(data)
  .attr("class", "line")
  .attr("d", line);
', 'load');
echo $page;
?>
