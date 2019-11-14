<?php
/*
 * PHP Framework
 * Version 2012.12
 * Mihkel Oviir
 * 
 * Class db_mysql
 */

class db_mysql extends Core {
	
	public function connect($host,$port,$db,$user,$passwd) {
		mysql_connect($host,$uuser,$passwd) or die(mysql_error());
		mysql_select_db($db) or die(mysql_error());
		$this->query("SET NAMES utf8");
		return true;
	}
	
	public function disconnect(){
		mysql_close();
	}
	
	public function query($q){
		$result = mysql_query($q) or die(mysql_error().': '.$q);
		return $result;
		return 'true';
	}
	
}

?>