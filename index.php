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


  // No.9
  $app->get("/playlists/:name", function($name) use ($app){

    $db = getConnection();

// 楽曲の存在確認
    $sql_query = "SELECT name FROM playlist WHERE `name` = " . $name;
echo $sql_query;
    $stmt   = $db->query($sql_query);
    $ret  = $stmt->fetchAll(PDO::FETCH_OBJ);
if(empty($ret)){
	    $app->response()->setStatus(404);
	  return;
}
    $sql_query = "SELECT music_id FROM playlist_detail WHERE playlist_name = " . $name . " ORDER BY number";
    $stmt   = $db->query($sql_query);
    $ret  = $stmt->fetchAll(PDO::FETCH_OBJ);

$arr = array();

      for($i = 0; $i <= count($ret); $i++){
$sql_query = "SELECT * FROM music WHERE id = " . $ret[$i]['music_id'];
    $stmt   = $db->query($sql_query);
    $tmp  = $stmt->fetchAll(PDO::FETCH_OBJ);
$arr[] = $tmp;
      }
    $app->response()->write(json_encode($arr));
    $app->response()->setStatus(200);

  });




  // No.6
  $app->get("/musics/:id/play", function($id) use ($app){

    $db = getConnection();

// 楽曲の存在確認
    $sql_query = "SELECT id FROM music WHERE id = " . $id;
    $stmt   = $db->query($sql_query);
    $ret  = $stmt->fetchAll(PDO::FETCH_OBJ);
if(empty($ret)){
	    $app->response()->setStatus(404);
	  return;
}
$sql = 'INSERT INTO play_history (music_id) values (?)';
    $stmt = $db->prepare($sql);
    $flag = $stmt->execute(array($id));

    if ($flag){
	    $app->response()->setStatus(204);
    }else{
      $app->response()->setStatus(404);
    }

  });



 
  // No.7
  $app->get("/musics/times", function() use ($app){
    //$db = connect_db();
    $id = $app->request()->params('id');
    $db = getConnection();
$arr = array();
    if(empty($id)) {

      for($i = 1; $i <= 100; $i++){
$sql_query = "SELECT id FROM play_history WHERE music_id = " . $i;
    $stmt   = $db->query($sql_query);
    $ret  = $stmt->fetchAll(PDO::FETCH_OBJ);
$arr[] = array("id" => $i, "times" => count($ret));
      }
    }
    else{
    $sql_query = "SELECT id FROM play_history WHERE music_id = " . $id;
    $stmt   = $db->query($sql_query);
    $ret  = $stmt->fetchAll(PDO::FETCH_OBJ);
$arr[] = array("id" => $id, "times" => count($ret));
    }
    $app->response()->write(json_encode($arr));
    $app->response()->setStatus(200);
});



  // No.5
  $app->delete("/musics/:id", function($id) use ($app){

    $db = getConnection();

// 楽曲の存在確認
    $sql_query = "SELECT id FROM music WHERE id = " . $id;
    $stmt   = $db->query($sql_query);
    $ret  = $stmt->fetchAll(PDO::FETCH_OBJ);
if(empty($ret)){
	    $app->response()->setStatus(404);
	  return;
}

    $sql_query = "DELETE FROM music WHERE id = " . $id;
    $stmt   = $db->prepare($sql_query);
    $ret  = $stmt->execute();
    if($ret){
    $app->response()->setStatus(204);
    }
    else {
    $app->response()->setStatus(404);
    }
  });


  // No.4
  $app->put("/musics/:id", function($id) use ($app){
    $artist_id = $app->request()->params('artist_id');
    $title = $app->request()->params('title');
    $outline = $app->request()->params('outline');
	if (empty($artist_id) || empty($title)){
	    $app->response()->setStatus(400);
	  return;
	}
    $db = getConnection();
    $sql_query = "SELECT id FROM artist WHERE id = " . $artist_id;
    $stmt   = $db->query($sql_query);
    $ret  = $stmt->fetchAll(PDO::FETCH_OBJ);
if(empty($ret)){
	    $app->response()->setStatus(400);
	  return;
}
// 楽曲の存在確認
    $sql_query = "SELECT id FROM music WHERE id = " . $id;
    $stmt   = $db->query($sql_query);
    $ret  = $stmt->fetchAll(PDO::FETCH_OBJ);
if(empty($ret)){
	    $app->response()->setStatus(404);
	  return;
}

// 更新
    $sql = 'UPDATE music SET artist_id=?, title=?, outline=? WHERE id=?';
if( ! isset($outline)) $outline = '';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(1, $artist_id);
    $stmt->bindValue(2, $title);
    $stmt->bindValue(3, $outline);
    $stmt->bindValue(4, $id);
    $flag = $stmt->execute();

    if ($flag){
	    $app->response()->setStatus(204);
    }
else{
      $app->response()->setStatus(404);
    }
  });



  // No.3
  $app->post("/musics", function() use ($app){
    $artist_id = $app->request()->params('artist_id');
    $title = $app->request()->params('title');
    $outline = $app->request()->params('outline');
	if (empty($artist_id) || empty($title)){
	    $app->response()->setStatus(400);
	  return;
	}
    $db = getConnection();
    $sql_query = "SELECT id FROM artist WHERE id = " . $artist_id;
    $stmt   = $db->query($sql_query);
    $ret  = $stmt->fetchAll(PDO::FETCH_OBJ);
if(empty($ret)){
	    $app->response()->setStatus(400);
	  return;
}
// 登録
    $sql = 'INSERT INTO music (artist_id, title, outline) values (?, ?, ?)';
    $stmt = $db->prepare($sql);
if( ! isset($outline)) $outline = '';
    $flag = $stmt->execute(array($artist_id, $title, $outline));

    if ($flag){
	    $app->response()->setStatus(201);
     // オートインクリメント値を取り出す
     $id = $db->lastInsertId();
     $app->response()->write('http://54.168.202.21/api/musics/' . $id);
    }else{
      $app->response()->setStatus(404);
    }
  });

  // No.2
  $app->get("/musics/:id", function($id) use ($app){
    $db = getConnection();
    $sql_query = "SELECT * FROM music WHERE id = " . $id;
    $stmt   = $db->query($sql_query);
    $ret  = $stmt->fetchAll(PDO::FETCH_OBJ);
    if(empty($ret)){
    $app->response()->setStatus(404);
    }
    else {
    $app->response()->setStatus(200);
    $app->response()->write(json_encode($ret[0]));
    }
  });
 
  // No.1
  $app->get("/musics", function() use ($app){
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
    $db = getConnection();
    $sql_query = "SELECT * FROM music ";
    if($update) $sql_query .= $where;
    $sql_query .= $limit;
    if (isset($boffset)) $sql_query .= $offset;
    $stmt   = $db->query($sql_query);
    $ret  = $stmt->fetchAll(PDO::FETCH_OBJ);
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
