<?php
/*
 * PHP Framework
 * Version 2012.12
 * Mihkel Oviir
 * 
 * Class Framework_Templator
 */
class Framework_Templator {
	
	# read file into variable
	public function readFile($page){
		$fd = fopen($page,'r');
		$page = @fread($fd, filesize($page));
		fclose($fd);
		return $page;
	}
	
	# parsing html to find php tags
	public function parse($page,$tags = array()) {
		$page=(@file_exists($page))? $this->readFile($page):$page;
		if(count($tags) > 0){
			foreach ($tags as $tag => $data) {
				$page = str_replace('{_'.$tag.'_}',$data,$page);
			}
		}
		return $page;
	}
	
}
?>