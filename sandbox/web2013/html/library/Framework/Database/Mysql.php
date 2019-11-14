<?php
/*
 * PHP Framework
 * Version 2012.12
 * Mihkel Oviir
 * 
 * Class db_mysql
 */

class Framework_Database_Mysql {
	
	public function connect($host,$port,$db,$user,$passwd) {
		mysql_connect($host,$user,$passwd) or die(mysql_error());
		mysql_select_db($db) or die(mysql_error());
		$this->query("SET NAMES utf8");
		return true;
	}
	
	public function disconnect(){
		mysql_close();
	}
	
	public function query($q){
		// debug
		$result = mysql_query($q) or die(mysql_error().': '.$q);
		// deploy
		//$result = mysql_query($q) or die('database error');
		return $result;
	}
	
}

?>