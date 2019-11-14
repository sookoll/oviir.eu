<?php

// include configuration
include 'config.php';

mysql_connect(DB_HOST, DB_USER, DB_PWD) or die(mysql_error());
mysql_select_db(DB_NAME) or die(mysql_error());
mysql_query("SET NAMES utf8");

$t = $n = array();

$q = "SELECT * FROM dev_tree WHERE whois=1 AND generation=1";
$result = mysql_query($q) or die(mysql_error() . ': ' . $q);

$i = 0;
while($row = mysql_fetch_assoc($result)) {
	$t[$i]['id'] = $row['id'];
	$t[$i]['name'] = $row['firstname'] . ' ' . $row['lastname'];
	$t[$i] = array_merge($t[$i],item($row['id']));
	$i++;
}

$q = "SELECT * FROM dev_tree WHERE boundwith IS NULL";
$result = mysql_query($q) or die(mysql_error() . ': ' . $q);
$i = 0;
while($row = mysql_fetch_assoc($result)) {
	$n[$i]['id'] = $row['id'];
	$n[$i]['name'] = $row['firstname'] . ' ' . $row['lastname'];
	$n[$i] = array_merge($n[$i],item($row['id']));
	$i++;
}

mysql_close();

//print_r($t);
echo json_encode(array('status'=>1,'data'=>array('bounded'=>$t,'nobounded'=>$n)));

function item($id) {
	$a = array();
	$q = "SELECT * FROM dev_tree WHERE boundwith=" . $id;
	$result = mysql_query($q) or die(mysql_error() . ': ' . $q);
	while($row = mysql_fetch_assoc($result)) {
		if($row['whois'] == 1) {
			$a['childrens'][] = array_merge(array('id'=>$row['id'],'name'=>$row['firstname'].' '.$row['lastname']),item($row['id']));
		} else
			$a['partners'][] = array('id'=>$row['id'],'name'=>$row['firstname'].' '.$row['lastname']);
	}
	return $a;
}

?>
