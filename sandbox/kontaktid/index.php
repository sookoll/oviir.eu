<?php 
/*
 * Oviir's family contacts
 * index.php
 * 
 * Creator: Mihkel Oviir
 * 04.2011
 * 
 */

// include configuration
include 'config.php';

// wordpress
define('WP_USE_THEMES', false);
require(PATH.'../wp-blog-header.php');

// set session
include PATH_INC.'session.php';
$sess = Session::getInstance();

if ( is_user_logged_in()){
    $sess->oviir_admin_is_logged_in = true;
} else {
	if($sess->oviir_admin_is_logged_in == true){
		unset($sess->oviir_admin_is_logged_in);
	}
}

// include functions class
include PATH_INC.'functions.php';

// start connection
$func->connection();

// if we manage with page
if(STATUS != 1) {
	$html=PATH_TMPL.TEMPLATE.'/html/outoforder.html';
}
else {
	
	// if admin logged in
	if(isset($sess->oviir_admin_is_logged_in) && $sess->oviir_admin_is_logged_in === true){
		// admin class
		$class=ADMIN_MODUL;
		// if logout
		if (isset($_GET['logout'])){
			unset($sess->oviir_admin_is_logged_in);
			$func->gotourl('http://'.$_SERVER['SERVER_NAME']);
		}
	}
	// if admin not logged in
	else {
		//$class=MAIN_MODUL;
		$func->gotourl('http://'.$_SERVER['SERVER_NAME']);
	}

	// call a main object
	if($class && @file_exists(PATH_INC.$class.'.php')){
		include PATH_INC.$class.'.php';
		$main = new $class();
		$main->front();
		$data = $main->getResult();
		$html=PATH_TMPL.TEMPLATE.'/html/'.$class.'.html';
		$html = $func->replace_tags($html,$data);
	}
}
// replace all tags
$defines = $func->definesArray();
$html = $func->replace_tags($html,$defines);

// close connection
$func->connection_close();
	
echo $html;
?>