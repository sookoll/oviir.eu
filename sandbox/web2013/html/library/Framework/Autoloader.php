<?php
/*
 * PHP Framework
 * Version 2012.12
 * Mihkel Oviir
 * 
 * Class Autoloader
 */

class Framework_Autoloader {
	/**
	 * Register the Autoloader with SPL
	 *
	 */
	public static function Register() {
		if (function_exists('__autoload')) {
			//	Register any existing autoloader function with SPL, so we don't get any clashes
			spl_autoload_register('__autoload');
		}
		//	Register ourselves with SPL
		return spl_autoload_register(array('Framework_Autoloader', 'Load'));
	}	//	function Register()


	/**
	 * Autoload a class identified by name
	 *
	 * @param	string	$pClassName		Name of the object to load
	 */
	public static function Load($pClassName){
		if ((class_exists($pClassName)) || (strpos($pClassName, 'Framework') !== 0)) {
			//	Either already loaded, or not a PHPExcel class request
			return false;
		}

		$pObjectFilePath = FRAMEWORK_PATH.DIRECTORY_SEPARATOR.str_replace('_',DIRECTORY_SEPARATOR,$pClassName).'.php';

		if ((file_exists($pObjectFilePath) === false) || (is_readable($pObjectFilePath) === false)) {
			//	Can't load
			return false;
		}

		require($pObjectFilePath);
	}	//	function Load()

}

Framework_Autoloader::Register();

?>