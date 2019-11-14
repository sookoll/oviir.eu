<?php 
/*
 * Oviir's family contacts
 * AJAX request handler
 * 
 * Creator: Mihkel Oviir
 * 04.2011
 * 
 */

// include configuration
include 'config.php';

// set session
include PATH_INC.'session.php';
$sess = Session::getInstance();

// include functions class
include PATH_INC.'functions.php';

// if we manage with page
if(STATUS != 1) {
	die('Not allowed!');
}
else {
	
	// start connection
	$func->connection();

	// control parameters and direct to correct class
	if(isset($_POST) && !empty($_POST['c'])&& !empty($_POST['m'])){

		$class=$_POST['c'];
		$modul=$_POST['m'];
		foreach($_POST as $k=>$v){
			$$k=$v;
		}
	} elseif(isset($_GET) && !empty($_GET['c'])&& !empty($_GET['m'])){

		$class=$_GET['c'];
		$modul=$_GET['m'];
		foreach($_GET as $k=>$v){
			$$k=$v;
		}
	}
	
	if(!empty($class) && !empty($modul)){
		include PATH_INC.$class.'.php';
		
		$m = new $class();
		$m->$modul();
		$data=$m->getResult();
		
		if($data['content_type']=='json'){
			$data['content'] = json_encode($data['content']);
		}
		
		// replace all tags
		$defines = $func->definesArray();
		$data['content'] = $func->replace_tags($data['content'],$defines);
		
		header('Content-Type: text/'.$data['content_type']);
		
		$data = $data['content'];
		
		echo $data;
	}
	
	// close connection
	$func->connection_close();
}
?>