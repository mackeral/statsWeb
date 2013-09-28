<?php
require('/home/mackeral/Web/phpIncludes/config.php');
switch($_REQUEST['action']){
    case 'downloads':
        $m = new MongoClient();
        $db = $m->selectDB('repos');
        $collection = new MongoCollection($db, 'statistics');
        
        if(empty($request['identifier'])){
            $results = $collection->aggregate(array(
                    '$group' => array(
                        '_id' => '$identifier',
                       'total' => array('$sum' => '$downloads')
                    )
                )
            );
            
            $downloads = array();
            foreach($results['result'] as $result) $downloads[$result['_id']] = $result['total'];
        } else {
            // implement
        }
        
        echo json_encode($downloads);
        //print_r($downloads);

$out = $c->aggregate(array(
        '$group' => array(
            '_id' => '$state',
           'totalPop' => array('$sum' => '$pop')
        )
    ),
    array(
        '$match' => array('totalPop' => array('$gte' => 10*1000*1000))
    )
);



        break;
    case 'personalAuthors':
        $institution = $_REQUEST['institution'];
        $m = new MongoClient();
        $db = $m->selectDB('repos');
        $collection = new MongoCollection($db, 'authors');
        $institutions = $collection->distinct('institution');
        if(!in_array($institution, $institutions)) die('invalid invocation');
        $authors = array();
        $cursor = $collection->find(array('institution' => $institution));
        foreach ($cursor as $doc) $authors[] = "{$doc['lname']}, {$doc['fname']}";
        echo json_encode($authors);
        break;
    case 'words':
        $lines = file('words.txt');
        foreach($lines as $line)
        	if (preg_match('/^[A-Z].{5}$/', trim($line))) $words[] = trim($line);
        echo json_encode($words);
        break;
}
?>