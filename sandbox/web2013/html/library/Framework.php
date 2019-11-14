<?php
/*
 * PHP Framework
 * Version 2012.12
 * Mihkel Oviir
 * 
 * Class Core
 */

// start session
session_name('Framework');
session_start();

if (!defined('FRAMEWORK_PATH')) {
	define('FRAMEWORK_PATH', dirname(__FILE__));
	require(FRAMEWORK_PATH.DIRECTORY_SEPARATOR.'Framework'.DIRECTORY_SEPARATOR.'Autoloader.php');
}

class Framework {
	var $conf;
	var $users;
	var $db;
	var $templator;
	var $modul;
	
	function __construct(){}
	
	public function setConfig($configuration_files){
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
		if($conf['core']['status'] != 'enabled')
			die('disabled');
		
	}
	
	public function loadSupportClasses(){
		
		// add database class
		if($this->conf['core']['use_database'] != ''){
			$class = 'Framework_Database_'.$this->conf['core']['use_database'];
			$this->db = new $class();
		}
		
		// add translator class
		if($this->conf['core']['locale'] != ''){
			$this->translator = new Framework_Translator($this->conf['core']['locale']);
		}
		
		// add templator
		$this->templator = new Framework_Templator();
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
	
	public function enableRequestModulsFromGet(){
		if(count($_GET)>0){
			if(isset($_GET[$this->conf['core']['module_param']])){
				$modul = 'Framework_Modules_'.$_GET[$this->conf['core']['module_param']].'_modul';
				if(class_exists($modul)){
					$o = new $modul($this);
					if($o->queryable === true){
						$this->modul = $o;
					}
					else
						$this->send404();
				}
				else
					$this->send404();
			}else
				$this->send404();
		}else{
			$modul = 'Framework_Modules_'.$this->conf['core']['module_name'].'_modul';
			if(class_exists($modul)){
				$o = new $modul($this);
				if($o->queryable === true){
					$this->modul = $o;
				}
				else
					$this->send404();
			}
			else
				$this->send404();
		}
	}
	
	public function enableRequestModulsFromPost($paramName){
		if(isset($_POST[$paramName])){
			$modul = 'Framework_Modules_'.$_POST[$paramName].'_modul';
			if(class_exists($modul)){
				$this->modul = new $modul($this);
			}
			else
				$this->send404();
		}elseif(count($_POST)>0)
			$this->send404();
	}
	
	public function url(){
		return $this->conf['core']['url'];
	}
	
	public function getCurrentUrl(){
		if(!isset($_SERVER['REQUEST_URI']))
			$serverrequri = $_SERVER['PHP_SELF'];
		else
			$serverrequri = $_SERVER['REQUEST_URI'];
		$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
		$protocol = strtolower($_SERVER["SERVER_PROTOCOL"]);
		$protocol = substr($protocol, 0, strpos($protocol, "/")).$s;
		$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
		return $protocol."://".$_SERVER['SERVER_NAME'].$port.$serverrequri;
	}
	
	public function getModulsPath(){
		return FRAMEWORK_PATH.DIRECTORY_SEPARATOR.'Framework'.DIRECTORY_SEPARATOR.$this->conf['core']['modules_path'];
	}
	
	public function getModulsUrl(){
		return $this->url().'/'.$this->conf['core']['library_path'].'/Framework/'.$this->conf['core']['modules_path'];
	}
	
	public function getTemplateUrl(){
		return $this->url().'/'.$this->conf['core']['library_path'].'/Framework/'.$this->conf['core']['templates_path'].'/'.$this->conf['core']['template_name'];
	}
	
	public function formatTime($t){
		return date('j.m.Y H:i:s',strtotime($t));
	}
	
	public function formatDate($t){
		return date('j.m.Y',strtotime($t));
	}
	
	
}

?>