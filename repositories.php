<?php
require('/var/www/phpIncludes/config.php');
$page = new StatsPage('Repositories', $logInOut);

$counts = $citations->group(
    array("dcPublisher"=>1),
    array("count"=>0),
    "function(cur, result){ result.count += 1 }"
);

$repositories = array();
foreach($counts['retval'] as $repository) {
    $repositories[$repository['dcPublisher']] = new Repository($repository['dcPublisher']);
    $repositories[$repository['dcPublisher']]->setCitationCount($repository['count']);
    $repositories[$repository['dcPublisher']]->setIdentifierPrefix($dcIdentifiers[$repository['dcPublisher']]);
}

$result = $mysql->query('select repo,sum(dlN) as downloads from stats group by repo;');
while($row = $result->fetch_assoc())
	$repositories[$row['repo']]->setDownloadCount($row['downloads']);

$trs = array();
foreach($repositories as $repository){
    $trs[] = HTMLLib::tr(array(
        HTMLLib::a($repository->label, "/statsWeb/repository.php?institution={$repository->label}"),
        $repository->citationCount,
        $repository->downloadCount
    ));
}

$trs[] = HTMLLib::tr(array(
    HTMLLib::a('Duke', '/statsWeb/repository.php?institution=Duke'),
    '123',
    '456'
));
$trs[] = HTMLLib::tr(array(
    HTMLLib::a('Harvard', '/statsWeb/repository.php?institution=Harvard'),
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
