<?php
require('/home/mackeral/Web/phpIncludes/config.php');
$chunks = explode(', ', $request['q']);
$author = "{$chunks[1]} {$chunks[0]}";

$m = new MongoClient();
$db = $m->selectDB('repos');
$collection = new MongoCollection($db, 'citations');
$citations = array();
$authorRE = new MongoRegEx("/" . $chunks[0] . ", " . $chunks[1][0] . "/");
$cursor = $collection->find(array('dcCreator'=>$authorRE));
$cursor->sort(array('dcTitle'=>1));
foreach($cursor as $citation) $citations[] = HTMLLib::li(HTMLLib::a($citation['dcTitle'], "/stats/citation.php?identifier={$citation['identifier']}"), array('id'=>$citation['dcIdentifier'][0]));

$page = new StatsPage("Personal Author: $author");
$page->addInternalCSS('.badge { position: relative; bottom: 1px; margin-left: 1em; }');
$page->addContent(HTMLLib::p('profile-like. includes info from authority service, photo (?)', array('class'=>'lead')));
$page->addContent(HTMLLib::ol($citations, null, false));
$page->addScript('$.getJSON("/stats/ajax.php?action=downloads", function(data) {
    $("ol li").each(function(i,liO){
        //$(liO).append($("span").text(data[$(liO).attr("id")]));
        if($(liO).attr("id") in data) $(liO).append("<span class=badge>" + data[$(liO).attr("id")] + "</span>");
    });
});', 'load');
echo $page;
?>
