<?php 
/*
 * Oviir's family contacts
 * Login class
 * 
 * Mihkel Oviir
 * 
 */

class login {
	
	var $output=array();
	
	// method to control login and set session variable
	function setLogin(){
		global $Password,$sess;
		$tmp['content']['status']='0';

		// admin login
		if(isset($Password)){
			if(urldecode($Password) === U_PWD) {
				// set the session
				$sess->oviir_admin_is_logged_in = true;
				$tmp['content']['status']='1';
			}
		}
		$tmp['content_type'] = 'json';
		$this->output = $tmp;
	}
	
	function setLogout(){
		global $sess;
		$tmp['content']['status']='0';
		
		unset($sess->oviir_admin_is_logged_in);
		$tmp['content']['status']='1';
		$tmp['content_type'] = 'json';
		$this->output = $tmp;
	}
	
	// method to return arrays
	function getResult() {
		return $this->output;
	}
}
?>