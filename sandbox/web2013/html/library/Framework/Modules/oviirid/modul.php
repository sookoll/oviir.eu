<?php
/*
 * PHP Framework
 * Version 2012.12
 * Mihkel Oviir
 * 
 * Modul Class Framework_Modules_oviirid_modul
 */
class Framework_Modules_oviirid_modul {
	
	var $framework;
	var $queryable = true;
	var $access = 'private';
	var $menuitem = 'oviirid';
	var $db_table = 'dev_2012_members';
	var $output = array();
	
	function __construct($framework){
		$this->framework = $framework;
		
		$this->output['title'] = _('Suguvõsa');
		$this->output['ajax-handler'] = $this->framework->conf['core']['ajax_handler'].'?'.$this->framework->conf['core']['module_param'].'=oviirid';
		$this->output['modul-scripts'] = '<script type="text/javascript" src="/libs/jquery/jquery-selectBox/jquery.selectBox.js"></script>';
		$this->output['modul-scripts'] .= '<script type="text/javascript" src="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/modul.js"></script>';
		$this->output['modul-styles'] = '<link type="text/css" rel="stylesheet" href="/libs/jquery/jquery-selectBox/jquery.selectBox.css" />';
		$this->output['modul-styles'] .= '<link rel="stylesheet" href="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/oviirid.css">';
		$this->output['modal-content'] = $this->framework->templator->parse(dirname(__FILE__).'/modal.html',array(title=>_('Sugulaste e-posti aadressid'),back_to_list=>_('Sulge (ESC)')));
	}
	
	public function front(){
		if($this->access == 'private' && $_SESSION['Framework']['auth_status'] == 'public')
			$this->framework->redirect($this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=login&ref='.urlencode($this->framework->getCurrenturl()));
		else {
			$this->loadMembersList();
		}
			
	}
	
	private function getDefaultValues(){
		return array(
			h3_1 => _('Teadmiseks'),
			h3_2 => _('Valikud'),
			h3_3 => _('Sugulaste nimekiri'),
			h3_4 => _('Toimingud'),
			h3_5 => _('Otsing'),
			actions => '',
			table => '',
			pages => '',
			submenu => '',
			members_active => 'active',
			members_list => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=oviirid',
			members_title => _('Sugulaste nimekiri'),
			contacts_active => '',
			contacts_list => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=oviirid&contacts',
			contacts_title => _('Sugulaste kontaktid'),
			ph_first_name => _('Eesnimi'),
			ph_last_name => _('Perenimi'),
			first_name => '',
			last_name => '',
			'reset' => _('Eemalda filter'),
			submit => _('Otsi'),
			'sort' => '',
			reset_href => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=oviirid',
			choose_ancestor => _('Vali haru'),
			'print' => _('Prindi'),
			print_href => $this->framework->url().'/'.$this->framework->conf['core']['ajax_handler'].'?'.$this->framework->conf['core']['module_param'].'=oviirid&m=printList',
			copy_emails => _('Kõik e-posti aadressid')
		);
	}
	
	private function getSortList($table,$page,$uri){
		if($table != '')
			$table .= '&';
		return array(
			first_name => array(
				classes => '',
				url => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=oviirid&'.$table.'page='.$page.'&sort=first_name'.$uri
			),
			last_name => array(
				classes => '',
				url => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=oviirid&'.$table.'page='.$page.'&sort=last_name'.$uri
			),
			ancestor => array(
				classes => '',
				url => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=oviirid&'.$table.'page='.$page.'&sort=ancestor'.$uri
			),
			generation => array(
				classes => '',
				url => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=oviirid&'.$table.'page='.$page.'&sort=generation'.$uri
			),
			relation => array(
				classes => '',
				url => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=oviirid&'.$table.'page='.$page.'&sort=relation'.$uri
			),
			address => array(
				classes => '',
				url => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=oviirid&'.$table.'page='.$page.'&sort=address'.$uri
			)
		);
	}

	private function getFilter(){
		$tmp = array();
		$tmp['where'] = "";
		$tmp['uri'] = '';
		if(!empty($_GET['first_name'])){
			$tmp['where'] .= " AND LOWER(first_name) LIKE LOWER('%".filter_input(INPUT_GET, 'first_name', FILTER_SANITIZE_STRING)."%')";
			$tmp['first_name'] = $_GET['first_name'];
			$tmp['uri'] .= '&first_name='.$_GET['first_name'];
		}
		
		if(!empty($_GET['last_name'])){
			$tmp['where'] .= " AND LOWER(last_name) LIKE LOWER('%".filter_input(INPUT_GET, 'last_name', FILTER_SANITIZE_STRING)."%')";
			$tmp['last_name'] = $_GET['last_name'];
			$tmp['uri'] .= '&last_name='.$_GET['last_name'];
		}
		
		if(!empty($_GET['ancestor']) && $_GET['ancestor'] != '*'){
			$tmp['where'] .= " AND ancestor LIKE '".$_GET['ancestor']."'";
			$tmp['ancestors'] = $this->getAncestors($_GET['ancestor']);
			$tmp['uri'] .= '&ancestor='.$_GET['ancestor'];
		}
		else
			$tmp['ancestors'] = $this->getAncestors('*');
		
		return $tmp;
	}
	
	private function loadMembersList(){
		
		if(isset($_GET['contacts']))
			$table = 'contacts';
		else
			$table = 'members';
		
		$tmp = $this->getDefaultValues();
		
		if($table == 'contacts'){
			$tmp['h3_3'] = _('Sugulaste kontaktid');
			$tmp['members_active'] = '';
			$tmp['contacts_active'] = 'active';
			$tmp['reset_href'] = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=oviirid&contacts';
		}
		
		// filter
		$filter = $this->getFilter();
		$where = $filter['where'];
		$uri = $filter['uri'];
		$tmp['first_name'] = $filter['first_name'];
		$tmp['last_name'] = $filter['last_name'];
		$tmp['ancestors'] = $filter['ancestors'];
		
		$page = 1;
		$limit = 50;
		$start = 0;
		// invitation
		$inv_arr = array(
			'no'=>'ei',
			'post'=>'post',
			'email'=>'e-post'
		);
		// relation
		$rel_arr = array(
			0=>'',
			1=>'Veresugulane',
			2=>'Hõimlane'
		);
		
		if(isset($_GET['page'])){
			$page = $_GET['page'];
			$start = ($page-1)*$limit;
		}

		$qlimit = " LIMIT ".$start.", ".$limit;
		
		// actions panel
		if($this->framework->users->userHaveRights('edit'))
			$tmp['actions'] = '<a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=profiil&add" class="btn btn-success" style="width:60px">'._('Lisa uus').'</a>';
		
		// sorting array
		if($table == 'contacts')
			$sortlist = $this->getSortList('contacts',$page,$uri);
		else
			$sortlist = $this->getSortList('',$page,$uri);
		
		// sorting order
		$sortby = 'first_name';
		$order = isset($_GET['desc'])?'desc':'asc';
		
		if(isset($_GET['sort']) && array_key_exists($_GET['sort'],$sortlist))
			$sortby = $_GET['sort'];
		
		$sortlist[$sortby]['classes'] = 'sortby '.$order;
		if($order == 'asc')
			$sortlist[$sortby]['url'] .= '&desc';
		
		// filter url
		$tmp['filter_url'] = $this->framework->url().'/';
		if(isset($_GET['sort']))
			$tmp['sort'] = '<input type="hidden" name="sort" value="'.$sortby.'">';
		if(isset($_GET['desc']))
			$tmp['sort'] .= '<input type="hidden" name="desc">';
		if($table == 'contacts')
			$tmp['sort'] .= '<input type="hidden" name="contacts">';
		
		// build header row
		$tmp['table'] .= '<tr class="header"><th style="width:40px"></th>';
		$tmp['table'] .= '<th style="width:90px" class="align-left '.$sortlist['first_name']['classes'].'"><a href="'.$sortlist['first_name']['url'].'">'._('Eesnimi').'</a></th>';
		$tmp['table'] .= '<th style="width:90px" class="align-left '.$sortlist['last_name']['classes'].'"><a href="'.$sortlist['last_name']['url'].'">'._('Perenimi').'</a></th>';
		
		if($table == 'contacts'){
			$tmp['table'] .= '<th style="width:250px" class="align-left '.$sortlist['address']['classes'].'"><a href="'.$sortlist['address']['url'].'">'._('Aadress').'</a></th>';
			$tmp['table'] .= '<th style="width:90px" class="align-left">'._('E-post').'</th>';
			$tmp['table'] .= '<th class="align-left">'._('Telefon').'</th>';
			$tmp['table'] .= '<th style="width:70px" class="align-right">'._('Kutse?').'</th>';
		}else{
			$tmp['table'] .= '<th style="width:150px" class="align-left">'._('Sünd (- surm)').'</th>';
			$tmp['table'] .= '<th style="width:90px" class="align-left '.$sortlist['generation']['classes'].'"><a href="'.$sortlist['generation']['url'].'">'._('Põlvkond').'</a></th>';
			$tmp['table'] .= '<th class="align-left '.$sortlist['relation']['classes'].'"><a href="'.$sortlist['relation']['url'].'">'._('Sugulusside').'</a></th>';
			$tmp['table'] .= '<th class="align-right '.$sortlist['ancestor']['classes'].'"><a href="'.$sortlist['ancestor']['url'].'">'._('Puu haru').'</a></th>';
		}
		$tmp['table'] .= '</tr>';
		
		if($table == 'contacts')
			$where .= " AND status!='disabled'";
		
		$q = "SELECT * FROM $this->db_table WHERE status!='hidden' AND deleted = 0".$where." ORDER BY ".$sortby." ".$order.$qlimit;
		$result = $this->framework->db->query($q);
		$i = $start+1;
		while($row = mysql_fetch_assoc($result)){
				
			foreach($row as $k => $v){
				$row[$k] = stripslashes($v);
			}
			
			// years
			$lives = '';
			if(!empty($row['birth']))
				$lives .= '('.$row['birth'];
			if(!empty($row['death']))
				$lives .= ' - '.$row['death'].')';
			else if(!empty($row['birth']))
				$lives .= ')';
			$row['invitation'] = $inv_arr[$row['invitation']];
			
			$tmp['table'] .= '<tr>';
			$tmp['table'] .= '<td class="align-center"><b>'.$i.'</b></dt>';
			$tmp['table'] .= '<td><a class="" href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=profiil&user='.$row['id'].'">'.$row['first_name'].'</a></dt>';
			$tmp['table'] .= '<td><a class="" href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=profiil&user='.$row['id'].'">'.$row['last_name'].'</a></dt>';
			
			if($table == 'contacts'){
				$tmp['table'] .= '<td>'.$row['address'].'</dt><td>'.$row['email'].'</dt><td>'.$row['phone'].'</dt><td class="align-right">'.$row['invitation'].'</dt>';
			}else{
				$tmp['table'] .= '<td>'.$lives.'</dt><td></dt><td>'.$rel_arr[$row['relation']].'</dt><td class="align-right">'.$row['ancestor'].'</dt>';
			}
			
			$tmp['table'] .= '</tr>';
			$i++;
		}
		
		// pages
		$q = "SELECT COUNT(id) AS count FROM $this->db_table WHERE status!='hidden' AND deleted = 0".$where;
		$result = $this->framework->db->query($q);
		if($row = mysql_fetch_assoc($result)){
			$pages_count = ceil($row['count']/$limit);
			if($pages_count>1){
				for($i=0;$i<$pages_count;$i++){
					$active = $i+1 == $page?'active':'';
					if($table == 'contacts')
						$add = 'contacts&';
					else
						$add = '';
					$tmp['pages'] .= '<a class="'.$active.'" href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=oviirid&'.$add.'page='.($i+1).'&sort='.$sortby.'&'.$order.$uri.'">'.($i+1).'</a>';
				}
			}	
		}
		
		$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/oviirid.html',$tmp);
	}

	public function getAncestors($u){
		$q = "SELECT ancestor FROM $this->db_table WHERE status!='hidden' AND deleted=0 GROUP BY ancestor ORDER BY ancestor ASC";
		$result = $this->framework->db->query($q);
		
		$content = '';
		while($row = mysql_fetch_assoc($result)){
			$s = $u == $row['ancestor']?'selected="selected"':'';
			$content .= '<option '.$s.' value="'.$row['ancestor'].'">'.$row['ancestor'].'</option>';
		}
		return $content;
	}
	
	// get all data for page printing and return printpage
    public function printList(){
		
		if($this->access == 'private' && $_SESSION['Framework']['auth_status'] == 'public'){
			$this->framework->redirect($this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=login&ref='.urlencode($this->framework->getCurrenturl()));
			exit();
		}
		
		$tmp = array();
		$tmp['content'] = '';
		
		if(isset($_GET['contacts']))
			$table = 'contacts';
		else
			$table = 'members';
		
		// filter
		$filter = $this->getFilter();
		$where = $filter['where'];
		$uri = $filter['uri'];
		
		// invitation
		$inv_arr = array(
			'no'=>'ei',
			'post'=>'post',
			'email'=>'e-post'
		);
		// relation
		$rel_arr = array(
			0=>'',
			1=>'Veresugulane',
			2=>'Hõimlane'
		);
		
		// sorting array
		if($table == 'contacts')
			$sortlist = $this->getSortList('contacts',$page,$uri);
		else
			$sortlist = $this->getSortList('',$page,$uri);
		
		// sorting order
		$sortby = 'first_name';
		$order = isset($_GET['desc'])?'desc':'asc';
		
		if(isset($_GET['sort']) && array_key_exists($_GET['sort'],$sortlist))
			$sortby = $_GET['sort'];
		
		if($table == 'contacts')
			$where .= " AND status!='disabled'";
		
		//$q = "SELECT * FROM $this->db_table WHERE status!='hidden' AND deleted = 0".$where." ORDER BY ".$sortby." ".$order.$qlimit;
		$q = "SELECT * FROM $this->db_table WHERE status!='hidden' AND deleted = 0".$where." ORDER BY ancestor,first_name,last_name";
		$result = $this->framework->db->query($q);
		$htmlarr = array();
		$i = 0;
		while($row = mysql_fetch_assoc($result)){
				
			foreach($row as $k => $v){
				$row[$k] = stripslashes($v);
			}
			
			// years
			$row['lives'] = '';
			if(!empty($row['birth']))
				$row['lives'] .= '('.$row['birth'];
			if(!empty($row['death']))
				$row['lives'] .= ' - '.$row['death'].')';
			else if(!empty($row['birth']))
				$row['lives'] .= ')';
			$row['invitation'] = $inv_arr[$row['invitation']];
			
			$htmlarr[$i] = array('first_name'=>trim($row['first_name']),'last_name'=>trim($row['last_name']),'address'=>trim($row['address']),'email'=>trim($row['email']),'phone'=>trim($row['phone']),'ancestor'=>trim($row['ancestor']),'invitation'=>$row['invitation']);
			$i++;
		}
			
		$htmlarr = $this->arrayBuild($htmlarr,'ancestor');
		$html['data_content'] = '';
		
		foreach($htmlarr as $ancestor=>$childrens){
			$html['data_content'] .= '<h2>'.$ancestor.'</h2><h3>'._('Järeltulijad').'</h3><table>';
			foreach($childrens as $children){
				$html['data_content'].='<tr><td><b>'.$children['first_name'].' '.$children['last_name'].'</b></td><td>'.$children['address'].'</td><td>'.$children['email'].'</td><td>'.$children['phone'].'</td></tr>';
			}
			$html['data_content'].='</table>';
		}
			
		$tmp['content'] = $this->framework->templator->parse(dirname(__FILE__).'/printlist.html',$html);
		$tmp['content_type'] = 'html';
		return $tmp;
    }

	public function getAllEmails(){
			
		$tmp = array(
			content => array(
				status => 0,
				data => ''
			),
			content_type => 'json'
		);
		
		if($this->access == 'private' && $_SESSION['Framework']['auth_status'] == 'public')
			$this->framework->send404();
		
		$q = "SELECT email FROM $this->db_table WHERE status!='hidden' AND deleted = 0 AND email IS NOT NULL ORDER BY ancestor,email";
		$result = $this->framework->db->query($q);
		$htmlarr = array();
		
		while($row = mysql_fetch_assoc($result)){
			foreach($row as $k => $v){
				$row[$k] = stripslashes(trim($v));
			}
			
			$htmlarr[] = $row['email'];
		}
		$tmp['content']['data'] = implode(', ',$htmlarr);
		$tmp['content']['status'] = 1;
		return $tmp;
	}

	# rebuild array
	private function arrayBuild($arr,$key){
    	$newarr = array();
    	foreach($arr as $line){
    		$newarr[$line[$key]][]=$line;
    	}
    	return $newarr;
    }
	
	public function getOutput(){
		return $this->output;
	}
	
}
?>