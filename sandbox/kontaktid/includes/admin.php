<?php 
/*
 * Oviir's family contacts
 * admin class to process data
 * 
 * Creator: Mihkel Oviir
 * 05.2011
 * 
 */

class admin {
	
	var $output=array();
	
	function __construct(){}
	
	public function front(){
		global $sess,$func;
		$tmp = array();
		
		$tmp['user_logged'] = 'true';
		
		if(@file_exists(PATH_INC.ADMIN_MODUL'.php')){
			require_once PATH_INC.ADMIN_MODUL.'.php';
			$m=new $row['name_key']();
			$m->front();
			$data=$m->getResult();
			$tmp['moduls'].=$func->replace_tags(PATH_TMPL.TEMPLATE.'/html/'.ADMIN_MODUL.'.html',$data);
		}

		$this->output = $tmp;
	}

	// return output data
	function getResult() {
		return $this->output;
	}
}

?>