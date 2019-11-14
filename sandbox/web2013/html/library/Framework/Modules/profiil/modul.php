<?php
/*
 * PHP Framework
 * Version 2012.12
 * Mihkel Oviir
 * 
 * Modul Class Framework_Modules_profiil_modul
 */

// include parent
require dirname(__FILE__).'/../oviirid/modul.php';

class Framework_Modules_profiil_modul extends Framework_Modules_oviirid_modul {
	
	var $framework;
	var $queryable = true;
	var $access = 'private';
	var $menuitem = 'oviirid';
	var $db_table = 'dev_2012_members';
	var $output = array();
	
	function __construct($framework){
		$this->framework = $framework;
		
		$this->output['title'] = 'Sugulane';
		$this->output['ajax-handler'] = $this->framework->conf['core']['ajax_handler'].'?'.$this->framework->conf['core']['module_param'].'=profiil';
		$this->output['modul-scripts'] = '<script type="text/javascript" src="/libs/jquery/jquery-selectBox/jquery.selectBox.js"></script>';
		$this->output['modul-scripts'] .= '<script type="text/javascript" src="'.$this->framework->getModulsUrl().'/profiil/modul.js"></script>';
		$this->output['modul-styles'] = '<link type="text/css" rel="stylesheet" href="/libs/jquery/jquery-selectBox/jquery.selectBox.css" />';
		$this->output['modul-styles'] .= '<link rel="stylesheet" href="'.$this->framework->getModulsUrl().'/profiil/modul.css">';
		
	}
	
	public function front(){
		if($this->access == 'private' && $_SESSION['Framework']['auth_status'] == 'public')
			$this->framework->redirect($this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=login&ref='.urlencode($this->framework->getCurrenturl()));
		else
			if(isset($_GET['user']))
				if(isset($_GET['edit'])){
					if($_GET['edit'] == 'settings')
						$this->loadUserProfileSettingsPage($_GET['user']);
					else
						$this->loadUserProfileEditPage($_GET['user']);
				}
					
				else
					$this->loadUserProfilePage($_GET['user']);
			elseif(isset($_GET['add']))
				$this->loadUserProfileEditPage();
			else
				$this->framework->send404();
	}
	
	private function getDefaultValues(){
		return array(
			h3_1 => _('Teadmiseks'),
			h3_2 => _('Valikud'),
			h3_3 => _('Sugulaste nimekiri'),
			h3_4 => _('Toimingud'),
			actions => '',
			metadata => '',
			members_list => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=oviirid',
			members_title => _('Sugulaste nimekiri'),
			contacts_list => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=oviirid&contacts',
			contacts_title => _('Sugulaste kontaktid'),
			information => _('Sugupuu andmed'),
			information_table => '',
			contacts => _('Kontaktid'),
			contacts_table => '',
			settings => _('Seaded'),
			settings_table => '',
			// add/edit profile
			h2 => _('Uue sugulase lisamine'),
			warning => _('NB! Enne lisamist veendu, et lisatav puudub ').'<a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=oviirid">'._('sugupuus').'</a>!<br>'._('Täida väljad sugulase andmetega, tärniga väljad on kohustuslikud. Kui ei tea mõne välja puhul, mida sinna kirjutada, jäta esialgu täitmata, küll hiljem saab parandada.'),
			cancel => _('Tühista'),
			cancel_href => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=oviirid',
			save => _('Salvesta'),
			ph_first_name => '* '._('Eesnimi'),
			ph_last_name => '* '._('Perenimi'),
			id => -1,
			first_name => '',
			last_name => '',
			ph_birth => _('Sünniaeg'),
			birth => '',
			ph_death => _('Surmaaeg'),
			death => '',
			death_note => _('Sünni ja surmaaeg kirjuta vabas vormis').'<br>'._('Kui isik on surnud aga surmaaeg pole teada, sisesta küsimärk (?)'),
			relations => '',
			relation_note => _('Vali sugulussuhe').' (<a href="http://et.wikipedia.org/wiki/Sugulussuhted" title="'._('Wikipeedia artikkel').'" target="_blank">'._('lisainfo').'!</a>)',
			ancestors => '',
			ancestor_note => _('Vali sugupuu haru'),
			ph_related_name => _('Isik, kes seob sind suguvõsaga'),
			related_name => '',
			related_name_note => _('Suguvõsa poolne vanem (veresugulane) või partner (hõimlane)').'<br>'._('Alusta nime kirjutamist, süsteem pakub vastavaid valikuid. Kui otsitavat nime ei leidu, jäta see väli täitmata'),
			related_with => '',
			ph_address => _('Postiaadress/elukoht'),
			address => '',
			ph_email => _('E-posti aadress'),
			email => '',
			ph_phone => _('Telefoni number'),
			phone => '',
			loading => $this->framework->getTemplateUrl().'/images/loading.gif',
			wait => _('Oota, laeb ...'),
			// edit settings
			username => _('Kasutajanime muutmine'),
			ph_uname => _('Kasutajanimi'),
			passwoord => _('Parooli muutmine'),
			ph_passwoord => _('Uus parool, vähemalt 6 märki'),
			ph_passwoord2 => _('Parooli kordus'),
			passwoord_note => _('Salasõna muutmiseks sisesta uus salasõna kaks korda, salasõna peab olema vähemalt 6 tähemärki'),
			invitation_note => _('Kokkutuleku kutse'),
			notification_note => _('Teated e-posti aadressile'),
			admin => ''
		);
	}
	
	private function loadUserProfilePage($uid){
		
		if($u = $this->framework->users->getUserDetails('id',$uid)){
			
			$user = $u[0];
			$tmp = $this->getDefaultValues();
			
			$rel_arr = array(
				0=>'',
				1=>'Veresugulane',
				2=>'Hõimlane'
			);
			
			$q = "SELECT t1.*,CONCAT(t2.first_name,' ',t2.last_name) AS related_name FROM $this->db_table AS t1 LEFT JOIN $this->db_table AS t2 ON (t1.related_with=t2.id) WHERE t1.deleted=0 AND t1.id=".$uid;
			$result = $this->framework->db->query($q);
			$row = mysql_fetch_assoc($result);
			if($row['status'] == 'hidden')
				$this->framework->send404();
			
			// find last edit author name
			if($u = $this->framework->users->getUserDetails('id',$row['changed_by'])){
				if (($timestamp = strtotime($u[0]['birth'])) !== false)
					$y = ' ('.date("Y", $timestamp).')';
				else
					$y = '';
				$row['metadata'] = '<b>'._('Viimati muutis').':</b><br>'.$row['changed'].'<br>'.$u[0]['first_name'].' '.$u[0]['last_name'].$y;
			}
			
			// actions
			if(($this->framework->users->userHaveRights('comment') && $_SESSION['Framework']['current_user']['status'] !== 'hidden') || ($this->framework->users->userHaveRights('edit')))
				$row['actions'] = '<h3>'._('Toimingud').'</h3><a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=profiil&user='.$uid.'&edit" class="btn btn-danger" style="width:60px">Muuda</a>';
			
			if($uid == $_SESSION['Framework']['current_user']['id'] || $this->framework->users->userHaveRights('admin'))
				$row['actions'] .= '<div style="height:6px"></div><a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=profiil&user='.$uid.'&edit=settings" class="btn btn-inverse" style="width:60px">'._('Seaded').'</a>';
			
			
			
			$tmp = array_merge($tmp,$row);
			
			$tmp['information_table'] = '<table>';
			$tmp['information_table'] .= '<tr><td style="width:200px">Eesnimi:</td><td>'.$tmp['first_name'].'</td></tr>';
			$tmp['information_table'] .= '<tr><td>Perenimi:</td><td>'.$tmp['last_name'].'</td></tr>';
			$tmp['information_table'] .= '<tr><td>Sündinud:</td><td>'.$tmp['birth'].'</td></tr>';
			$tmp['information_table'] .= !empty($tmp['death'])?'<tr><td>Surmaaeg:</td><td>'.$tmp['death'].'</td></tr>':'';
			$tmp['information_table'] .= '<tr><td>Sugulusside:</td><td>'.$rel_arr[$tmp['relation']].'</td></tr>';
			$tmp['information_table'] .= '<tr><td>Puu haru:</td><td>'.$tmp['ancestor'].'</td></tr>';
			$tmp['information_table'] .= '</table>';
			
			$tmp['contacts_table'] = '<table>';
			$tmp['contacts_table'] .= '<tr><td style="width:200px">Aadress:</td><td>'.$tmp['address'].'</td></tr>';
			$tmp['contacts_table'] .= '<tr><td>E-post:</td><td>'.$tmp['email'].'</td></tr>';
			$tmp['contacts_table'] .= '<tr><td>Telefon:</td><td>'.$tmp['phone'].'</td></tr>';
			$tmp['contacts_table'] .= '</table>';
			
			if($tmp['invitation']=='post')
				$inv = 'Posti teel';
			elseif($tmp['invitation']=='email')
				$inv = 'E-posti aadressile';
			else
				$inv = 'Ei saadeta kutset';
			
			$tmp['settings_table'] = '<table>';
			$tmp['settings_table'] .= '<tr><td style="width:200px">Kokkutuleku kutse:</td><td>'.$inv.'</td></tr>';
			$inv = $tmp['notification']==1?'Jah':'Ei';
			$tmp['settings_table'] .= '<tr><td>Teated e-posti aadressile:</td><td>'.$inv.'</td></tr>';
			$tmp['settings_table'] .= '</table>';
			
			$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/profile.html',$tmp);
		}else
			$this->framework->send404();
	}

	private function loadUserProfileEditPage($uid = null){
		
		$tmp = $this->getDefaultValues();
		
		$rel_arr = array(
			0=>'-',
			1=>'Veresugulane',
			2=>'Hõimlane'
		);
		
		// edit
		if($uid !== null){
			
			$q = "SELECT t1.*,CONCAT(t2.first_name,' ',t2.last_name) AS related_name FROM $this->db_table AS t1 LEFT JOIN $this->db_table AS t2 ON (t1.related_with=t2.id) WHERE t1.deleted=0 AND t1.id=".$uid;
			$result = $this->framework->db->query($q);
			$row = mysql_fetch_assoc($result);
			if(!$row)
				$this->framework->send404();
			if($row['status'] == 'hidden')
				$this->framework->send404();
			
			$tmp = array_merge($tmp,$row);
			$tmp['h2'] = $tmp['first_name'].' '.$tmp['last_name'];
			$tmp['ancestors'] = $this->getAncestors($tmp['ancestor']);
			$tmp['cancel_href'] = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=profiil&user='.$uid;
			
		}
		// add
		else{
			$tmp['ancestors'] = $this->getAncestors('');
		}
		
		foreach($rel_arr as $k=>$v){
			if(isset($tmp['relation']) && $tmp['relation'] == $k)
				$sel = ' selected="selected"';
			else
				$sel = '';
			$tmp['relations'] .= '<option value="'.$k.'"'.$sel.'>'.$v.'</option>';
		}
		
		if($uid !== null && ($uid == $_SESSION['Framework']['current_user']['id'] || $this->framework->users->userHaveRights('admin')))
			$tmp['actions'] .= '<a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=profiil&user='.$uid.'&edit=settings" class="btn btn-inverse" style="width:60px">'._('Seaded').'</a>';
		
		// delete button is visible only for admin and if edit
		if($uid !== null && $this->framework->users->userHaveRights('admin'))
			$tmp['actions'] .= '<div style="height:4px">&nbsp;</div><a href="delete" class="btn btn-danger delete" style="width:60px">'._('Kustuta').'</a>';
		
		$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/profile_add.html',$tmp);
		
	}

	private function loadUserProfileSettingsPage($uid){
			
		$inv_arr = array(
			'no'=>_('Ei saadeta kutset'),
			'post'=>_('Kutse posti teel'),
			'email'=>_('Kutse e-posti aadressile')
		);
		
		$notif_arr = array(
			0=>_('Ei'),
			1=>_('Jah')
		);
		
		$status_arr = array('enabled','disabled','hidden');
		
		$tmp = $this->getDefaultValues();
		$tmp['cancel_href'] = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=profiil&user='.$uid;
		
		$q = "SELECT * FROM $this->db_table WHERE deleted=0 AND id=".$uid;
		$result = $this->framework->db->query($q);
		$row = mysql_fetch_assoc($result);
		
		if(!$row)
			$this->framework->send404();
		if($row['status'] == 'hidden')
			$this->framework->send404();
		
		$tmp = array_merge($tmp,$row);
		$tmp['h2'] = $tmp['first_name'].' '.$tmp['last_name'];
		$tmp['warning'] = _('Siin saad muuta oma konto seadeid');
		
		foreach($inv_arr as $k=>$v){
			$sel = '';
			if($tmp['invitation'] == $k)
				$sel = ' selected="selected"';
			$tmp['invitations'] .= '<option value="'.$k.'"'.$sel.'>'.$v.'</option>';
		}
		
		foreach($notif_arr as $k=>$v){
			$sel = '';
			if($tmp['notification'] == $k)
				$sel = ' selected="selected"';
			$tmp['notifications'] .= '<option value="'.$k.'"'.$sel.'>'.$v.'</option>';
		}

		if($this->framework->users->userHaveRights('admin')){
			$tmp['admin'] = '<h4>'._('Kasutaja õigused').'</h4>';
			$tmp['admin'] .= '<div><select name="level" tabindex="0" style="width:200px">';
			foreach($this->framework->conf['permissions'] as $k=>$v){
				$sel = '';
				if($tmp['level'] == $k)
					$sel = ' selected="selected"';
				$tmp['admin'] .= '<option value="'.$k.'"'.$sel.'>'.$k.'</option>';
			}
			$tmp['admin'] .= '</select></div><div style="height:14px"></div>';
			$tmp['admin'] .= '<h4>'._('Kasutaja staatus').'</h4>';
			$tmp['admin'] .= '<div><select name="status" tabindex="0" style="width:200px">';
			foreach($status_arr as $k=>$v){
				$sel = '';
				if($tmp['status'] == $v)
					$sel = ' selected="selected"';
				$tmp['admin'] .= '<option value="'.$v.'"'.$sel.'>'.$v.'</option>';
			}
			$tmp['admin'] .= '</select></div>';
		}
		
		$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/profile_settings.html',$tmp);
	}

	public function getMembersList(){
		
		$tmp['content_type']='json';
		$tmp['content']['status']='0';
		$tmp['content']['data']=array();
		
		if($this->access == 'private' && $_SESSION['Framework']['auth_status'] == 'public')
			$this->framework->send404();
		
		$q = "SELECT id,CONCAT(first_name,' ',last_name) AS name,IF(birth IS NULL AND death IS NULL,'',CONCAT(IF(birth IS NULL,'(...',CONCAT('(',birth)),IF(death IS NULL,')',CONCAT(' - ',death,')')))) AS datum FROM $this->db_table WHERE deleted=0 AND status!='hidden' ORDER BY first_name,last_name";
		$result = $this->framework->db->query($q);
		while($row = mysql_fetch_assoc($result)){
			$tmp['content']['data'][] = $row;
		}
		$tmp['content']['status']='1';
		
		return $tmp;
	}
	
	public function deleteMember(){
		
		if(!$this->framework->users->userHaveRights('admin'))
			$this->framework->send404();
		
		$tmp = array(
			content_type => 'json',
			content => array(
				status => 0
			)
		);
		
		if(isset($_POST['id']) && $_POST['id'] != ''){
			
			$q = "UPDATE $this->db_table SET deleted=1,changed=NOW(),changed_by=".$_SESSION['Framework']['current_user']['id']." WHERE id=".$_POST['id'];
			$r = $this->framework->db->query($q);
			
			if($r)
				$tmp['content']['status'] = 1;
			else
				$tmp['content']['status'] = 0;
		}
		
		return $tmp;
	}
	
	public function saveMemberData(){
		
		if(($_SESSION['Framework']['current_user']['status'] == 'hidden' && $this->framework->users->userHaveRights('admin')) || ($_SESSION['Framework']['current_user']['status'] == 'enabled' && $this->framework->users->userHaveRights('comment'))){
			
			$tmp = array(
				content_type => 'json',
				content => array(
					status => 0
				)
			);
			
			if(isset($_POST['id']) && isset($_POST['first_name']) && trim($_POST['first_name']) != '' && isset($_POST['last_name']) && trim($_POST['last_name']) != ''){
				
				switch ($_POST['id']) {
					case -1:
						$tmp['content']['id'] = $this->addNewMember($_POST);
					break;
					default:
						$tmp['content']['id'] = $this->changeMember($_POST);
					break;
				}
			}
			
			if(isset($_POST['id']) && isset($_POST['uname']) && trim($_POST['uname']) != ''){
				$tmp['content']['id'] = $this->changeMemberSettings($_POST);
			}
			
			if($tmp['content']['id'])
				$tmp['content']['status'] = 1;
			
			return $tmp;
			
		}else
			$this->framework->send404();
	}

	private function addNewMember($d){
		
		if(empty($d['related_with']))
			$d['related_with'] = 'NULL';
		if($d['death']!='')
			$d['status'] = 'disabled';
		else
			$d['status'] = 'enabled';
		
		$q = "INSERT INTO $this->db_table (status,first_name,last_name,birth,death,relation,related_with,email,address,phone,ancestor,registered,changed,changed_by) VALUES (
			'".$d['status']."',
			IF(LENGTH('".mysql_real_escape_string($d['first_name'])."')=0,NULL,'".mysql_real_escape_string($d['first_name'])."'),
			IF(LENGTH('".mysql_real_escape_string($d['last_name'])."')=0,NULL,'".mysql_real_escape_string($d['last_name'])."'),
			IF(LENGTH('".mysql_real_escape_string($d['birth'])."')=0,NULL,'".mysql_real_escape_string($d['birth'])."'),
			IF(LENGTH('".mysql_real_escape_string($d['death'])."')=0,NULL,'".mysql_real_escape_string($d['death'])."'),
			IF(LENGTH('".mysql_real_escape_string($d['relation'])."')=0,NULL,".mysql_real_escape_string($d['relation'])."),
			".mysql_real_escape_string($d['related_with']).",
			IF(LENGTH('".mysql_real_escape_string($d['email'])."')=0,NULL,'".mysql_real_escape_string($d['email'])."'),
			IF(LENGTH('".mysql_real_escape_string($d['address'])."')=0,NULL,'".mysql_real_escape_string($d['address'])."'),
			IF(LENGTH('".mysql_real_escape_string($d['phone'])."')=0,NULL,'".mysql_real_escape_string($d['phone'])."'),
			IF(LENGTH('".mysql_real_escape_string($d['ancestor'])."')=0,NULL,'".mysql_real_escape_string($d['ancestor'])."'),
			NOW(),NOW(),
			".$_SESSION['Framework']['current_user']['id'].");";
		
		if($this->framework->db->query($q))
			return mysql_insert_id();
		else
			return false;
	}
	
	private function changeMember($d){
		
		if(!isset($d['id']) || empty($d['id']))
			return false;
		
		if(empty($d['related_with']))
			$d['related_with'] = 'NULL';
		
		if($d['death']!='')
			$d['status'] = 'disabled';
		else
			$d['status'] = 'enabled';
		
		$q = "UPDATE $this->db_table SET 
			status='".$d['status']."',
			first_name='".mysql_real_escape_string($d['first_name'])."',
			last_name='".mysql_real_escape_string($d['last_name'])."',
			birth='".mysql_real_escape_string($d['birth'])."',
			death='".mysql_real_escape_string($d['death'])."',
			relation=".mysql_real_escape_string($d['relation']).",
			related_with=".mysql_real_escape_string($d['related_with']).",
			email='".mysql_real_escape_string($d['email'])."',
			address='".mysql_real_escape_string($d['address'])."',
			phone='".mysql_real_escape_string($d['phone'])."',
			ancestor='".mysql_real_escape_string($d['ancestor'])."',
			changed=NOW(),
			changed_by=".$_SESSION['Framework']['current_user']['id']."
			WHERE id=".$d['id'];
		
		if($this->framework->db->query($q))
			return $d['id'];
		else
			return false;
	}
	
	private function changeMemberSettings($d){
		$psw = "";
		if(!isset($d['id']) || empty($d['id']))
			return false;
		if(isset($d['passwoord']) && isset($d['passwoord2'])){
			if($d['passwoord'] == $d['passwoord2'] && strlen(trim($d['passwoord']))>5)
				$psw = "password=MD5('".trim($d['passwoord'])."'),";
		}
		
		$q = "UPDATE $this->db_table SET 
			status=IF(LENGTH('".mysql_real_escape_string($d['status'])."')=0,NULL,'".mysql_real_escape_string($d['status'])."'),
			level=IF(LENGTH('".mysql_real_escape_string($d['level'])."')=0,NULL,'".mysql_real_escape_string($d['level'])."'),
			uname='".mysql_real_escape_string($d['uname'])."',
			".$psw."
			invitation='".mysql_real_escape_string($d['invitation'])."',
			notification='".mysql_real_escape_string($d['notification'])."',
			changed=NOW(),
			changed_by=".$_SESSION['Framework']['current_user']['id']."
			WHERE id=".$d['id'];
		
		if($this->framework->db->query($q))
			return $d['id'];
		else
			return false;
		
	}
	
	public function getOutput(){
		return $this->output;
	}
	
}
?>