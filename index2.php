<?php
require('/home/mackeral/Web/phpIncludes/config.php');
$page = new StatsPage('Browse and Search');
$page->addInternalCSS('#q { width: 500px; }');
$page->addContent(HTMLLib::form(null, null,
    HTMLLib::div(
        HTMLLib::label('q', 'Search term', array('class'=>'sr-only')) . 
        HTMLLib::input('q', array('id'=>'q', 'class'=>'form-control', 'placeholder'=>'search...')),
        array('class'=>'form-group')
    ) . 
    HTMLLib::button('Search', null, array('class'=>'btn btn-default'), 'submit'),
    array('class'=>'form-inline', 'role'=>'form')
));
$links = array(
    HTMLLib::a('Repositories', '/stats/repositories.php'),
    HTMLLib::a('Institutional Authors', '/stats/institutionalAuthors.php'),
    HTMLLib::a('Personal Authors', '/stats/personalAuthors.php'),
    HTMLLib::a('Structures', '/stats/structures.php'),
    HTMLLib::a('Document Types', '/stats/types.php')
);
$page->addContent(HTMLLib::p('Browse: ' . implode(' | ', $links), array('style'=>'margin-top: 2em;')));
echo $page;
?>
