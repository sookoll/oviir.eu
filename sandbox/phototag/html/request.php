<?php

// include configuration
include 'config.php';

mysql_connect(DB_HOST, DB_USER, DB_PWD) or die(mysql_error());
mysql_select_db(DB_NAME) or die(mysql_error());
mysql_query("SET NAMES utf8");

$t = $n = array();

$q = "SELECT firstname,lastname FROM oviiride_kontaktid WHERE active=1 AND deleted=0 ORDER BY firstname,lastname";
$result = mysql_query($q) or die(mysql_error() . ': ' . $q);

while($row = mysql_fetch_assoc($result)) {
	$t[] = $row['firstname'] . ' ' . $row['lastname'];
}

//print_r($t);
echo json_encode(array('status'=>1,'data'=>$t));

?>
