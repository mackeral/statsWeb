<?php
require('/home/mackeral/Web/phpIncludes/config.php');
$page = new StatsPage('Structures');

$m = new MongoClient('mongodb://lawlibrary:unclezeb@ds063287.mongolab.com:63287/repos');
$db = $m->selectDB('repos');
$citations = new MongoCollection($db, 'citations');
$counts = $citations->group(
    array("setSpec"=>1),
    array("count"=>0),
    "function(cur, result){ result.count += 1 }"
);

// bjil => SetSpec object
$setSpecs = array();
foreach($counts['retval'] as $setSpec) {
    $chunks = explode(':', $setSpec['setSpec']);
    $setSpecs[$chunks[1]] = new SetSpec($chunks[1], $setSpecLabels[$chunks[1]]);
    $setSpecs[$chunks[1]]->setCitationCount($setSpec['count']);
}

// dcIdentifier => setSpec
$cursor = $citations->find(array(), array('dcIdentifier'=>true, 'setSpec'=>true));
$dcIdentifiers = array();
foreach($cursor as $citation) $dcIdentifiers[$citation['dcIdentifier'][0]] = substr($citation['setSpec'], 12);

// dcIdentifier => downloadCount
$statistics = new MongoCollection($db, 'statistics');
$results = $statistics->aggregate(
    array(
        '$group' => array(
            '_id' => '$dcIdentifier',
           'total' => array('$sum' => '$downloads')
        )
    )
);
$downloads = array();
foreach($results['result'] as $result) $downloads[$result['_id']] = $result['total'];

foreach($downloads as $dcIdentifier=>$downloadCount) $setSpecs[$dcIdentifiers[$dcIdentifier]]->addDownloadCount($downloadCount);

$journals = array('aalj', 'bjalp', 'bjil', 'bjcl', 'bjesl', 'californialawreview');
$structures = array();
$structures['Journals'] = new Structure('Journals');
$structures['Faculty Publications'] = new Structure('Faculty Publications');
foreach($setSpecs as $id=>$label){
    if(in_array($id, $journals)) {
        $structures['Journals']->addCitationCount($label->getCitationCount());
        $structures['Journals']->addDownloadCount($label->getDownloadCount());
    }
    else if($id=='facpubs') {
        $structures['Faculty Publications']->addCitationCount($label->getCitationCount());
        $structures['Faculty Publications']->addDownloadCount($label->getDownloadCount());
    }
}

$trs = array();
foreach($structures as $structure){
    $trs[] = HTMLLib::tr(array(
        HTMLLib::a($structure->label, "structure.php?label={$structure->label}"),
        $structure->citationCount,
        $structure->downloadCount
    ));
}

$page->addContent(HTMLLib::table(
    $trs,
    array('class'=>'sortable'),
    HTMLLib::tr(array(
        HTMLLib::td('Structure', array('data-defaultsort'=>'asc'), true),
        HTMLLib::td('# documents', null, true),
        HTMLLib::td('# downloads', null, true)
    ), false, null, true)
));

echo $page;
?>
