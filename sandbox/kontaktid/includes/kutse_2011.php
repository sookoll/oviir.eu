<?php 
/*
 * Oviir's family invitation
 * main class to process data
 * 
 * Creator: Mihkel Oviir
 * 05.2011
 * 
 */

class kutse_2011 {
	
	var $output=array();
	
	function __construct(){}
	
	public function front(){
		global $sess,$func;
		$tmp = array();

		$this->output = $tmp;
	}

	// return output data
	function getResult() {
		return $this->output;
	}
}

?>