<?php
/*
 * Oviir's family contacts
 * constants and configuration
 * 
 * Creator: Mihkel Oviir
 * 04.2011
 * 
 */

class Functions {
	
	# make db connection
	function connection() {
		mysql_connect(DB_HOST,DB_USER,DB_PWD) or die(mysql_error());
		mysql_select_db(DB_NAME) or die(mysql_error());
		$this->makeQuery("SET NAMES utf8");
		return true;
	}
	
	# close db connection
	function connection_close(){
		mysql_close();
	}
	
	# method to make query
	function makeQuery($q){
		$result = mysql_query($q) or die(mysql_error().': '.$q);
		return $result;
	}
	
		# move to url
	function gotourl($url) {
		if(empty($url)) $url = URL;
		header('Location: '.$url);
	}
	
	# read file into variable
	function parseFile($page){
		$fd = fopen($page,'r');
		$page = @fread($fd, filesize($page));
		fclose($fd);
		return $page;
	}
	
	# parsing html to find php tags
	function replace_tags($page,$tags = array()) {
		$page=(@file_exists($page))? $this->parseFile($page):$page;
		if(sizeof($tags) > 0){
			foreach ($tags as $tag => $data) {
				$page = str_replace('{_'.$tag.'_}',$data,$page);
			}
		}
		return $page;
	}
	
	# definesarray
	function definesArray(){
		$data = array();
		$data['def-libs']=HTML_LIBS;
		$data['def-tmpl']=HTML_TMPL;
		return $data;
	}
}

$func = new Functions();

?>