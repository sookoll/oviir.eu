<?php
/*
 * PHP Framework
 * Version 2012.12
 * Mihkel Oviir
 * 
 * Modul Class Framework_Modules_login_modul
 */
class Framework_Modules_login_modul {
	
	var $framework;
	var $queryable = true;
	var $menuitem = 'login';
	var $db_table = 'dev_2012_members';
	var $output = array();
	
	function __construct($framework){
		$this->framework = $framework;
	}
	
	public function front(){
		// modul pages controller
		if(isset($_GET['logout'])){
			$this->doLogout($_GET['logout']);
		}elseif(isset($_GET['go'])){
			$this->doLogin();
		}else
			$this->openLoginPage();
	}
	
	// load modal
	public function loadUserContainer(){
		
		$tmp = array(
			user => ''
		);
		
		if($_SESSION['Framework']['auth_status'] == 'private'){
			
			if($_SESSION['Framework']['current_user']['status'] != 'hidden')
				$tmp['user'] .=  '<a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=profiil&user='.$_SESSION['Framework']['current_user']['id'].'" title="'._('Minu profiil').'">'.$_SESSION['Framework']['current_user']['full_name'].'</a> | ';
			else
				$tmp['user'] .=  $_SESSION['Framework']['current_user']['full_name'].' | ';
				
			$tmp['user'] .= '<a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=login&logout">'._('Välju').'</a>';
			$script = '<script type="text/javascript" src="{_js-path_}/user.js"></script>';
		}else{
			
			//$tmp['user'] .= 'Kontaktivorm | ';
			
			// if this modul page
			if(isset($_GET[$this->framework->conf['core']['module_param']]) && $_GET[$this->framework->conf['core']['module_param']] == $this->menuitem)
				$tmp['user'] .= _('Sisene');
			else
				$tmp['user'] .= '<a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=login&ref='.urlencode($this->framework->getCurrenturl()).'">'._('Sisene').'</a>';
			$script = '<script type="text/javascript" src="{_js-path_}/public.js"></script>';
		}
			
		$u = $this->framework->templator->parse(dirname(__FILE__).'/user.html',$tmp);
		
		
		return array('user'=>$u,'user-scripts'=> $script);
	}
	
	private function openLoginPage(){
		$tmp = array();
		
		if($_SESSION['Framework']['auth_status'] == 'public'){
			
			$tmp['text'] = '<h3>'._('Veebikeskkonda sisenemine').'</h3>'._('Sisenemiseks kasuta oma kasutajanime ja parooli. Probleemide korral pöördu lehe administraatori poole.');
			$tmp['url'] = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=login&go';
			
			if(isset($_GET['ref']))
				$tmp['ref'] = $_GET['ref'];
			else
				$tmp['ref'] = urlencode($this->framework->url());
			
			$tmp['user_placeholder'] = _('Kasutajanimi');
			$tmp['pass_placeholder'] = _('Salasõna');
			$tmp['submit'] = _('Sisene');
			$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/login.html',$tmp);
			$this->output['modul-styles'] = '<link rel="stylesheet" href="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/login.css">';
		}
			
	}
	
	private function doLogin(){
		
		// user login
		if(isset($_POST['u']) && isset($_POST['p']) && !empty($_POST['u']) && !empty($_POST['p'])){
			if($users = $this->getUserDetails('uname',urldecode($_POST['u']))){
				if($users[0]['status'] != 'disabled' && strlen($users[0]['password'])>3 && $users[0]['password'] === md5(urldecode($_POST['p']))){
					// set the session
					$_SESSION['Framework']['auth_status'] = 'private';
					$_SESSION['Framework']['current_user'] = array(
						id => $users[0]['id'],
						auth_level => $users[0]['level'],
						full_name => $users[0]['first_name'].' '.$users[0]['last_name'],
						status => $users[0]['status']
					);
					
					// last login to db
					$q = "UPDATE $this->db_table SET last_login=NOW() WHERE uname='".$_POST['u']."'";
					$result = $this->framework->db->query($q);
				}
			}
		}
		
		// if in
		if($_SESSION['Framework']['auth_status'] == 'private')
			$this->framework->redirect(urldecode($_POST['ref']));
		else
			$this->framework->redirect($this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=login');
	}
	
	public function getUserDetails($prop,$id){
		
		if(!isset($prop) || empty($prop))
			$prop = 'uname';
		if(!isset($id) || empty($id))
			return false;
		
		$tmp = array();

		if($prop == 'id')
			$where = "id=$id";
		else if($prop == 'uname')
			$where = "uname='".$id."'";
		else
			return false;
		
		$q = $id=='*'?"SELECT * FROM $this->db_table WHERE deleted=0 ORDER BY first_name,last_name":"SELECT * FROM $this->db_table WHERE deleted=0 AND ".$where;
		$result = $this->framework->db->query($q);
		
		// TODO: hard coded mysql function!
		while($row = mysql_fetch_assoc($result)){
			$tmp[] = $row;
		}
		
		if(count($tmp)>0)
			return $tmp;
		else
			return false;
	}
	
	private function doLogout(){
		unset($_SESSION['Framework']['auth_status']);
		unset($_SESSION['Framework']['current_user']);
		$this->framework->redirect($this->framework->url());
	}
	
	public function userHaveRights($level){
		return @in_array($level,$this->framework->conf['permissions'][$_SESSION['Framework']['current_user']['auth_level']])?true:false;
	}
	
	public function getOutput(){
		return $this->output;
	}
	
}
?>