<?php
/*
 * PHP Framework
 * Version 2012.12
 * Mihkel Oviir
 * 
 * Modul Class Framework_Modules_foorum_modul
 */
class Framework_Modules_kontakt_modul {
	
	protected $framework;
	public $queryable = true;
	protected $access = 'public';
	public $menuitem = 'kontakt';
	protected $db_topics = 'dev_2013_topics';
	protected $news_forum = 4;
	var $output = array();
	
	function __construct($framework){
		$this->framework = $framework;
		$this->output['title'] = _('Kontaktivorm');
		$this->output['ajax-handler'] = $this->framework->conf['core']['ajax_handler'].'?'.$this->framework->conf['core']['module_param'].'=kontakt';
		
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

	public function getOutput(){
		return $this->output;
	}
	
}
?>