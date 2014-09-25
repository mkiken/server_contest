<?php
require 'vendor/autoload.php';

$app = new \Slim\Slim(array(
    'debug'              => true,
    'log.level'          => \Slim\Log::DEBUG,
    'log.enabled'        => true,
    'cookies.encrypt'    => true,    //cookie
));
// UTF-8
$app->contentType('text/html; charset=utf-8');

$app->group('/api', function () use ($app) {

  // テスト用
  $app->get("/test", function() use ($app){
    phpinfo();
  })->name('test');
 
  // No.1
  $app->get("/musics", function() use ($app){
    echo 'start!';
    //$db = connect_db();
    $artist_id = $app->request()->params('artist_id');
    $title = $app->request()->params('title');
    $limit = $app->request()->params('limit');
    $start = $app->request()->params('start');
    $where = 'WHERE';
    $update = false;
    if(!empty($artist_id)) {
      $where .= ' artist_id = ' . $artist_id;
      $update = true;
    }
    if(!empty($title)) {
      if($update) $where .= ' AND';
      $where .= ' title LIKE \'%' . $title . '%\'';
      $update = true;
    }
    if(!empty($limit)) {
      $limit = ' LIMIT ' . $limit;
    }
    else{
      $limit = ' LIMIT 100';
    }
    if(!empty($start)) {
      $offset = ' OFFSET ' . $start;
      $boffset = true;
    }
echo 'where is';
echo $where;
    $db = getConnection();
    $sql_query = "SELECT * FROM music ";
    $sql_query .= $where;
    $sql_query .= $limit;
    if (isset($boffset)) $sql_query .= $offset;
    $stmt   = $db->query($sql_query);
    $ret  = $stmt->fetchAll(PDO::FETCH_OBJ);
    echo '{"users": ' . json_encode($ret) . '}';
    $app->response()->setStatus(200);
    $app->response()->write(json_encode($ret));
  });

});

// phpinfo ページ
$app->get("/info", function() use ($app){
    phpinfo();
})->name("info");


//リクエストディスパッチ
$app->run();

function getConnection() {
    try {
        $db_username = "root";
        $db_password = "";
        $conn = new PDO('mysql:host=localhost;dbname=totec;charset=utf8', $db_username, $db_password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 
    } catch(PDOException $e) {
        echo 'ERROR: ' . $e->getMessage();
    }
    return $conn;
}

function connect_db() {
	$server = 'localhost'; // this may be an ip address instead
	$user = 'root';
	$pass = '';
	$database = 'totec';
	$connection = new mysqli($server, $user, $pass, $database);

	return $connection;
}
