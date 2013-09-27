<?php
require('/home/mackeral/Web/phpIncludes/config.php');

$institution = $request['institution'];

$m = new MongoClient();
$db = $m->selectDB('repos');
$collection = new MongoCollection($db, 'authors');
$institutions = $collection->distinct('institution');
if(!in_array($institution, $institutions)) die('invalid invocation');

$page = new StatsPage("Institutional Author: $institution");
$page->addInternalCSS('.nav.nav-tabs { margin: 1em 0; }');

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

$page->addContent(HTMLLib::h(2, "Personal authors at $institution"));

$cursor = $collection->find(array('institution' => $institution));
$cursor->sort(array('lname'=>1, 'fname'=>1));
$authors = array();
$pager = array();
foreach ($cursor as $doc) {
    $authorLink = HTMLLib::a("{$doc['lname']}, {$doc['fname']}", "/stats/personalAuthor.php?q={$doc['lname']}, {$doc['fname']}");
    if(!array_key_exists($doc['lname'][0], $pager)) $pager[$doc['lname'][0]] = array();
    $pager[$doc['lname'][0]][] = $authorLink;
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
$('#author').typeahead({name: 'author', prefetch: 'ajax.php?action=personalAuthors&institution=$institution' }).bind('typeahead:selected', function(obj, datum){ location.href = 'personalAuthor.php?q=' + datum.value + 'r=thisRepository'; });
$('#structure').typeahead({name: 'structure', prefetch: 'ajax.php?action=words' }).bind('typeahead:selected', function(obj, datum){ location.href = 'structure.php?q=' + datum.value + 'r=thisRepository'; });
");
echo $page;