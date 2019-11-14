<?php
/*
 * PHP Framework
 * Version 2012.12
 * Mihkel Oviir
 * 
 * Modul Class Framework_Modules_varasalv_modul
 */
class Framework_Modules_varasalv_modul {
	
	protected $framework;
	public $queryable = true;
	protected $access = 'private';
	public $menuitem = 'varasalv';
	protected $db_table = 'dev_2013_documents';
	protected $db_members_table = 'dev_2012_members';
	protected $types_arr = array();
	protected $relative_path = 'varad';
	protected $path;
	protected $uploadfiletypes = array(
		document => '/(\.|\/)(txt|zip|pdf|doc|docx|xls|xlsx)$/i',
		picture => '/(\.|\/)(gif|jpe?g|png)$/i',
		sound => '/(\.|\/)(mp3)$/i'
	);
	protected $output = array();
	
	function __construct($framework){
		$this->framework = $framework;
		$this->output['title'] = _('Suguvõsa varasalv');
		$this->output['ajax-handler'] = $this->framework->conf['core']['ajax_handler'].'?'.$this->framework->conf['core']['module_param'].'=varasalv';
		
		$this->output['modul-styles'] = '<link type="text/css" rel="stylesheet" href="/libs/jquery/jquery-selectBox/jquery.selectBox.css" />';
		$this->output['modul-styles'] .= '<link rel="stylesheet" href="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/varasalv.css">';
		
		$this->output['modul-scripts'] = '<script type="text/javascript" src="/libs/jquery/jquery-selectBox/jquery.selectBox.js"></script>';
		$this->output['modul-scripts'] .= '<script type="text/javascript" src="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/modul.js"></script>';
		
		$this->types_arr = array(
			'document'=>_('Dokument'),
			'video'=>_('Video'),
			'sound' => _('Helisalvestus'),
			'picture'=>_('Pilt'),
			'link'=>_('Viide')
		);
		
		$this->path = APP_PATH.DIRECTORY_SEPARATOR.$this->relative_path;
	}
	
	public function front(){
		if($this->access == 'private' && $_SESSION['Framework']['auth_status'] == 'public')
			$this->framework->redirect($this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=login&ref='.urlencode($this->framework->getCurrenturl()));
		else{
			if(isset($_GET['id']) && !empty($_GET['id']))
				$this->loadItemPage($_GET['id']);
			elseif(isset($_GET['add']) && array_key_exists($_GET['add'], $this->types_arr))
				$this->loadAddingPage($_GET['add']);
			elseif(isset($_GET['edit']) && !empty($_GET['edit']))
				$this->loadEditPage($_GET['edit']);
			elseif(isset($_GET['table']))
				$this->loadDocumentsList();
			else
				$this->loadCategoryView();
		}
	}
	
	private function getDefaultValues(){
		return array(
			h3_1 => _('Teadmiseks'),
			h3_2 => _('Valikud'),
			h3_3 => _('Varad salves'),
			h3_4 => _('Toimingud'),
			h3_5 => _('Otsing'),
			actions => '',
			table => '',
			pages => '',
			ph_title => _('Otsisõna'),
			ph_related_with => _('Seotud isik'),
			choose_type => _('Vali tüüp'),
			types => '',  
			'reset' => _('Tühjenda väljad'),
			submit => _('Otsi'),
			'sort' => '',
			reset_href => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=varasalv',
			// adding
			loading => $this->framework->getTemplateUrl().'/images/loading.gif',
			wait => _('Oota, laeb...'),
			cancel => _('Tühista'),
			cancel_href => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=varasalv',
			save => _('Salvesta'),
			back_to_list => _('Tagasi'),
			extra_field => '',
			table_links => ''
		);
	}
	
	private function getFilter(){
		$tmp = array();
		$tmp['where'] = "";
		$tmp['uri'] = '';
		if(!empty($_GET['title'])){
			$tmp['where'] .= " AND LOWER(t1.title) LIKE LOWER('%".filter_input(INPUT_GET, 'title', FILTER_SANITIZE_STRING)."%')";
			$tmp['title'] = $_GET['title'];
			$tmp['uri'] .= '&title='.$_GET['title'];
		}
		
		if(!empty($_GET['related_with'])){
			$tmp['where'] .= " AND t1.related_with=".filter_input(INPUT_GET, 'related_with', FILTER_SANITIZE_STRING);
			$tmp['related_with'] = $_GET['related_with'];
			$tmp['uri'] .= '&related_with='.$_GET['related_with'];
		}
		
		if(!empty($_GET['type'])){
			$tmp['where'] .= " AND t1.type LIKE '".$_GET['type']."'";
			$tmp['type'] = $_GET['type'];
			$tmp['uri'] .= '&type='.$_GET['type'];
		}
		
		return $tmp;
	}
	
	private function getSortList($table,$page,$uri){
		if($table != '')
			$table .= '&';
		return array(
			title => array(
				classes => '',
				url => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=varasalv&'.$table.'page='.$page.'&sort=title'.$uri
			),
			related_name => array(
				classes => '',
				url => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=varasalv&'.$table.'page='.$page.'&sort=related_name'.$uri
			),
			added => array(
				classes => '',
				url => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=varasalv&'.$table.'page='.$page.'&sort=added'.$uri
			)
		);
	}
	
	private function getTypeIcon($type,$filename){
		$img = 'txt';
		switch($type){
			case 'document':
				if(strpos($filename,'pdf')!==false)
					$img = 'pdf';
				else if(strpos($filename,'doc')!==false)
					$img = 'doc';
				else if(strpos($filename,'xls')!==false)
					$img = 'xls';
				else if(strpos($filename,'zip')!==false)
					$img = 'zip';
			break;
			case 'picture':
				$img = 'picture';
			break;
			case 'sound':
				$img = 'music';
			break;
			case 'video':
				$img = 'film';
			break;
			case 'link':
				$img = 'html';
			break;
		}
		return $img;
	}

	// rebuild array
	private function arrayBuild($arr,$key){
    	$newarr = array();
    	foreach($arr as $line){
    		$newarr[$line[$key]][]=$line;
    	}
    	return $newarr;
    }

	private function loadCategoryView(){
		$types_arr = array(
			'document'=>_('Dokumendid'),
			'video'=>_('Videod'),
			'sound' => _('Helisalvestused'),
			'picture'=>_('Pildid'),
			'link'=>_('Viited')
		);
		
		$tmp = $this->getDefaultValues();
		$tmp['table_links'] = '<a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=varasalv&table">'._('Varad tabeli kujul').'</a>';
		
		$arr = array();
		$q = "SELECT t1.*,CONCAT(t2.first_name,' ',t2.last_name) AS related_name FROM $this->db_table AS t1 LEFT JOIN $this->db_members_table AS t2 ON (t1.related_with=t2.id) WHERE t1.deleted = 0 ORDER BY related_name ASC,type ASC,added DESC";
		$result = $this->framework->db->query($q);
		$j = 0;
		while($row = mysql_fetch_assoc($result)){
			foreach($row as $k => $v){
				$row[$k] = stripslashes($v);
			}
			$arr[] = $row;
			$j++;
		}
		
		$arr = $this->arrayBuild($arr,'related_name');
		
		foreach($arr as $k=>$v){
			if($k == '')
				$tmp['table'] .= '<h3>'._('Suguvõsa varad').'</h3>';
			else
				$tmp['table'] .= '<h3>'.$k.'</h3>';
			$v = $this->arrayBuild($v,'type');
			foreach($v as $type=>$list){
				$tmp['table'] .= '<h4>'.$types_arr[$type].'</h4><ul>';
				foreach ($list as $i => $item) {
					$item['title'] = empty($item['title'])?_('Pealkirjata'):$item['title'];
					$item['content'] = json_decode($item['content'],true);
					$img = $this->getTypeIcon($item['type'],$item['content']['name']);
					$tmp['table'] .= '<li><img src="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/images/'.$img.'.png" title="'.$types_arr[$item['type']].'"/> ';
					$tmp['table'] .= '<a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=varasalv&id='.$item['id'].'">'.$item['title'].'</a>';
					$tmp['table'] .= '<em class="float-right grey">'._('Lisatud: ').date('d.m.Y',strtotime($item['added'])).'</em><div class="clear"></div></li>';
				}
				$tmp['table'] .= '</ul>';
			}
		}

		// if no rows
		if($j == 0)
			$tmp['table'] = '<div class="grey">'._('Varad salves puuduvad').'</div>';
		
		// actions panel
		$tmp['actions'] = $this->getAddingButtons();
		$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/category_view.html',$tmp);
	}
	
	private function loadDocumentsList(){
		$tmp = $this->getDefaultValues();
		
		// filter
		$filter = $this->getFilter();
		$where = $filter['where'];
		$uri = $filter['uri'];
		$tmp['title'] = $filter['title'];
		$tmp['related_with'] = $filter['related_with'];
		$tmp['type'] = $filter['type'];
		
		$page = 1;
		$limit = 50;
		$start = 0;
		
		$types_arr = $this->types_arr;
		
		foreach($types_arr as $k=>$v)
			$tmp['types'] .= '<option value="'.$k.'">'.$v.'</option>';
		
		if(isset($_GET['page'])){
			$page = $_GET['page'];
			$start = ($page-1)*$limit;
		}

		$qlimit = " LIMIT ".$start.", ".$limit;
		
		// sorting array
		$sortlist = $this->getSortList('',$page,$uri);
		
		// default sorting order
		$sortby = 'added';
		$order = isset($_GET['desc'])?'desc':'asc';
		
		if(isset($_GET['sort']) && array_key_exists($_GET['sort'],$sortlist))
			$sortby = $_GET['sort'];
		// default order by added time desc
		else
			$order = 'desc';
		
		$sortlist[$sortby]['classes'] = 'sortby '.$order;
		if($order == 'asc')
			$sortlist[$sortby]['url'] .= '&desc';
		
		// filter url
		$tmp['filter_url'] = $this->framework->url().'/';
		if(isset($_GET['sort']))
			$tmp['sort'] = '<input type="hidden" name="sort" value="'.$sortby.'">';
		if(isset($_GET['desc']))
			$tmp['sort'] .= '<input type="hidden" name="desc">';
		
		// build header row
		$tmp['table'] .= '<tr class="header"><th style="width:40px"></th>';
		$tmp['table'] .= '<th style="width:390px" class="align-left '.$sortlist['title']['classes'].'"><a href="'.$sortlist['title']['url'].'">'._('Pealkiri').'</a></th>';
		$tmp['table'] .= '<th style="width:60px" class="align-center"></th>';
		$tmp['table'] .= '<th style="width:90px" class="align-left '.$sortlist['related_name']['classes'].'"><a href="'.$sortlist['related_name']['url'].'">'._('Sugulane').'</a></th>';
		$tmp['table'] .= '<th class="align-right '.$sortlist['added']['classes'].'"><a href="'.$sortlist['added']['url'].'">'._('Lisatud').'</a></th>';
		$tmp['table'] .= '</tr>';
		
		$q = "SELECT t1.*,CONCAT(t2.first_name,' ',t2.last_name) AS related_name FROM $this->db_table AS t1 LEFT JOIN $this->db_members_table AS t2 ON (t1.related_with=t2.id) WHERE t1.deleted = 0".$where." ORDER BY ".$sortby." ".$order.$qlimit;
		$result = $this->framework->db->query($q);
		$i = $start+1;
		while($row = mysql_fetch_assoc($result)){
			foreach($row as $k => $v){
				$row[$k] = stripslashes($v);
			}
			
			$row['title'] = empty($row['title'])?_('Pealkirjata'):$row['title'];
			$row['content'] = json_decode($row['content'],true);
			$img = $this->getTypeIcon($row['type'],$row['content']['name']);
			
			$tmp['table'] .= '<tr>';
			$tmp['table'] .= '<td class="align-center"><b>'.$i.'</b></dt>';
			$tmp['table'] .= '<td class="align-left"><a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=varasalv&id='.$row['id'].'">'.$row['title'].'</a></dt>';
			$tmp['table'] .= '<td class="align-center"><img src="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/images/'.$img.'.png" title="'.$types_arr[$row['type']].'"/></dt>';
			$tmp['table'] .= '<td class="align-left">'.$row['related_name'].'</dt>';
			$tmp['table'] .= '<td class="align-right">'.date('d.m.Y',strtotime($row['added'])).'</dt>';
			$tmp['table'] .= '</tr>';
			$i++;
		}
		
		// if no rows
		if($i == $start+1)
			$tmp['table'] = '<div class="grey">'._('Varad salves puuduvad').'</div>';
			
		// pages
		$q = "SELECT COUNT(id) AS count FROM $this->db_table t1 WHERE deleted = 0".$where;
		$result = $this->framework->db->query($q);
		if($row = mysql_fetch_assoc($result)){
			$pages_count = ceil($row['count']/$limit);
			if($pages_count>1){
				for($i=0;$i<$pages_count;$i++){
					$active = $i+1 == $page?'active':'';
					$add = '';
					$tmp['pages'] .= '<a class="'.$active.'" href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=varasalv&'.$add.'page='.($i+1).'&sort='.$sortby.'&'.$order.$uri.'">'.($i+1).'</a>';
				}
			}	
		}

		// actions panel
		$tmp['actions'] = $this->getAddingButtons();
		$tmp['table_links'] = '<a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=varasalv">'._('Varad nimekirja kujul').'</a>';
		
		$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/varasalv.html',$tmp);
	}
	
	private function getAddingButtons(){
		$b = '';
		if($this->framework->users->userHaveRights('edit')){
			$b = '<h3>'._('Toimingud').'</h3>';
			$b .= '<a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=varasalv&add=document" class="btn btn-success" style="width:120px"><b>'._('Uus dokument').'</b></a>';
			$b .= '<div style="height:6px"></div>';
			$b .= '<a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=varasalv&add=video" class="btn btn-success" style="width:120px"><b>'._('Uus video').'</b></a>';
			$b .= '<div style="height:6px"></div>';
			$b .= '<a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=varasalv&add=sound" class="btn btn-success" style="width:120px"><b>'._('Uus helisalvestus').'</b></a>';
			$b .= '<div style="height:6px"></div>';
			$b .= '<a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=varasalv&add=picture" class="btn btn-success" style="width:120px"><b>'._('Uus pilt').'</b></a>';
			$b .= '<div style="height:6px"></div>';
			$b .= '<a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=varasalv&add=link" class="btn btn-success" style="width:120px"><b>'._('Uus viide').'</b></a>';
		}
		return $b;
	}
	
	private function loadItemPage($id){
		$tmp = $this->getDefaultValues();
		$tmp['texts'] = _('Varad on jaotatud nelja tüüpi: teos ehk dokument, pilt, video ja viide. Pilti ja videot näeb siin lehel, pilti saab ka eraldi aknasse avada ja oma arvutisse salvestada. Dokumendi vaatamiseks tuleb see allalaadimise nupust omale arvutisse laadida.');
		$tmp['edit'] = _('Muuda');
		$tmp['edit_href'] = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=varasalv&edit='.$id;
		
		if($this->framework->users->userHaveRights('edit'))
			$tmp['actions'] = '<h3>'.$tmp['h3_4'].'</h3><a href="'.$tmp['edit_href'].'" class="btn btn-danger" style="width:60px">'.$tmp['edit'].'</a>';
		
		$q = "SELECT t1.*,CONCAT(t2.first_name,' ',t2.last_name) AS related_name FROM $this->db_table AS t1 LEFT JOIN $this->db_members_table AS t2 ON (t1.related_with=t2.id) WHERE t1.deleted=0 AND t1.id = ".$id;
		$result = $this->framework->db->query($q);
		if($row = mysql_fetch_assoc($result)){
			foreach($row as $k => $v){
				$row[$k] = stripslashes($v);
			}
			
			$tmp = array_merge($tmp,$row);
			
			if(empty($tmp['title']))
				$tmp['title'] = _('Pealkirjata');
			if(empty($tmp['related_with']))
				$tmp['relation'] = '';
			else
				$tmp['relation'] = '<h4>Seotud sugulasega: <a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=profiil&user='.$tmp['related_with'].'">'.$tmp['related_name'].'</a></h4>';
			if(empty($tmp['description']))
				$tmp['description'] = _('Kirjeldus puudub');
			$tmp['content'] = json_decode($tmp['content'],true);
			
			switch($row['type']){
				case 'document':
					$this->loadDocumentPage($tmp);
				break;
				case 'video':
					$this->loadVideoPage($tmp);
				break;
				case 'sound':
					$this->loadSoundPage($tmp);
				break;
				case 'picture':
					$this->loadPicturePage($tmp);
				break;
				case 'link':
					$this->loadLinkPage($tmp);
				break;
			}
		}else
			$this->framework->send404();
	}
	
	private function loadDocumentPage($tmp){
		$img = $this->getTypeIcon($tmp['type'],$tmp['content']['name']);
		$tmp['link'] = '<img src="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/images/'.$img.'.png" title="'.$this->types_arr[$tmp['type']].'"/> ';
		$tmp['link'] .= '<a href="'.$this->framework->url().'/'.$this->relative_path.'/'.$tmp['type'].'/'.$tmp['content']['name'].'" target="_blank"><b>'.$this->framework->url().'/'.$this->relative_path.'/'.$tmp['type'].'/'.$tmp['content']['name'].'</b></a>';
		$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/'.$tmp['type'].'.html',$tmp);
	}

	private function loadSoundPage($tmp){
		$this->output['modul-scripts'] = '<script type="text/javascript" src="/libs/audiojs/audiojs/audio.min.js"></script>';
		$this->output['modul-scripts'] .= '<script type="text/javascript" src="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/playsound.js"></script>';
		$tmp['link'] = $this->framework->url().'/'.$this->relative_path.'/'.$tmp['type'].'/'.$tmp['content']['name'];
		$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/'.$tmp['type'].'.html',$tmp);
	}
	
	private function loadPicturePage($tmp){
		$img = $this->getTypeIcon($tmp['type'],$tmp['content']['name']);
		$tmp['picture'] = $this->framework->url().'/'.$this->relative_path.'/'.$tmp['type'].'/'.$tmp['content']['name'];
		$tmp['link'] = '<img src="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/images/'.$img.'.png" title="'.$this->types_arr[$tmp['type']].'"/> ';
		$tmp['link'] .= '<a href="'.$this->framework->url().'/'.$this->relative_path.'/'.$tmp['type'].'/'.$tmp['content']['name'].'" target="_blank"><b>'.$this->framework->url().'/'.$this->relative_path.'/'.$tmp['type'].'/'.$tmp['content']['name'].'</b></a>';
		$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/'.$tmp['type'].'.html',$tmp);
	}
	
	private function loadLinkPage($tmp){
		$img = $this->getTypeIcon($tmp['type'],$tmp['content']['name']);
		$tmp['link'] = '<img src="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/images/'.$img.'.png" title="'.$this->types_arr[$tmp['type']].'"/> ';
		$tmp['link'] .= '<a href="'.$tmp['content']['name'].'" target="_blank"><b>'.$tmp['content']['name'].'</b></a>';
		$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/'.$tmp['type'].'.html',$tmp);
	}
	
	private function loadVideoPage($tmp){
		$tmp['link'] = $tmp['content']['name'];
		$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/'.$tmp['type'].'.html',$tmp);
	}
	
	private function loadAddingPage($type){
		if(!$this->framework->users->userHaveRights('edit'))
			$this->framework->send404();
		
		$tmp = $this->getDefaultValues();
		$tmp['type'] = $type;

		if($type == 'document' || $type == 'sound' || $type == 'picture')
			$this->loadUploadPage($tmp);
		else
			$this->loadEditPage(-1,$type);
	}

	private function loadUploadPage($tmp){
			
		switch($tmp['type']){
			case 'document':
				$tmp['h3_3'] = _('Lae uus dokument (.pdf, .txt, .doc, .docx, .xls, .xlsx, .zip) üles');
			break;
			case 'picture':
				$tmp['h3_3'] = _('Lae uus pildifail (.jpg, .jpeg, .gif, .png) üles');
			break;
			case 'sound':
				$tmp['h3_3'] = _('Lae uus helifail (.mp3) üles');
			break;
		}
		
		$tmp['texts'] = _('Kasuta + nuppu, et lisada uus dokument või lohista oma arvutist dokument alla kasti');
		$tmp['filetypes'] = $this->uploadfiletypes[$tmp['type']];
		
		$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/add_'.$file.'.html',$tmp);
		$this->output['modul-styles'] = '<link type="text/css" rel="stylesheet" href="/libs/jquery/jquery-fileUpload/css/jquery.fileupload-ui.css" />'.$this->output['modul-styles'];
		$this->output['modul-scripts'] = '<script type="text/javascript" src="/libs/jquery/jquery-fileUpload/js/vendor/jquery.ui.widget.js"></script>';
		$this->output['modul-scripts'] .= '<script type="text/javascript" src="/libs/jquery/jquery-fileUpload/js/jquery.iframe-transport.js"></script>';
		$this->output['modul-scripts'] .= '<script type="text/javascript" src="/libs/jquery/jquery-fileUpload/js/jquery.fileupload.js"></script>';
		$this->output['modul-scripts'] .= '<script type="text/javascript" src="/libs/jquery/jquery-fileUpload/js/jquery.fileupload-fp.js"></script>';
		$this->output['modul-scripts'] .= '<script type="text/javascript" src="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/upload.js"></script>';
	}
	
	private function loadEditPage($id,$type=null){
		if(!$this->framework->users->userHaveRights('edit'))
			$this->framework->send404();
		
		$tmp = $this->getDefaultValues();
		
		$tmp['texts'] = _('Tärniga väljad on kohustuslikud. Vara saab siduda isikuga. Seda on mõtet teha ainult siis, kui materjal käib konkreetselt ühe sugulase kohta või ta on materjali autor. Ära unusta ka vara kirjeldust!');
		$tmp['ph_title'] = _('Pealkiri');
		$tmp['relation_note'] = _('Alusta nime kirjutamist, süsteem pakub vastavaid valikuid. Kui otsitavat nime ei leidu, jäta see väli täitmata');
		
		// if adding new
		if($id == -1){
			$tmp['cancel_href'] = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=varasalv';
			$tmp['type'] = $type;
			$tmp['id'] = $id;
			$tmp['filename'] = $tmp['title'] = $tmp['related_name'] = $tmp['related_with'] = $tmp['description'] = '';
			
		}
		// if edit old
		else{
			$tmp['cancel_href'] = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=varasalv&id='.$id;
			$q = "SELECT t1.*,CONCAT(t2.first_name,' ',t2.last_name) AS related_name FROM $this->db_table AS t1 LEFT JOIN $this->db_members_table AS t2 ON (t1.related_with=t2.id) WHERE t1.deleted=0 AND t1.id = ".$id;
			$result = $this->framework->db->query($q);
			if($row = mysql_fetch_assoc($result)){
				foreach($row as $k => $v){
					$row[$k] = stripslashes($v);
				}
				$tmp = array_merge($tmp,$row);
				$tmp['content'] = json_decode($tmp['content'],true);
				$tmp['filename'] = $tmp['content']['name'];
			}else
				$this->framework->send404();
			// delete button is visible only for admin and if edit
			if($this->framework->users->userHaveRights('edit'))
				$tmp['actions'] .= '<div style="height:34px">&nbsp;</div><a href="delete" class="btn btn-danger delete" style="width:60px">'._('Kustuta').'</a>';
		}
		
		switch($tmp['type']){
			case 'document':
				$tmp['h3_3'] = _('Dokument');
				$tmp['item_title'] = 'Dokumendi pealkiri';
				$tmp['ph_related_name'] = _('Dokumendiga seotud sugulane');
				$tmp['ph_description'] = _('Kirjelda dokumenti');
			break;
			case 'video':
				$tmp['h3_3'] = _('Video');
				$tmp['item_title'] = 'Video pealkiri';
				$tmp['ph_related_name'] = _('Videoga seotud sugulane');
				$tmp['ph_description'] = _('Kirjelda videot');
				$tmp['extra_field'] = '<h4>'._('Video link').'</h4><div><input type="text" class="text" name="link" placeholder="*'._('Video link').'" value="'.$tmp['filename'].'"></div>';
				$tmp['extra_field'] .= '<em class="small grey video-note">Kopeeri siia YouTube video aadress. Kui aadress on õige, siis süsteem muudab ise aadressi ja siia tuleb vastav teade</em>';
			break;
			case 'sound':
				$tmp['h3_3'] = _('Helisalvestus');
				$tmp['item_title'] = 'Salvestuse pealkiri';
				$tmp['ph_related_name'] = _('Salvestusega seotud sugulane');
				$tmp['ph_description'] = _('Kirjelda salvestust');
			break;
			case 'picture':
				$tmp['h3_3'] = _('Pilt');
				$tmp['item_title'] = 'Pildi pealkiri';
				$tmp['ph_related_name'] = _('Pildiga seotud sugulane');
				$tmp['ph_description'] = _('Kirjelda pilti');
			break;
			case 'link':
				$tmp['h3_3'] = _('Veebilink');
				$tmp['item_title'] = 'Lingi pealkiri';
				$tmp['ph_related_name'] = _('Lingiga seotud sugulane');
				$tmp['ph_description'] = _('Kirjelda linki');
				$tmp['extra_field'] = '<h4>'._('Veebiaadress').'</h4><div><input type="text" class="text" name="link" placeholder="* '._('Veebiaadress').'" value="'.$tmp['filename'].'"></div>';
			break;
		}
		
		$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/edit_document.html',$tmp);
		$this->output['modul-scripts'] = '<script type="text/javascript" src="/libs/nicEdit/nicEdit.js"></script>'.$this->output['modul-scripts'];
	}
	
	public function uploadFiles(){
		if(!$this->framework->users->userHaveRights('edit') || !array_key_exists($_GET['type'],$this->types_arr))
			$this->framework->send404();
			
		$tmp = array(
			content_type => 'plain',
			content => array(
				status => 0
			)
		);
		
		$opts = array(
			'upload_dir' => $this->path.'/'.$_GET['type'],
			'param_name' => 'files',
			'accept_file_types' => $this->uploadfiletypes[$_GET['type']]
		);
		
		require FRAMEWORK_PATH.DIRECTORY_SEPARATOR.'Framework'.DIRECTORY_SEPARATOR.'UploadHandler.php';
		$uploader = new UploadHandler($opts);
		$files = $uploader->uploadedFiles;
		
		if(!isset($files[0]->error) && isset($files[0]->name)){
			
			$q = "INSERT INTO $this->db_table (type,content,title,author,added) VALUES ('".$_GET['type']."','".json_encode($files[0])."','".$files[0]->name."',".$_SESSION['Framework']['current_user']['id'].",NOW());";
			if($this->framework->db->query($q)){
				$files[0]->id = mysql_insert_id();
				$tmp['content']['url'] = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=varasalv&edit='.$files[0]->id;
				$tmp['content']['status'] = 1;
			}
		}else
			$tmp['content']['path'] = $this->path.'/'.$_GET['type'];
		
		$tmp['content']['files'] = $files;
		$tmp['content'] = json_encode($tmp['content']);
		return $tmp;
	}

	public function deleteDocument(){
		
		if(!$this->framework->users->userHaveRights('edit'))
			$this->framework->send404();
		
		$tmp = array(
			content_type => 'json',
			content => array(
				status => 0
			)
		);
		
		if(isset($_POST['id']) && $_POST['id'] != '' && isset($_POST['type']) && $_POST['type'] != ''){
			
			if($_POST['type'] == 'document' || $_POST['type'] == 'sound' || $_POST['type'] == 'picture'){
			
				$q = "SELECT * FROM $this->db_table WHERE id = ".$_POST['id'];
				$result = $this->framework->db->query($q);
				if($row = mysql_fetch_assoc($result)){
					$content = json_decode($row['content'],true);
					$file = $this->path.'/'.$row['type'].'/'.$content['name'];
					if(is_file($file) && is_dir($this->path.'/kustutatud')){
						$newname = $this->path.'/kustutatud/'.date('YmdHis').'-'.$content['name'];
						rename($file,$newname);
					}
				}
			
			}
			
			if($_POST['type'] == 'video' || $_POST['type'] == 'link' || ($newname && is_file($newname))){
				$q = "UPDATE $this->db_table SET deleted=1,modified=NOW(),modified_by=".$_SESSION['Framework']['current_user']['id']." WHERE id=".$_POST['id'];
				$r = $this->framework->db->query($q);
				
				if($r)
					$tmp['content']['status'] = 1;
			}
		}
		
		return $tmp;
	}

	public function saveDocumentData(){
		
		if(!$this->framework->users->userHaveRights('edit'))
			$this->framework->send404();
		
		$tmp = array(
			content_type => 'json',
			content => array(
				status => 0
			)
		);
		
		if(isset($_POST['id']) && $_POST['id'] != ''){
			$d = $_POST;
			foreach($d as $k=>$v){
				$d[$k] = mysql_real_escape_string(trim($v));
			}
			
			$rel = 'NULL';
			if(!empty($d['related_with']))
				$rel = $d['related_with'];
			
			// add new
			if($d['id'] == -1){
				
				if(empty($d['link']))
					return $tmp;
				
				$content = array(
					name => $d['link'],
					size => null,
					type => null
				);
				
				$q = "INSERT INTO $this->db_table (type,content,title,description,related_with,author,added) VALUES (
					'".$d['type']."',
					'".json_encode($content)."',
					IF(LENGTH('".$d['title']."')=0,NULL,'".$d['title']."'),
					IF(LENGTH('".$d['description']."')=0,NULL,'".$d['description']."'),
					".$rel.",
					".$_SESSION['Framework']['current_user']['id'].",
					NOW());";
			}
			// edit data
			else{
				$q = "UPDATE $this->db_table SET
					title=IF(LENGTH('".$d['title']."')=0,NULL,'".$d['title']."'),
					description=IF(LENGTH('".$d['description']."')=0,NULL,'".$d['description']."'),
					related_with=".$rel.",
					modified_by='".$_SESSION['Framework']['current_user']['id']."',
					modified=NOW() WHERE id=".$d['id'];
			}
				
			
			if($this->framework->db->query($q)){
				$tmp['content']['status']='1';
				$tmp['content']['id']=$d['id'];
				if($d['id'] == -1)
					$tmp['content']['id']=mysql_insert_id();
			}
		}
		return $tmp;
	}

	public function getMembersList(){
		
		$tmp['content_type']='json';
		$tmp['content']['status']='0';
		$tmp['content']['data']=array();
		
		if($this->access == 'private' && $_SESSION['Framework']['auth_status'] == 'public')
			$this->framework->send404();
		
		$q = "SELECT id,CONCAT(first_name,' ',last_name) AS name,IF(birth IS NULL AND death IS NULL,'',CONCAT(IF(birth IS NULL,'(...',CONCAT('(',birth)),IF(death IS NULL,')',CONCAT(' - ',death,')')))) AS datum FROM $this->db_members_table WHERE deleted=0 AND status!='hidden' ORDER BY first_name,last_name";
		$result = $this->framework->db->query($q);
		while($row = mysql_fetch_assoc($result)){
			$tmp['content']['data'][] = $row;
		}
		$tmp['content']['status']='1';
		
		return $tmp;
	}
	
	public function getOutput(){
		return $this->output;
	}
	
}
?>