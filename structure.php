<?php
require('/home/mackeral/Web/phpIncludes/config.php');
$page = new StatsPage($request['label']);

switch($request['label']){
    case 'Journals':
        // show list of journals with citation and download counts
        
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
        
        $trs = array();
        foreach($journals as $journal){
            $trs[] = HTMLLib::tr(array(
                HTMLLib::a($setSpecLabels["publication:{$journal}"], "#"),
                $setSpecs[$journal]->citationCount,
                $setSpecs[$journal]->downloadCount
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
        break;
    case 'Faculty Publications':
        
        break;
}
echo $page;
?>
