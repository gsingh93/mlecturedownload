<?php 
require_once "../resource/db_config.php";

$mysqli = new mysqli($host, $user, $password, $database);
if ($mysqli->connect_errno) {
	echo json_encode(array('error', $mysqli->connect_errno));
}

$result = $mysqli->query("SELECT * FROM download_count");
$result->data_seek(0);
$result = $result->fetch_assoc();
$count = $result['count'];

$result = array('count' => $count);
echo json_encode($result);