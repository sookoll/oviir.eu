<?php
/*
 * PHP Framework
 * Version 2012.12
 * Mihkel Oviir
 * 
 * Modul Class Framework_Modules_kokkutulekud_modul
 */
class Framework_Modules_kokkutulekud_modul {
	
	var $framework;
	var $queryable = true;
	var $access = 'private';
	var $menuitem = 'kokkutulekud';
	var $db_table = 'dev_2012_events';
	var $db_table_pictures = 'miuview_items';
	var $output = array();
	
	function __construct($framework){
		$this->framework = $framework;
		$this->output['title'] = _('Oviiride kokkutulekud');
		$this->output['modul-styles'] = '<link rel="stylesheet" href="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/kokkutulekud.css">';
	}
	
	public function front(){
		if($this->access == 'private' && $_SESSION['Framework']['auth_status'] == 'public')
			$this->framework->redirect($this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=login&ref='.urlencode($this->framework->getCurrenturl()));
		else {
			
			if(isset($_GET['aasta'])){
				if(isset($_GET['edit']))
					if($this->framework->users->userHaveRights('edit'))
						$this->loadEditPage($_GET['aasta']);
					else
						$this->framework->send404();
				else
					$this->loadEvent($_GET['aasta']);
			} else
				if(isset($_GET['edit']))
					if($this->framework->users->userHaveRights('edit'))
						$this->loadEditPage();
					else
						$this->framework->send404();
				else
					$this->loadEventslist();
			
		}
	}
	
	private function loadEventslist(){
			
		$tmp['h3_1'] = _('Teadmiseks');
		$tmp['h3_3'] = _('Kokkutulekute nimekiri');
		$tmp['1972'] = _('Toimunud kokkutulek');
		$tmp['2015'] = _('Kokku lepitud, kindel värk');
		$tmp['2022'] = _('On laekunud sooviavaldus');
		$tmp['actions'] = '';
		$tmp['table'] = '';
		
		if($this->framework->users->userHaveRights('edit'))
			$tmp['actions'] = '<h3>'._('Toimingud').'</h3><a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'='.'kokkutulekud&edit" class="btn btn-info" style="width:60px">Lisa uus</a>';
		
		$q = "SELECT * FROM $this->db_table WHERE deleted=0 ORDER BY year ASC";
		$result = $this->framework->db->query($q);
		while($row = mysql_fetch_assoc($result)){
				
			foreach($row as $k => $v){
				$row[$k] = stripslashes($v);
			}
			
			switch ($row['status']) {
				case 'done':
					$btn = '';
				break;
				case 'deal':
					$btn = ' btn-success';
				break;
				case 'wish':
					$btn = ' btn-warning';
				break;
			}
			$tmp['table'] .= '<tr><td class="align-center"><b>'.$row['event_id'].'</b></dt><td>'.$row['event_organizer'].'</dt><td>'.$row['event_location'].'</dt>';
			$tmp['table'] .= '<td class="align-right"><a class="btn'.$btn.'" style="width:60px" href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'='.'kokkutulekud&aasta='.$row['year'].'"><b>'.$row['year'].'</b></a></dt></tr>';
		}
		$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/kokkutulekud.html',$tmp);
		
	}

	private function loadEvent($year){
		
		$q = "SELECT * FROM $this->db_table WHERE deleted=0 AND year=$year";
		$result = $this->framework->db->query($q);
		if(mysql_num_rows($result) == 0)
			$this->framework->send404();
		if($row = mysql_fetch_assoc($result)){
			
			foreach($row as $k => $v){
				$row[$k] = stripslashes($v);
			}
			
			if(empty($row['title']))
				$row['title'] = $row['year'].'.a';
			$row['h3_1'] = _('Teadmiseks');
			$row['h3_3'] = _('Kokkutulekute nimekiri');
			$row['1972'] = _('Toimunud kokkutulek');
			$row['2015'] = _('Kokku lepitud, kindel värk');
			$row['2022'] = _('On laekunud sooviavaldus');
			$row['span1'] = $row['status'] == 'done'?'':'grey';
			$row['span2'] = $row['status'] == 'deal'?'':'grey';
			$row['span3'] = $row['status'] == 'wish'?'':'grey';
			$row['metadata'] = '';
			$row['picture'] = empty($row['picture'])?'':'<img src="http://oviir.eu/miuview-api/?request=getimage&album=kokkutulekud&item='.$row['picture'].'&size=720&mode=longest">';
			
			// find author name
			if($u = $this->framework->users->getUserDetails('id',$row['author'])){
				if (($timestamp = strtotime($u[0]['birth'])) !== false)
					$y = ' ('.date("Y", $timestamp).')';
				else
					$y = '';
				$row['metadata'] = '<b>'._('Viimati muutis').':</b><br>'.$row['modified'].'<br>'.$u[0]['first_name'].' '.$u[0]['last_name'].$y;
			}
				
			
			if($this->framework->users->userHaveRights('edit'))
				$row['actions'] = '<h3>'._('Toimingud').'</h3><a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=kokkutulekud&aasta='.$year.'&edit" class="btn btn-danger" style="width:60px">Muuda</a>';
			else
				$row['actions'] = '';
			
			$row['list_url'] = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'='.'kokkutulekud';
			$row['back_to_list'] = _('Tagasi nimekirja');
			$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/kokkutulek.html',$row);
		}
	}

	private function loadEditPage($year=null){
		
		$this->output['ajax-handler'] = $this->framework->conf['core']['ajax_handler'].'?'.$this->framework->conf['core']['module_param'].'=kokkutulekud';
		$this->output['modul-scripts'] = '<script type="text/javascript" src="/libs/nicEdit/nicEdit.js"></script>';
		$this->output['modul-scripts'] .= '<script type="text/javascript" src="/libs/jquery/jquery-selectBox/jquery.selectBox.js"></script>';
		$this->output['modul-scripts'] .= '<script type="text/javascript" src="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/modul.js"></script>';
		$this->output['modul-styles'] = '<link type="text/css" rel="stylesheet" href="/libs/jquery/jquery-selectBox/jquery.selectBox.css" />'.$this->output['modul-styles'];
		$tmp = array(
			h3_1 => _('Teadmiseks'),
			h3_2 => _('Toimingud'),
			h3_3 => _('Lisa uus kokkutulek'),
			texts => _('Piltide nimekiri on identne galeriis "Kokkutulekud" albumi piltidega. Enne kui siin pilti valida, tuleks laadida pilt sinna albumisse.'),
			save => _('Salvesta'),
			actions => '',
			cancel => _('Tühista'),
			cancel_href => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=kokkutulekud',
			pictures => '',
			ph_event_id => _('Jrk.nr'),
			event_id => '',
			ph_year => _('Aasta'),
			year => '',
			ph_title => 'Pealkiri',
			title => '',
			ph_event_organizer => _('Korraldaja'),
			event_organizer => '',
			ph_event_time => _('Kokkutuleku aeg'),
			event_time => '',
			ph_event_location => _('Kokkutuleku koht'),
			event_location => '',
			done_selected => 'selected="selected"',
			deal_selected => '',
			wish_selected => '',
			1972 => _('Toimunud kokkutulek'),
			2015 => _('Kokku lepitud, kindel värk'),
			2022 => _('On laekunud sooviavaldus'),
			ph_content => _('Kirjuta kokkutuleku kohta'),
			content => '',
			type => 'new',
			id => '',
			loading => $this->framework->getTemplateUrl().'/images/loading.gif',
			wait => _('Oota, salvestab ...'),
			readonly => ''
		);
		
		// edit
		if($year !== null){
				
			$tmp['actions'] = '<div style="height:34px">&nbsp;</div><a href="delete" class="btn btn-danger delete" style="width:60px">'._('Kustuta').'</a>';
			
			$q = "SELECT * FROM $this->db_table WHERE deleted=0 AND year=$year";
			$result = $this->framework->db->query($q);
			if(mysql_num_rows($result) == 0)
				$this->framework->send404();
			if($row = mysql_fetch_assoc($result)){
					
				foreach($row as $k => $v){
					$row[$k] = stripslashes($v);
				}
				
				$tmp['h3_3'] = _('Muuda kokkutuleku andmeid');
				$tmp['cancel_href'] .= '&aasta='.$year;
				$tmp['event_id'] = $row['event_id'];
				$tmp['year'] = $row['year'];
				$tmp['title'] = $row['title'];
				$tmp['event_organizer'] = $row['event_organizer'];
				$tmp['event_time'] = $row['event_time'];
				$tmp['event_location'] = $row['event_location'];
				$tmp['done_selected'] = $row['status']=='done'?'selected="selected"':'';
				$tmp['deal_selected'] = $row['status']=='deal'?'selected="selected"':'';
				$tmp['wish_selected'] = $row['status']=='wish'?'selected="selected"':'';
				$tmp['done_selected'] = $row['status']=='done'?'selected="selected"':'';
				$tmp['content'] = $row['content'];
				$tmp['type'] = 'change';
				$tmp['id'] = $row['year'];
				$tmp['readonly'] = 'readonly="readonly"';
			}
			
		}

		// picture list
		$q = "SELECT * FROM $this->db_table_pictures WHERE album='kokkutulekud' ORDER BY sort";
		$result = $this->framework->db->query($q);
		while($picture = mysql_fetch_assoc($result)){
			$selected = ($year !== null && isset($row) && $row['picture'] == $picture['item'])?'selected="selected"':'';
			$tmp['pictures'] .= '<option value="'.$picture['item'].'" '.$selected.'>'.$picture['item'].'</option>';
		}
		
		$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/edit.html',$tmp);
	}

	public function saveEvent(){
		
		if(!$this->framework->users->userHaveRights('edit'))
			$this->framework->send404();
		
		$tmp = array(
			content_type => 'json',
			content => array(
				status => 0
			)
		);
		
		if(isset($_POST['type']) && ($_POST['type'] == 'new' || $_POST['type'] == 'change') && $_POST['year'] != '' && $_POST['event_id'] != ''){
			
			switch ($_POST['type']) {
				case 'new':
					$tmp['content']['status'] = $this->addNewEvent($_POST);
				break;
				
				case 'change':
					$tmp['content']['status'] = $this->changeEvent($_POST);
				break;
			}
		}
		
		return $tmp;
	}

	private function addNewEvent($d){
		
		$q = "INSERT INTO $this->db_table (event_id,year,event_time,event_location,event_organizer,title,picture,content,status,author,added,modified) VALUES (
			IF(LENGTH(".mysql_real_escape_string($d['event_id']).")=0,NULL,".mysql_real_escape_string($d['event_id'])."),
			IF(LENGTH(".mysql_real_escape_string($d['year']).")=0,NULL,".mysql_real_escape_string($d['year'])."),
			IF(LENGTH('".mysql_real_escape_string($d['event_time'])."')=0,NULL,'".mysql_real_escape_string($d['event_time'])."'),
			IF(LENGTH('".mysql_real_escape_string($d['event_location'])."')=0,NULL,'".mysql_real_escape_string($d['event_location'])."'),
			IF(LENGTH('".mysql_real_escape_string($d['event_organizer'])."')=0,NULL,'".mysql_real_escape_string($d['event_organizer'])."'),
			IF(LENGTH('".mysql_real_escape_string($d['title'])."')=0,NULL,'".mysql_real_escape_string($d['title'])."'),
			IF(LENGTH('".mysql_real_escape_string($d['picture'])."')=0,NULL,'".mysql_real_escape_string($d['picture'])."'),
			IF(LENGTH('".mysql_real_escape_string($d['content'])."')=0,NULL,'".mysql_real_escape_string($d['content'])."'),
			IF(LENGTH('".mysql_real_escape_string($d['status'])."')=0,NULL,'".mysql_real_escape_string($d['status'])."'),
			".$_SESSION['Framework']['current_user'][id].",
			NOW(),NOW());";
		
		if($this->framework->db->query($q))
			return 1;
		else
			return 0;
	}
	
	private function changeEvent($d){
		
		if(!isset($d['id']) || empty($d['id']))
			return 0;
		
		$q = "UPDATE $this->db_table SET 
			event_id=".mysql_real_escape_string($d['event_id']).",
			event_time='".mysql_real_escape_string($d['event_time'])."',
			event_location='".mysql_real_escape_string($d['event_location'])."',
			event_organizer='".mysql_real_escape_string($d['event_organizer'])."',
			title='".mysql_real_escape_string($d['title'])."',
			picture='".mysql_real_escape_string($d['picture'])."',
			content='".mysql_real_escape_string($d['content'])."',
			status='".mysql_real_escape_string($d['status'])."',
			author='".$_SESSION['Framework']['current_user'][id]."',
			modified=NOW() WHERE year=".$d['id'];
		
		if($this->framework->db->query($q))
			return 1;
		else
			return 0;
		
	}
	
	public function deleteEvent(){
		
		if(!$this->framework->users->userHaveRights('edit'))
			$this->framework->send404();
		
		$tmp = array(
			content_type => 'json',
			content => array(
				status => 0
			)
		);
		
		if(isset($_POST['id']) && $_POST['id'] != ''){
			
			$q = "DELETE FROM $this->db_table WHERE year=".$_POST['id'];
			$r = $this->framework->db->query($q);
			
			if($r)
				$tmp['content']['status'] = 1;
			else
				$tmp['content']['status'] = 0;
		}
		
		return $tmp;
	}
	
	public function getOutput(){
		return $this->output;
	}
	
}
?>