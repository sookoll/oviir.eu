<?php
/*
 * PHP Framework
 * Version 2012.12
 * Mihkel Oviir
 * 
 * Class Core
 */

class Core {
	var $conf;
	var $sess;
	var $db;
	
	function __construct(){}
	
	function init($configuration_files){
		$conf = array();
		$type = gettype($configuration_files);
		switch($type){
			case 'string':
				$conf = $this->parseConfigurationFiles($configuration_files);
			break;
			case 'array':
				for($i=0;$i<count($configuration_files);$i++){
					$conf = array_merge($conf,$this->parseConfigurationFiles($configuration_files[$i]));
				}
			break;
		}
		
		// set configuration as class property
		$this->conf = $this->buildConfiguration($conf);
		
		// test if application is enabled
		// TODO: create disabled page
		if($conf['application']['status'] != 'enabled')
			die('disabled');
		
		// add session class
		if($conf['application']['use_session'] != 0){
			$this->autoLoader('session',false);
			$this->sess = session::getInstance();
		}
		
		// add database class
		if($conf['application']['use_database'] != ''){
			$this->db = $this->autoLoader('db_'.$conf['application']['use_database']);
		}
	}
	
	/*
	 * parseConfigurationFiles
	 * @ $configuration_file - input file in ini format
	 * return configuration array
	 */
	private function parseConfigurationFiles($configuration_file){
		return parse_ini_file($configuration_file, true);
	}
	
	/*
	 * buildConfiguration
	 * @ $configuration
	 * return builded configuration
	 */
	private function buildConfiguration($conf){
		return $conf;
	}
	
	/*
	 * autoLoader
	 * @ $name
	 * return builded class object
	 */
	public function autoLoader($name,$callClass = true){
		if(!$this->isValidClassName($name)){
			
		}
		
		if(!class_exists($name)){
			include_once $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$this->conf['application']['path'].DIRECTORY_SEPARATOR.$this->conf['application']['library_path'].DIRECTORY_SEPARATOR.$name.'.php';
			if($callClass)
				return new $name();
		}
	}
	
	/*
	 * isValidClassName
	 * @ $name
	 * return boolean
	 */
	private function isValidClassName($name){
		return true;
	}
	
	public function redirect($url = null) {
		if(empty($url))
			$url = $this->conf['application']['url'];
		header('Location: '.$url);
		exit();
	}
	
	public function send404(){
		header("HTTP/1.0 404 Not Found");
		echo "<h1>404 Not Found</h1>";
		echo "The page that you have requested could not be found.";
	    exit();
	}
}

?>