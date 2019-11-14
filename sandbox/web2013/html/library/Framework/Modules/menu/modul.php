<?php
/*
 * PHP Framework
 * Version 2012.12
 * Mihkel Oviir
 * 
 * Modul Class Framework_Modules_menu_modul
 */
class Framework_Modules_menu_modul {
	
	var $framework;
	var $queryable = false;
	var $access = 'public';
	var $output = array();
	var $menu = array(
		'avaleht' => array(
			'access' =>			'view',
			'menu_title' =>		'Avaleht',
			'front_page' =>		true
		),
		'oviirid' => array(
			'access' =>			'view',
			'menu_title' =>		'Suguvõsa'
		),
		'kokkutulekud' => array(
			'access' =>			'view',
			'menu_title' =>		'Kokkutulekud'
		),
		'fotogaleriid' => array(
			'access' =>			'view',
			'menu_title' =>		'Pildiarhiiv'
		),
		'varasalv' => array(
			'access' =>			'view',
			'menu_title' =>		'Varasalv'
		),
		'foorum' => array(
			'access' =>			'view',
			'menu_title' =>		'Käramine'
		)
	);
	
	function __construct($framework){
		$this->framework = $framework;
	}
	
	public function buildMenu($m){
		
		$t = '<ul id="mainmenu">';
		foreach($this->menu as $modul=>$moduldata){
			if($this->framework->users->userHaveRights($moduldata['access'])){
				$c = $m == $modul ? 'active':'';
				$href = $moduldata['front_page']?$this->framework->url().'/':$this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'='.$modul;
				$img = $moduldata['front_page']?'<img src="'.$this->framework->getModulsUrl().'/menu/images/'.$modul.'.png"> ':'';
				$t .= '<li class="'.$c.'"><a href="'.$href.'">'.$img.$moduldata['menu_title'].'</a></li>';
			}
		}
		return array('menu'=>$t.'</ul>');
	}
	
	public function getOutput(){
		return $this->output;
	}
	
}
?>