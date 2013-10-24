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
    if($k=='dcIdentifier') $dcIdentifier = $v;
}


$statistics = new MongoCollection($db, 'statistics');
//db.statistics.aggregate({$match: {"dcIdentifier": "http://scholarship.law.berkeley.edu/facpubs/329", "dlDate": {$gte: new ISODate("2013-09-00T00:00:00Z")}}}, {$group: {_id: "dcIdentifier", dlThisMonth: { $sum : "$downloads" }}})
$pipeline = array(
   array('$match'=>array(
        'dcIdentifier'=>$dcIdentifier,
        'dlDate'=>array(
            '$gte'=> new MongoDate(strtotime("2013-09-00 00:00:00"))
        )
    )), array('$group'=>array(
        '_id'=>"dcIdentifier",
        'dlThisMonth'=>array(
            '$sum'=>'$downloads'
        )
    ))
);
$results = $statistics->aggregate($pipeline);
$itemDetails .= HTMLLib::element('dt', 'month so far') . HTMLLib::element('dd', $results['result'][0]['dlThisMonth']);


$page = new StatsPage($citation['dcTitle']);
$page->addContent(HTMLLib::element('dl', $itemDetails, array('class'=>'dl-horizontal')));
$page->addContent(HTMLLib::p('metadata from any kind of harvest, e.g. name authority', array('class'=>'lead')));

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
$page->addContent(HTMLLib::div('', array('id'=>'downloadsChart')));
$page->addScript('
var margin = {top: 20, right: 80, bottom: 30, left: 50},
    width = 960 - margin.left - margin.right,
    height = 500 - margin.top - margin.bottom;

var parseDate = d3.time.format("%Y-%m-%d").parse;

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

d3.csv("downloads.php?dcIdentifier=' . $citation['dcIdentifier'][0] . '", function(error, data){
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
        .attr("x", -10)
        .attr("dy", "-2em")
        .text(function(d) { return d.name; });
});
', 'load');
echo $page;
?>
