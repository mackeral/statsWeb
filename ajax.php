<?php
require('/var/www/phpIncludes/config.php');
switch($_REQUEST['action']){
    case 'downloads':
        if(empty($request['identifier'])){
            $result = $mysql->query('select dcID,sum(dlN) as downloads from stats group by dcID');
            $downloads = array();
            while($row = $result->fetch_assoc()) $downloads[$row['dcID']] = $row['downloads'];
        } else {
            // implement
        }
        echo json_encode($downloads);
        break;
    case 'personalAuthors':
        $institution = $_REQUEST['institution'];
/*
        $m = new MongoClient();
        $collection = $m->repos->authors;
        $institutions = $collection->distinct('institution');
        if(!in_array($institution, $institutions)) die('invalid invocation');
        $authors = array();
        $cursor = $collection->find(array('institution' => $institution));
        foreach ($cursor as $doc) $authors[] = "{$doc['lname']}, {$doc['fname']}";
        echo json_encode($authors);
*/
        break;
    case 'words':
        $lines = file('words.txt');
        foreach($lines as $line)
        	if (preg_match('/^[A-Z].{5}$/', trim($line))) $words[] = trim($line);
        echo json_encode($words);
        break;
}
?>