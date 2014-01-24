<?php
require('/var/www/phpIncludes/config.php');
$page = new StatsPage('Institutional Authors', $logInOut);
$trs = array();
$trs[] = HTMLLib::tr(array(
    HTMLLib::a('Duke', '/statsWeb/institutionalAuthor.php?institution=Duke'),
    '123',
    '456'
));
$trs[] = HTMLLib::tr(array(
    HTMLLib::a('Berkeley Law', '/statsWeb/institutionalAuthor.php?institution=Berkeley Law'),
    '123',
    '456'
));
$trs[] = HTMLLib::tr(array(
    HTMLLib::a('Harvard', '/statsWeb/institutionalAuthor.php?institution=Harvard'),
    '123',
    '456'
));
$page->addContent(HTMLLib::table(
    $trs,
    array('class'=>'sortable'),
    HTMLLib::tr(array(
        HTMLLib::td('Institution', array('data-defaultsort'=>'asc'), true),
        HTMLLib::td('# documents', null, true),
        HTMLLib::td('# downloads', null, true)
    ), false, null, true)
));
echo $page;
?>
