<?php 
$mysqli = new mysqli("localhost", "root", "root", "mlecturedownload");
if ($mysqli->connect_errno) {
	echo json_encode(array('error', $mysqli->connect_errno));
}

$result = $mysqli->query("SELECT * FROM download_count");
$result->data_seek(0);
$result = $result->fetch_assoc();
$count = $result['count'];

$result = array('count' => $count);
echo json_encode($result);