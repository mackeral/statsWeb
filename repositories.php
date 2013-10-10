<?php
require('/home/mackeral/Web/phpIncludes/config.php');
$page = new StatsPage('Repositories');

$m = new MongoClient('mongodb://lawlibrary:unclezeb@ds063287.mongolab.com:63287/repos');
$db = $m->selectDB('repos');
$citations = new MongoCollection($db, 'citations');
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

$downloads = new MongoCollection($db, 'statistics');
$counts = $downloads->aggregate(
    array(
        '$group' => array(
            "_id" => '$repo',
            "totalDL" => array('$sum' => '$downloads')
        )
    )
);
foreach($counts['result'] as $count) $repositories[$count['_id']]->setDownloadCount($count['totalDL']);

$trs = array();
foreach($repositories as $repository){
    $trs[] = HTMLLib::tr(array(
        HTMLLib::a($repository->label, "/stats/repository.php?institution={$repository->label}"),
        $repository->citationCount,
        $repository->downloadCount
    ));
}

$trs[] = HTMLLib::tr(array(
    HTMLLib::a('Duke', '/stats/repository.php?institution=Duke'),
    '123',
    '456'
));
$trs[] = HTMLLib::tr(array(
    HTMLLib::a('Harvard', '/stats/repository.php?institution=Harvard'),
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
