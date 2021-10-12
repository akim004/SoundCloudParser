Parser SoundCloud
===========

Подключение к базе данных
-------------
$host     = 'localhost';  
$username = 'username';  
$db       = 'rates';  
$port     = 33060;  
$password = 'Nokia6230i.';

$mysql        = new MySQL($host, $username, $password, $db, $port);

-------------

Репозиторий для работы с БД
-------------
$dbRepository = new DbRepository($mysql);

-------------

$url      = 'https://soundcloud.com/birocratic'; - урл откуда распарсить

$parser       = new SoundCloudParser($url, $dbRepository); - создание парсера  
$data         = $parser->parse(); - запустить парсер и получить данные  
$parser->saveData($data) - сохранить полученные данные  

-------------



