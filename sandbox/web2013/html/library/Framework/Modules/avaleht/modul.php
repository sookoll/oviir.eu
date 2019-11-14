<?php
/*
 * PHP Framework
 * Version 2012.12
 * Mihkel Oviir
 * 
 * Modul Class Framework_Modules_avaleht_modul
 * This modul bases a lot of forum posts.
 */
class Framework_Modules_avaleht_modul {
	
	var $framework;
	var $queryable = true;
	var $access = 'private';
	var $menuitem = 'avaleht';
	protected $db_topics = 'dev_2013_topics';
	protected $db_members_table = 'dev_2012_members';
	protected $news_forum = 3;
	var $output = array();
	
	function __construct($framework){
		$this->framework = $framework;
		$this->output['modul-styles'] = '<link rel="stylesheet" href="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/css/style.css">';
	}
	
	public function front(){
		if($this->access == 'private' && $_SESSION['Framework']['auth_status'] == 'public')
			$this->framework->redirect($this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=login&ref='.urlencode($this->framework->getCurrenturl()));
		else
			$this->output['content'] = $this->buildFrontPage();
	}
	
	private function getDefaultValues(){
		return array(
			h3_1 => _('Uudised ja teated'),
			news => '',
			subforum_link => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=foorum&forum='.$this->news_forum
		);
	}
	
	private function buildFrontPage(){
		$tmp = $this->getDefaultValues();
		
		// get news
		$tmp['news'] = $this->getNews();
		
		return $this->framework->templator->parse(dirname(__FILE__).'/html/modul.html',$tmp);
	}
	
	private function getNews(){
		$news = '';
		$q = "SELECT t1.*,t2.id AS uid,CONCAT(t2.first_name,' ',t2.last_name) AS author_name FROM $this->db_topics t1 LEFT JOIN $this->db_members_table t2 ON (t1.author = t2.id) WHERE (t1.subforum=".$this->news_forum." AND t1.type='topic') AND t1.status!='deleted' ORDER BY t1.added DESC LIMIT 5";
		$result = $this->framework->db->query($q);
		if(mysql_num_rows($result)==0)
			$news .= '<li>'._('Uudised puuduvad').'</li>';
		else{
			while($row = mysql_fetch_assoc($result)){
				foreach($row as $k => $v){
					$row[$k] = stripslashes($v);
				}
				$news .= '<li><h3>'.$row['title'].'</h3>';
				$news .= '<span class="small grey">'.$this->framework->formatDate($row['added']).' '.$row['author_name'].'</span>';
				$news .= '<div class="content">'.$row['content'].'</div></li>';
			}
		}
		return $news;
	}
	
	public function getOutput(){
		return $this->output;
	}
	
}
?>