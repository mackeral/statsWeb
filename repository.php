<?php
require('/var/www/phpIncludes/config.php');
$institution = $shortNames[$request['institution']];

$institutions = $authors->distinct('institution');
if(!in_array($institution, $institutions)) die('invalid invocation');

$page = new StatsPage("Repository: {$request['institution']}", $logInOut);
$page->addInternalCSS('.repoContainer .nav > li > a { padding: 10px 11px; } .nav.nav-tabs { margin: 1em 0; }');

$page->addContent(HTMLLib::form(null, null, HTMLLib::div(
    HTMLLib::div(
        HTMLLib::label('author', 'Personal Author', array('class'=>'control-label col-lg-2')) . 
        HTMLLib::div(HTMLLib::input('author', array('id'=>'author', 'placeholder'=>'Last, First', 'class'=>'form-control')), array('class'=>'col-lg-8'))
    , array('class'=>'form-group'))
), array('class'=>'form-horizontal', 'role'=>'form')));
$page->addContent(HTMLLib::form(null, null, HTMLLib::div(
    HTMLLib::div(
        HTMLLib::label('structure', 'Structure', array('class'=>'control-label col-lg-2')) . 
        HTMLLib::div(HTMLLib::input('structure', array('id'=>'structure', 'placeholder'=>'Journals...', 'class'=>'form-control')), array('class'=>'col-lg-8'))
    , array('class'=>'form-group'))
), array('class'=>'form-horizontal', 'role'=>'form')));

$dcCreators = array();
$cursor = $citations->distinct('dcCreator');
foreach($cursor as $dcCreator) $dcCreators[trim(strtoupper($dcCreator), ' ,')] = trim($dcCreator, ', ');
ksort($dcCreators);

$pager = array();
foreach ($dcCreators as $dcCreator) {
    $authorLink = HTMLLib::a($dcCreator, "/statsWeb/personalAuthor.php?q=$dcCreator");
    if(!array_key_exists(strtoupper($dcCreator[0]), $pager)) $pager[strtoupper($dcCreator[0])] = array();
    $pager[strtoupper($dcCreator[0])][] = $authorLink;
    $allAuthors[] = $authorLink;
}
$tabs = array();
$tabs[] = HTMLLib::li(HTMLLib::a('All', '#All', array('data-toggle'=>'tab')), array('class'=>'active'));
$indexDivs = array();
$indexDivs[] = HTMLLib::div(HTMLLib::ul($allAuthors, array('class'=>'list-unstyled')), array('class'=>'tab-pane active', 'id'=>'All'));
foreach($pager as $index => $authorList) {
    $tabs[] = HTMLLib::li(HTMLLib::a($index, "#$index", array('data-toggle'=>'tab')));
    $indexDivs[] = HTMLLib::div(HTMLLib::ul($authorList, array('class'=>'list-unstyled')), array('class'=>'tab-pane', 'id'=>$index));
}
$page->addContent(HTMLLib::ul($tabs, array('class'=>'nav nav-tabs'), false));
$page->addContent(HTMLLib::div(implode('', $indexDivs), array('class'=>'tab-content')));


$page->addScript("
$('#author').typeahead({ name: 'author', prefetch: 'ajax.php?action=personalAuthors&institution=$institution' })
    .bind('typeahead:selected', function(obj, datum){ location.href = 'personalAuthor.php?q=' + datum.value + '&r=$institution';});
$('#structure').typeahead({name: 'structure', prefetch: 'ajax.php?action=words' })
    .bind('typeahead:selected', function(obj, datum){ location.href = 'structure.php?q=' + datum.value + 'r=thisRepository'; });
");

echo $page;
?>