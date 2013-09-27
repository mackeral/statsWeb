<?php
require('/home/mackeral/Web/phpIncludes/config.php');

$institution = $request['institution'];

$m = new MongoClient();
$db = $m->selectDB('repos');
$collection = new MongoCollection($db, 'authors');
$institutions = $collection->distinct('institution');
if(!in_array($institution, $institutions)) die('invalid invocation');

$page = new StatsPage("Repository: $institution");

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


$page->addScript("$('#author').typeahead( { name: 'author', prefetch: 'ajax.php?action=personalAuthors&institution=$institution' }).bind('typeahead:selected', function(obj, datum){ location.href = 'personalAuthor.php?q=' + datum.value + '&r=$institution'; });
$('#structure').typeahead({name: 'structure', prefetch: 'ajax.php?action=words' }).bind('typeahead:selected', function(obj, datum){ location.href = 'structure.php?q=' + datum.value + 'r=thisRepository'; });");
echo $page;
?>