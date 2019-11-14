<?php
/*
 * PHP Framework
 * Version 2012.12
 * Mihkel Oviir
 * 
 * Modul Class Framework_Modules_foorum_modul
 */
class Framework_Modules_foorum_modul {
	
	protected $framework;
	public $queryable = true;
	protected $access = 'private';
	public $menuitem = 'foorum';
	protected $db_topics = 'dev_2013_topics';
	protected $db_subforums = 'dev_2013_subforums';
	protected $db_members_table = 'dev_2012_members';
	protected $categories;
	var $output = array();
	
	function __construct($framework){
		$this->framework = $framework;
		$this->output['title'] = _('Oviirid käravad');
		$this->output['ajax-handler'] = $this->framework->conf['core']['ajax_handler'].'?'.$this->framework->conf['core']['module_param'].'=foorum';
		
		if($this->framework->users->userHaveRights('edit'))
			$this->output['modul-styles'] .= '<link type="text/css" rel="stylesheet" href="/libs/jquery/jquery-selectBox/jquery.selectBox.css" />';
		$this->output['modul-styles'] .= '<link rel="stylesheet" href="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/css/style.css">';
		if($this->framework->users->userHaveRights('edit')){
			$this->output['modul-scripts'] .= '<script type="text/javascript" src="/libs/jquery/jquery-selectBox/jquery.selectBox.js"></script>';
			$this->output['modul-scripts'] .= '<script type="text/javascript" src="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/modul.js"></script>';
		}
		
		$this->categories = array(
			0 => _('Uudised ja teated'),
			1 => _('Suguvõsa'),
			2 => _('Veebileht')
		);
	}
	
	public function front(){
		if($this->access == 'private' && $_SESSION['Framework']['auth_status'] == 'public')
			$this->framework->redirect($this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=login&ref='.urlencode($this->framework->getCurrenturl()));
		else{
			if(isset($_GET['add'])){
				switch($_GET['add']){
					case 'subforum':
						if(array_key_exists($_GET['cat'],$this->categories))
							$this->addSubForum($_GET['cat']);
					break;
					case 'topic':
						if(isset($_GET['forum']))
							$this->loadTopicEditPage('topic',$_GET['forum']);
					break;
					case 'answer':
						if(isset($_GET['topic']))
							$this->loadTopicEditPage('answer',$_GET['topic']);
					break;
				}
			}
			else if(isset($_GET['edit']))
				$this->loadTopicEditPage('edit',$_GET['edit']);
			else if(isset($_GET['delete']))
				$this->deleteTopic($_GET['delete']);
			else if(isset($_GET['forum']) && !empty($_GET['forum']))
				$this->loadForumPage($_GET['forum']);
			else if(isset($_GET['topic']) && !empty($_GET['topic']))
				$this->loadTopicPage($_GET['topic']);
			else
				$this->loadFrontPage();	
		}
	}

	private function getDefaultValues(){
		return array(
			h3_1 => _('Teadmiseks'),
			h3_2 => _('Valikud'),
			h3_3 => _('Foorumid'),
			h3_4 => _('Toimingud'),
			actions => '',
			table => '',
			pages => '',
			reset_href => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=foorum',
			// adding
			loading => $this->framework->getTemplateUrl().'/images/loading.gif',
			wait => _('Oota, laeb...'),
			cancel => _('Tühista'),
			cancel_href => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=foorum',
			save => _('Salvesta'),
			back_to_list => _('Foorumid'),
			nav => _('Tagasi')
		);
	}

	private function loadFrontPage(){
		
		$tmp = $this->getDefaultValues();
		$tmp['opts_cat'] = $tmp['hiddenputs'] = '';
		foreach($this->categories as $k => $v){
			
			$tmp['table'] .= '<table rel="'.$k.'">';
			$tmp['table'] .= '<tr><th colspan="5"><h3>'.$v.'</h3></th></tr>';
			$opts = '';
			$subf = $this->getSubForums($k);
			if(count($subf)>0){
				foreach($subf as $f){
					$tc = $this->getTopicsCountInSubforum($f['id']);
					$pc = $this->getPostsCountInSubforum($f['id']);
					$lastpost = $this->getLastPostInSubforum($f['id']);
					$tmp['table'] .= '<tr rel="'.$f['id'].'"><td style="width:40px;text-align:center"><img src="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/images/envelope2.png"></td><td><a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=foorum&forum='.$f['id'].'">'.$f['title'].'</a></td><td style="width:60px;text-align:center" class="small">'.$tc.'</td><td style="width:60px;text-align:center" class="small">'.$pc.'</td><td style="width:150px">'.$lastpost.'</td></tr>';
					$opts .= '<option value="'.$f['id'].'">'.$f['title'].'</option>';
				}
			}else
				$tmp['table'] .= '<tr><td colspan="5"><div class="grey">'._('Alamfoorumid puuduvad').'</div></td></tr>';
			$tmp['table'] .= '</table>';
			
			if($this->framework->users->userHaveRights('edit')){
				$tmp['opts_cat'] .= '<option value="'.$k.'">'.$v.'</option>';
				$tmp['hiddenputs'] .= '<select style="display:none" name="opts_'.$k.'">'.$opts.'</select>';
			}
			
			
		}
		
		if($this->framework->users->userHaveRights('edit')){
			$tmp['admin_btn_text'] = _('Halda alamfoorumeid');
			$tmp['opts_cat_text'] = _('Vali kategooria');
			$tmp['opts_forum_text'] = _('Vali alamfoorum');
			$tmp['ph_title'] = _('Alamfoorumi pealkiri');
			$tmp['delete'] = _('Kustuta');
			
			$tmp['actions'] = $this->framework->templator->parse(dirname(__FILE__).'/html/admin_subforums.html',$tmp);
		}
		
		$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/html/foorum.html',$tmp);
		
	}

	private function getTopicsCountInSubforum($forum){
		$c = 0;
		$q = "SELECT COUNT(id) AS count FROM $this->db_topics WHERE type='topic' AND subforum=".$forum." AND status!='deleted'";
		if($r = mysql_fetch_assoc($this->framework->db->query($q))){
			$c = $r['count'];
		}
		return $c;
	}
	
	private function getPostsCountInSubforum($forum){
		$c = 0;
		$q = "SELECT COUNT(id) AS count FROM $this->db_topics WHERE subforum=".$forum." AND status!='deleted'";
		if($r = mysql_fetch_assoc($this->framework->db->query($q))){
			$c = $r['count'];
		}
		return $c;
	}

	private function getLastPostInSubforum($forum){
		$last_author = $last_time = '';
		$q = "SELECT t1.added,CONCAT(t2.first_name,' ',t2.last_name) AS author_name FROM $this->db_topics t1 LEFT JOIN $this->db_members_table t2 ON (t1.author = t2.id) WHERE t1.subforum=".$forum." AND t1.status!='deleted' ORDER BY t1.added DESC LIMIT 1";
		if($r = mysql_fetch_assoc($this->framework->db->query($q))){
			$last_author = $r['author_name'];
			$last_time = $this->framework->formatTime($r['added']);
		}
		return '<em class="small grey">'.$last_author.'<br>'.$last_time.'</em>';
	}

	private function getSubForums($cat){
		$arr = array();
			
		$q = "SELECT * FROM $this->db_subforums WHERE deleted=0 AND category=".$cat." ORDER BY added ASC";
		$result = $this->framework->db->query($q);
		while($row = mysql_fetch_assoc($result)){
			foreach($row as $k => $v){
				$row[$k] = stripslashes($v);
			}
			$arr[] = $row;
			$j++;
		}
		return $arr;
	}
	
	public function editSubForum(){
		if(!$this->framework->users->userHaveRights('edit'))
			$this->framework->send404();
		
		$tmp = array(
			content_type => 'json',
			content => array(
				status => 0
			)
		);
		
		if(($_POST['action'] == 'edit' && array_key_exists($_POST['category'],$this->categories) && strlen($_POST['title']) > 0) || ($_POST['action'] == 'delete' && strlen($_POST['forum']) > 0)){
			$d = $_POST;
			foreach($d as $k=>$v){
				$d[$k] = mysql_real_escape_string(trim($v));
			}
			if($_POST['action'] == 'delete')
				$q = "UPDATE $this->db_subforums SET deleted=1 WHERE id=".$d['forum'];
			else{
				if(strlen($d['forum'])>0){
					$q = "UPDATE $this->db_subforums SET title='".$d['title']."' WHERE id=".$d['forum'];
				}else{
					$q = "INSERT INTO $this->db_subforums (category,title,added) VALUES (".$d['category'].",'".$d['title']."',NOW())";
				}
			}
			
			if($this->framework->db->query($q)){
				$tmp['content']['status']='1';
			}
			
		}
		return $tmp;
	}
	
	private function loadForumPage($id){
		$tmp = $this->getDefaultValues();
		$tmp['th_topic_title'] = _('Teema');
		$tmp['th_replies_count'] = _('Vastuseid');
		$tmp['th_views_count'] = _('Vaatamisi');
		$tmp['th_last_post'] = _('Viimane postitus');
		
		$q = "SELECT * FROM $this->db_subforums WHERE id=".$id;
		$result = $this->framework->db->query($q);
		if($row = mysql_fetch_assoc($result)){
			$tmp['h3_3'] = $row['title'];
		}
		
		$q = "SELECT t1.*,CONCAT(t3.first_name,' ',t3.last_name) AS author_name FROM $this->db_topics t1 LEFT JOIN $this->db_members_table t3 ON (t1.author = t3.id) WHERE t1.status!='deleted' AND t1.type='topic' AND t1.subforum=".$id." ORDER BY t1.sticky DESC, t1.lastpost DESC";
		$result = $this->framework->db->query($q);
		if(mysql_num_rows($result)==0)
			$tmp['table'] .= '<tr><td></td><td colspan="3"><div class="grey">'._('Teemad puuduvad').'</div></td></tr>';
		else{
			while($f = mysql_fetch_assoc($result)){
				foreach($f as $k => $v){
					$f[$k] = stripslashes($v);
				}

				// get answers count
				$q = "SELECT COUNT(id) AS count FROM $this->db_topics WHERE type='answer' AND topic=".$f['id']." AND status!='deleted'";
				if($r = mysql_fetch_assoc($this->framework->db->query($q))){
					$answers = $r['count'];
				}
				
				// get last answer
				if($answers>0){
					$q = "SELECT t1.added,CONCAT(t2.first_name,' ',t2.last_name) AS author_name FROM $this->db_topics t1 LEFT JOIN $this->db_members_table t2 ON (t1.author = t2.id) WHERE t1.type='answer' AND t1.topic=".$f['id']." AND t1.status!='deleted' ORDER BY t1.added DESC LIMIT 1";
					if($r = mysql_fetch_assoc($this->framework->db->query($q))){
						$last_author = $r['author_name'];
						$last_time = $this->framework->formatTime($r['added']);
					}
				}else{
					$last_author = $f['author_name'];
					$last_time = $this->framework->formatTime($f['added']);
				}
				
				$tmp['table'] .= '<tr rel="'.$f['id'].'"><td style="width:40px;text-align:center"><img src="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/images/envelope.png"></td>';
				$tmp['table'] .= '<td><a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=foorum&topic='.$f['id'].'">'.$f['title'].'</a></td>';
				$tmp['table'] .= '<td style="width:60px;text-align:center">'.$answers.'</td><td style="width:150px"><em class="small grey">'.$last_author.'<br>'.$last_time.'</em></td></tr>';
			}
		}
		
		// actions
		if($this->framework->users->userHaveRights('comment')){
			$tmp['actions'] = '<h3>'.$tmp['h3_4'].'</h3><a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=foorum&add=topic&forum='.$id.'" class="btn btn-success">'._('Uus teema').'</a>';
		}
		
		$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/html/subforum.html',$tmp);
	}

	// @forum: case answer $forum is topic id, case topic, $forum is subforum id
	private function loadTopicEditPage($type,$forum,$id=-1){
		if(!$this->framework->users->userHaveRights('comment'))
			$this->framework->send404();
			
		$tmp = $this->getDefaultValues();
		$tmp['texts'] = '';
		$tmp['cancel_href'] = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=foorum&forum='.$forum;
		$tmp['h3_3'] = _('Uus teema');
		$tmp['ph_title'] = _('Teema pealkiri');
		$tmp['title'] = '';
		$tmp['content'] = '';
		$tmp['forum'] = $forum;
		$tmp['id'] = $id;
		$tmp['type'] = $type;
		$tmp['checkboxes'] = '';
		$tmp['title_disabled'] = '';
		
		switch($tmp['type']){
			case 'topic':// add topic
				$tmp['checkboxes'] = '<label><input type="checkbox" name="sticky"> '._('Oluline teema, püsib teemade nimekirja alguses').'</label>';
				$tmp['checkboxes'] .= '<br><label><input type="checkbox" name="status"> '._('Teema suletud, vastata ei saa').'</label>';
				$tmp['checkboxes'] .= '<input type="hidden" name="forum" value="'.$tmp['forum'].'">';
			break;
			case 'answer':// add answer
				$q = "SELECT * FROM $this->db_topics WHERE status='published' AND id=".$forum;
				$result = $this->framework->db->query($q);
				if($row = mysql_fetch_assoc($result)){
					foreach($row as $k => $v){
						$row[$k] = stripslashes($v);
					}
					$tmp['h3_3'] = $row['title'];
					$tmp['checkboxes'] = '<input type="hidden" name="topic" value="'.$forum.'">';
					$tmp['checkboxes'] .= '<input type="hidden" name="forum" value="'.$row['subforum'].'">';
					$tmp['title_disabled'] = ' disabled="disabled"';
					$tmp['title'] = _('Vastus').': '.$row['title'];
					$tmp['cancel_href'] = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=foorum&topic='.$forum;
				}else
					$this->framework->send404();
			break;
			case 'edit':// edit post
				$tmp['h3_3'] = _('Muuda postitust');
				$q = "SELECT * FROM $this->db_topics WHERE id=".$forum;
				$result = $this->framework->db->query($q);
				if($row = mysql_fetch_assoc($result)){
					foreach($row as $k => $v){
						$row[$k] = stripslashes($v);
					}
					$tmp = array_merge($tmp,$row);
					if($row['author'] == $_SESSION['Framework']['current_user']['id'] || $this->framework->users->userHaveRights('admin')){
						// topic
						if($tmp['type'] == 'topic'){
							$sticky = $status = '';
							if($row['sticky'] == 1)
								$sticky = 'checked="checked"';
							if($row['status'] == 'closed')
								$status = 'checked="checked"';
							$tmp['checkboxes'] = '<label><input type="checkbox" name="sticky"'.$sticky.'> '._('Oluline teema, püsib teemade nimekirja alguses').'</label>';
							$tmp['checkboxes'] .= '<br><label><input type="checkbox" name="status"'.$status.'> '._('Teema suletud, vastata ei saa').'</label>';
							$tmp['checkboxes'] .= '<input type="hidden" name="forum" value="'.$row['subforum'].'">';
							$tmp['cancel_href'] = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=foorum&topic='.$row['id'];
						}
						// answer
						else{
							$tmp['title_disabled'] = ' disabled="disabled"';
							$tmp['cancel_href'] = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=foorum&topic='.$row['topic'];
							$tmp['checkboxes'] = '<input type="hidden" name="topic" value="'.$row['topic'].'">';
							$tmp['checkboxes'] .= '<input type="hidden" name="forum" value="'.$row['subforum'].'">';
						}
					}else
						$this->framework->send404();
						
						
				}else
					$this->framework->send404();
			break;
		}
		
		$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/html/edit_topic.html',$tmp);
		$this->output['modul-scripts'] = '<script type="text/javascript" src="/libs/nicEdit/nicEdit.js"></script>';
		$this->output['modul-scripts'] .= '<script type="text/javascript" src="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/topic.js"></script>';
	}
	
	public function saveTopic(){
		if(!$this->framework->users->userHaveRights('comment'))
			$this->framework->send404();
		
		$tmp = array(
			content_type => 'json',
			content => array(
				status => 0
			)
		);
		
		if(strlen($_POST['title']) > 0 && strlen($_POST['content']) > 0 && isset($_POST['forum']) && isset($_POST['id']) && ($_POST['type'] == 'topic' || ($_POST['type'] == 'answer' && isset($_POST['topic'])))){
			$d = $_POST;
			foreach($d as $k=>$v){
				$d[$k] = mysql_real_escape_string(trim($v));
			}
			
			if(!isset($d['sticky'])){
				$d['sticky'] = 'NULL';
			}
			
			if(!isset($d['status'])){
				$d['status'] = 'published';
			}
			
			// if not answer
			$last = 'NULL';
			if(!isset($d['topic']) || $d['type'] == 'topic'){
				$d['topic'] = 'NULL';
				$last = 'NOW()';
			}
			
			// edit
			if($_POST['id'] > 0){
				$q = "UPDATE $this->db_topics SET
					sticky=".$d['sticky'].",
					title='".$d['title']."',
					content='".$d['content']."',
					status='".$d['status']."',
					modified=NOW() WHERE id=".$d['id'];
			}
			// insert
			else{
				$q = "INSERT INTO $this->db_topics (subforum,type,topic,sticky,author,title,content,status,added,lastpost) VALUES (
					".$d['forum'].",
					'".$d['type']."',
					".$d['topic'].",
					".$d['sticky'].",
					".$_SESSION['Framework']['current_user']['id'].",
					'".$d['title']."',
					'".$d['content']."',
					'".$d['status']."',
					NOW(),".$last.")";
				
			}
			
			if($this->framework->db->query($q)){
				$tmp['content']['status'] = '1';
				// edit
				if($d['id'] > 0){
					if($d['type'] == 'topic')
						$tmp['content']['href'] = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=foorum&topic='.$d['id'].'#'.$d['id'];
					else
						$tmp['content']['href'] = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=foorum&topic='.$d['topic'].'#'.$d['id'];
				}else{
					if($d['type'] == 'topic'){
						$tmp['content']['href'] = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=foorum&topic='.mysql_insert_id().'#'.mysql_insert_id();
					}else{
						$tmp['content']['href'] = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=foorum&topic='.$d['topic'].'#'.mysql_insert_id();
						// change lastpost time in topic row
						$q = "UPDATE $this->db_topics SET lastpost=NOW() WHERE id=".$d['topic'];
						$this->framework->db->query($q);
					}
				}
					
			}
			
		}
		return $tmp;
	}

	private function loadTopicPage($topic){
		$tmp = $this->getDefaultValues();
		
		$q = "SELECT t1.*,t2.id AS uid,CONCAT(t2.first_name,' ',t2.last_name) AS author_name,t3.title AS forum FROM $this->db_topics t1 LEFT JOIN $this->db_members_table t2 ON (t1.author = t2.id) LEFT JOIN $this->db_subforums t3 ON (t1.subforum = t3.id) WHERE ((t1.id=".$topic." AND t1.type='topic') OR (t1.type='answer' AND t1.topic=".$topic.")) AND t1.status!='deleted' ORDER BY added";
		$result = $this->framework->db->query($q);
		$i = 1;
		$closed = false;
		$max = mysql_num_rows($result);
		if($max == 0)
			$this->framework->send404();
		while($row = mysql_fetch_assoc($result)){
			foreach($row as $k => $v){
				$row[$k] = stripslashes($v);
			}
			
			if($i == 1){
				if($row['status'] == 'closed')
					$closed = true;
				$tmp['h3_3'] = $row['title'];
				$tmp['cancel_href2'] = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=foorum&forum='.$row['subforum'];
				$tmp['status'] = $row['status'];
				$tmp['forum'] = $row['forum'];
			}	
			
			$tmp['table'] .= '<tr class="separator"><td colspan="2"><div style="height:4px"></div></td></tr>';
			$tmp['table'] .= '<tr class="header"><td colspan="2"><a name="'.$row['id'].'">&nbsp;</a>';
			if(($row['author'] == $_SESSION['Framework']['current_user']['id'] && $closed === false) || $this->framework->users->userHaveRights('admin')){
				$tmp['table'] .= '<a class="small grey float-right" href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=foorum&edit='.$row['id'].'">'._('muuda').'</a>';
			}
				
			$tmp['table'] .= '</td></tr>';
			$tmp['table'] .= '<tr><td valign="top" style="width:140px"><em class="small grey">'.$row['author_name'].'<br>'.$this->framework->formatTime($row['added']).'</em></td>';
			$tmp['table'] .= '<td>'.$row['content'].'</td></tr>';
			
			$i++;
		}
		// actions
		if($tmp['status'] == 'closed')
			$tmp['actions'] = '<a href="#" class="btn">'._('Teema suletud').'</a>';
		else if($this->framework->users->userHaveRights('comment') ){
			$tmp['actions'] = '<a href="'.$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=foorum&add=answer&topic='.$topic.'" class="btn btn-success">'._('Vasta').'</a>';
		}
		$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/html/topic.html',$tmp);
	}
	
	public function getOutput(){
		return $this->output;
	}
	
}
?>