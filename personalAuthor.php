<?php
require('/var/www/phpIncludes/config.php');
$chunks = explode(', ', $request['q']);
$author = "{$chunks[1]} {$chunks[0]}";

$authorCitations = array();
$authorRE = new MongoRegEx("/" . $chunks[0] . ", " . $chunks[1][0] . "/");
$cursor = $citations->find(array('dcCreator'=>$authorRE));
$cursor->sort(array('dcTitle'=>1));
foreach($cursor as $citation) $authorCitations[] = HTMLLib::li(HTMLLib::a($citation['dcTitle'], "/statsWeb/citation.php?identifier={$citation['identifier']}"), array('id'=>$citation['dcIdentifier'][0]));

$page = new StatsPage("Personal Author: $author", $logInOut);
$page->addInternalCSS('.badge { position: relative; bottom: 1px; margin-left: 1em; }');
$page->addContent(HTMLLib::p('profile-like. includes info from authority service, photo (?)', array('class'=>'lead')));
$page->addContent(HTMLLib::ol($authorCitations, null, false));
$page->addScript('$.getJSON("/statsWeb/ajax.php?action=downloads", function(data) {
    $("ol li").each(function(i,liO){
        if($(liO).attr("id") in data) $(liO).append("<span class=badge>" + data[$(liO).attr("id")] + "</span>");
    });
});', 'load');
echo $page;
?>
