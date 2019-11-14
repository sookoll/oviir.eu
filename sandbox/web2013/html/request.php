<?php

// new application
if (!defined('APP_PATH')) {
	define('APP_PATH', dirname(__FILE__));
}

// include and call framework
include APP_PATH.'/library/Framework.php';
$app = new Framework();

// set configuration
$app->setConfig('./library/Framework/Configs/core.ini');

// load support classes if neccessary
$app->loadSupportClasses();

// set public user
if(!isset($_SESSION['Framework']))
	$_SESSION['Framework'] = array();

if(!isset($_SESSION['Framework']['auth_status'])){
	$_SESSION['Framework']['auth_status'] = 'public';
	$_SESSION['Framework']['current_user'] = array(
		id => 0,
		auth_level => 'guest',
		full_name => 'Public viewer',
		status => 'hidden'
	);
}

$tags = array();

// connect to db
$app->db->connect($app->conf['database']['host'],$app->conf['database']['port'],$app->conf['database']['name'],$app->conf['database']['user'],$app->conf['database']['pass']);

// users administration
$app->users = new Framework_Modules_login_modul($app);

// add dynamic modules loading via $_GET parameter
$app->enableRequestModulsFromGet();

// call a method
if(isset($_GET['m']) && method_exists($app->modul,$_GET['m'])){
	$data = $app->modul->$_GET['m']();
	if($data['content_type']=='json')
		$data['content'] = json_encode($data['content']);
	
	header('Content-Type: text/'.$data['content_type']);
	echo $data['content'];
}else
	$app->send404();

// disconnect from db
$app->db->disconnect();

?>