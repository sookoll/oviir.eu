<?php
/*
 * PHP Framework
 * Version 2012.12
 * Mihkel Oviir
 * 
 * Class Framework_Translator
 */
class Framework_Translator {
	
	function __construct($locale){
		setlocale(LC_ALL, $locale);
		bindtextdomain("translations", FRAMEWORK_PATH.DIRECTORY_SEPARATOR.'Framework'.DIRECTORY_SEPARATOR.'translations');
		textdomain("translations");
	}
	
}
?>