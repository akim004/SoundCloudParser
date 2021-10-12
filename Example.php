<?php

require_once 'SoundCloudParser.php';
require_once 'DbRepository.php';
require_once 'Mysql.php';

$url      = 'https://soundcloud.com/birocratic';
$host     = 'localhost';
$username = 'root';
$db       = 'db';
$port     = 33060;
$password = '123456';

$mysql        = new MySQL($host, $username, $password, $db, $port);
$dbRepository = new DbRepository($mysql);
$parser       = new SoundCloudParser($url, $dbRepository);
$data         = $parser->parse();

if ($parser->saveData($data)) {
    echo 'Done';
} else {
    echo 'Error';
}
