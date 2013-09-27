<?php
switch($_REQUEST['action']){
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