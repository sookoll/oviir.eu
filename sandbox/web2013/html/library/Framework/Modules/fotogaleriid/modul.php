<?php
/*
 * PHP Framework
 * Version 2012.12
 * Mihkel Oviir
 * 
 * Modul Class Framework_Modules_fotogaleriid_modul
 */
class Framework_Modules_fotogaleriid_modul {
	
	var $framework;
	var $queryable = true;
	var $access = 'private';
	var $menuitem = 'fotogaleriid';
	var $db_albums = 'dev_2012_gallery_album';
	var $db_items = 'dev_2012_gallery_item';
	var $db_users = 'dev_2012_members';
	var $db_tags = 'dev_2012_tags';
	var $thsize = 150;
	var $picsize = 1024;
	var $strlim = array(26,0,23);
	var $output = array();
	
	function __construct($framework){
		$this->framework = $framework;
		
		$this->output['title'] = 'Oviiride fotogaleriid';
		$this->output['modul-styles'] = '<link rel="stylesheet" href="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/gallery.css">';
		$this->output['modul-scripts'] = '<script src="{_libs-path_}/jquery/jquery.lazyload.min.js"></script>';
		$this->output['modul-scripts'] .= '<script src="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/modul.js"></script>';
	}
	
	public function front(){
		if($this->access == 'private' && $_SESSION['Framework']['auth_status'] == 'public')
			$this->framework->redirect($this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=login&ref='.urlencode($this->framework->getCurrenturl()));
		else {
			
			// album page
			if(isset($_GET['album']) && !isset($_GET['item'])){
				if(isset($_GET['edit']))
					if($this->framework->users->userHaveRights('edit'))
						$this->loadEditPage($_GET['album']);
					else
						$this->framework->send404();
				else
					$this->loadAlbumPage($_GET['album']);
			}
			
			// item page
			else if(isset($_GET['album']) && isset($_GET['item'])){
				if(isset($_GET['edit']))
					if($this->framework->users->userHaveRights('edit'))
						$this->loadEditPage($_GET['album']);
					else
						$this->framework->send404();
				else
					$this->loadItemPage($_GET['album'],$_GET['item']);
			}
			
			
			else
				if(isset($_GET['edit']))
					if($this->framework->users->userHaveRights('edit'))
						$this->loadEditPage();
					else
						$this->framework->send404();
				else
					$this->loadGalleryPage();
		}
	}
	
	private function loadGalleryPage(){
		
		$tmp = array(
			h3_1 => _('Teadmiseks'),
			h3_2 => _('Pildialbumid'),
			notes => _('Albumite ja piltide lisamise võimalus tuleb mõne aja pärast.'),
			actions => '',
			content => ''
		);
		
		$q = "SELECT t1.*,CONCAT(t2.first_name,' ',t2.last_name) AS adder FROM $this->db_albums t1 LEFT JOIN $this->db_users t2 ON (t1.author=t2.id) WHERE t1.status='public' OR t1.status='private' ORDER BY t1.sort DESC";
		$result = $this->framework->db->query($q);
		$i = 0;
		while($row = mysql_fetch_assoc($result)){
			
			$t = $row['title'];
			if(empty($row['thumb'])){
				$image = '';
				$iclass = ' no-thumb';
			}else{
				$image = '<img class="lazy" src="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/images/no-thumb.png" data-original="'.$this->framework->conf['librarys']['miuview_api'].'/?request=getimage&album='.$row['album'].'&item='.$row['thumb'].'&size='.$this->thsize.'&mode=square" width="'.$this->thsize.'" heigh="'.$this->thsize.'">';
				$iclass = '';
			}
			$t = strlen($t) > $this->strlim[0]?substr($t,$this->strlim[1],$this->strlim[2]).'...':$t;
			$s = $SESS->view == 'private'?_('Lisas').' '.$row['adder'].' '.date('j.m.Y',strtotime($row['added'])):$row['title'];
			// pictures count
			$q = "SELECT IFNULL(COUNT(item),0) AS count FROM $this->db_items WHERE status!='deleted' AND album='".$row['album']."'";
			$result2 = $this->framework->db->query($q);
			if($row2 = mysql_fetch_assoc($result2))
				$count = $row2['count'];
			$href = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=fotogaleriid&album='.$row['album'];
			$tmp['content'] .= '<li id="album'.$i.'" class="float-left">';
			$tmp['content'] .= '<div class="item'.$iclass.'" style="width:'.$this->thsize.'px;height:'.$this->thsize.'px"><a href="'.$href.'" class="openAlbum" title="'.$s.'">'.$image.'</a><div class="count">'.$count.'</div></div>';
			$tmp['content'] .= '<div class="caption" style="width:'.$this->thsize.'px;"><a href="'.$href.'" class="openAlbum" title="'.$s.'"><b>'.$t.'</b></a></div>';
			$tmp['content'] .= '</li>';
			$i++;
			
		}
		
		if($this->framework->users->userHaveRights('edit')){
			$tmp['actions'] = '<h3>'._('Tegevused').'</h3><a href="'.$this->framework->conf['librarys']['miuview_api'].'/admin.php" class="btn btn-warning" target="_blank">'._('Piltide haldamine').'</a>';
		}
		
		$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/gallery.html',$tmp);
	}

	private function loadAlbumPage($album){
		
		if($a = $this->getAlbum($album)) {
			
			// set ajax handler to this modul
			//$this->output['ajax-handler'] = $this->framework->conf['core']['ajax_handler'].'?'.$this->framework->conf['core']['module_param'].'=fotogaleriid';
			//$this->output['modul'] = 'fotogaleriid';
			
			$tmp = array(
				h3_1 => _('Teadmiseks'),
				notes => _('Eelvaatepildil klikkides avaneb see pilt suurelt. Suure pildi vaates saab navigeerida ka klaviatuuriga. Paremale-Vasakule nooled vahetavad pilti ja ESC klahv sulgeb pildi vaate.'),
				list_url => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=fotogaleriid',
				back_to_list => _('Tagasi galeriisse'),
				title => empty($a['title'])?$album:$a['title'],
				content => ''
			);
			
			//$this->output['modul-scripts'] .= '<script src="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/album.js"></script>';
			
			$q = "SELECT * FROM $this->db_items WHERE album='".$a['album']."' AND status!='deleted' ORDER BY sort ASC";
			$result = $this->framework->db->query($q);
			$i = 0;
			while($row = mysql_fetch_assoc($result)){
				$image = '<img class="lazy" src="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/images/no-thumb.png" data-original="'.$this->framework->conf['librarys']['miuview_api'].'/?request=getimage&album='.$row['album'].'&item='.$row['item'].'&size='.$this->thsize.'&mode=square" width="'.$this->thsize.'" heigh="'.$this->thsize.'">';
				$t = strlen($row['title']) > $this->strlim[0]?substr($row['title'],$this->strlim[1],$this->strlim[2]).'...':$row['title'];
				
				$fi = json_decode($row['metadata'],true);
				if($fi['FILE']['Width']>$fi['FILE']['Height']){
					$w = $this->picsize<=$fi['FILE']['Width']?$this->picsize:$fi['FILE']['Width'];
					$h = $fi['FILE']['Height']*$w/$fi['FILE']['Width'];
				}else{
					$h = $this->picsize<=$fi['FILE']['Height']?$this->picsize:$fi['FILE']['Height'];
					$w = $fi['FILE']['Width']*$h/$fi['FILE']['Height'];
				}
				
				$fi['FILE']['Height'] = $fi['FILE']['Width']>$fi['FILE']['Height']?$fi['FILE']['Height']*$this->picsize/$fi['FILE']['Width']:$this->picsize;
				$fi['FILE']['Width'] = $fi['FILE']['Width']>$fi['FILE']['Height']?$this->picsize:$fi['FILE']['Width']*$this->picsize/$fi['FILE']['Height'];
				
				$href = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=fotogaleriid&album='.$row['album'].'&item='.$row['item'];
				
				$tmp['content'] .= '<li id="item'.$i.'" class="float-left">';
				$tmp['content'] .= '<div class="item" style="width:'.$this->thsize.'px;height:'.$this->thsize.'px"><a name="'.$row['item'].'" href="'.$href.'" class="openItem" title="'.$t.'">'.$image.'</a></div>';
				$tmp['content'] .= '<div class="caption" style="width:'.$this->thsize.'px;"><a href="'.$href.'" class="openItem" title="'.$t.'"><b>'.$t.'</b></a></div>';
				$tmp['content'] .= '</li>';
				$i++;
			}
			
			$this->output['content'] = $this->framework->templator->parse(dirname(__FILE__).'/album.html',$tmp);
			
		}else
			$this->framework->send404();
		
	}
	
	private function getAlbum($a){
		$q = "SELECT * FROM $this->db_albums WHERE album='".$a."'";
		$result = $this->framework->db->query($q);
		$ar = false;
		if($row = mysql_fetch_assoc($result))
			$ar = $row;
		return $ar;
	}
	
	// picture page
	private function loadItemPage($album,$item){
		
		$this->output['modul-scripts'] = '<script src="'.$this->framework->getModulsUrl().'/'.$this->menuitem.'/album.js"></script>';
		
		$tmp = array(
			list_url => $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=fotogaleriid&album='.$album.'#'.$item,
			back_to_list => _('Sulge'),
			mv_api => $this->framework->conf['librarys']['miuview_api']
			
		);
		
		$q = "SELECT a.*,
			(SELECT p.item FROM $this->db_items p WHERE p.sort < a.sort AND p.album='".$album."' ORDER BY sort DESC LIMIT 1) prev_id,
			(SELECT n.item FROM $this->db_items n WHERE n.sort > a.sort AND n.album='".$album."' ORDER BY sort LIMIT 1) next_id,
			(SELECT COUNT(b.item) FROM $this->db_items b WHERE b.album='".$album."') total,
			(SELECT COUNT(c.item) FROM $this->db_items c WHERE c.sort <= a.sort AND c.album='".$album."') count
			FROM $this->db_items AS a WHERE a.album='".$album."' AND a.item='".$item."'";
		
		$result = $this->framework->db->query($q);
		if($pic = mysql_fetch_assoc($result)){
			
			$fi = json_decode($pic['metadata'],true);
			
			// lets find resize factor from original image
			if($fi['FILE']['Width']>$fi['FILE']['Height'])
				$rf = $fi['FILE']['Width']/$this->picsize;
			else 
				$rf = $fi['FILE']['Height']/$this->picsize;
			
			// if picture original is smaller than $this->picsize, then we do not resize
			if($rf<1)
				$rf = 1;
			
			$tmp['width'] = $fi['FILE']['Width']/$rf;
			$tmp['height'] = $fi['FILE']['Height']/$rf;
			$tmp['mv-api'] = $this->framework->conf['librarys']['miuview_api'];
			$tmp['tag_areas'] = $tmp['tag_links'] = $tmp['taggers'] = '';
			$tmp['title'] = empty($pic['title'])?$item:$pic['title'];
			$tmp['description'] = $pic['description'];
			$tmp['album'] = $album;
			$tmp['item'] = $item;
			$tmp['size'] = $this->picsize;
			$tmp['counter'] = $pic['count'].'/'.$pic['total'];
			
			// if first
			if(!empty($pic['prev_id'])){
				$tmp['prev_url'] = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=fotogaleriid&album='.$album.'&item='.$pic['prev_id'];
				$tmp['prev_disabled'] = '';
			}
			else{
				$tmp['prev_url'] = '';
				$tmp['prev_disabled'] = ' disabled';
			}
			
			// if last
			if(!empty($pic['next_id'])){
				$tmp['next_url'] = $this->framework->url().'/?'.$this->framework->conf['core']['module_param'].'=fotogaleriid&album='.$album.'&item='.$pic['next_id'];
				$tmp['next_disabled'] = '';
			}
			else{
				$tmp['next_url'] = '';
				$tmp['next_disabled'] = ' disabled';
			}
			
			$tmp['prev'] = _('Eelmine');
			$tmp['next'] = _('Järgmine');
			
		}
		$this->output['modal-content'] = $this->framework->templator->parse(dirname(__FILE__).'/frame.html',$tmp);
	}
	
	// pictureframe
	public function pictureFrame(){
		
		$tmp = array(
			content_type => 'html',
			content => ''
		);
		
		$q = "SELECT * FROM $this->db_items WHERE album='".$_GET['album']."' AND item='".$_GET['item']."'";
		$result = $this->framework->db->query($q);
		if($pic = mysql_fetch_assoc($result)){
			
			$tmp['mv-api'] = $this->framework->conf['librarys']['miuview_api'];
			$tmp['tag_areas'] = $tmp['tag_links'] = $tmp['taggers'] = '';
			
			$fi = json_decode($pic['metadata'],true);
			
			// lets find resize factor from original image
			if($fi['FILE']['Width']>$fi['FILE']['Height'])
				$rf = $fi['FILE']['Width']/$this->picsize;
			else 
				$rf = $fi['FILE']['Height']/$this->picsize;
			
			// if picture original is smaller than $this->picsize, then we do not resize
			if($rf<1)
				$rf = 1;
			
			$tmp['width'] = $fi['FILE']['Width']/$rf;
			$tmp['height'] = $fi['FILE']['Height']/$rf;
			
			// but if $maxheight is smaller than $h;
			if($_GET['maxheight']<$tmp['height']){
				$rf = $fi['FILE']['Height']/$_GET['maxheight'];
				$tmp['width'] = $fi['FILE']['Width']/$rf;
				$tmp['height'] = $fi['FILE']['Height']/$rf;
			}
				
			$tmp['taggers'] = '<a href="sildista" id="activate" class="float-right"><small>Sildista</small></a><a href="sildista" id="deactivate" class="float-right hide"><small>Lõpeta</small></a>';
			$q = "SELECT t1.*,CONCAT(t2.first_name,' ',t2.last_name) AS user_name FROM $this->db_tags AS t1 LEFT JOIN $this->db_users AS t2 ON (t1.who=t2.id) WHERE t1.modul='fotogaleriid' AND t1.album='".$_GET['album']."' AND t1.item='".$_GET['item']."' AND t1.deleted=0 ORDER BY CONCAT(t2.first_name,' ',t2.last_name) ASC";
			$result = $this->framework->db->query($q);
			$i = 0;
			while($row = mysql_fetch_assoc($result)){
				$tag = json_decode($row['content'],true);
				$tag['x'] = ($tag['xi']<$tag['xii']?$tag['xi']:$tag['xii'])/$rf;
				$tag['y'] = ($tag['yi']<$tag['yii']?$tag['yi']:$tag['yii'])/$rf;
				$w = ($tag['xii']-$tag['xi'])/$rf;
				$h = ($tag['yii']-$tag['yi'])/$rf;
				if(!empty($row['who'])){
					$tmp['tag_areas'] .= '<div id="'.$tag['id'].'" rel="'.$row['who'].'" style="top:'.$tag['y'].'px;left:'.$tag['x'].'px;width:'.$w.'px;height:'.$h.'px;" title="'.$row['user_name'].'"></div>';
					$tmp['tag_links'] .= '<a rel="'.$tag['id'].'" class="black" href="'.$this->framework->url().'/profiil/'.$row['who'].'">'.$row['user_name'].'</a> ';
				}else{
					$tmp['tag_areas'] .= '<div id="'.$tag['id'].'" rel="" style="top:'.$tag['y'].'px;left:'.$tag['x'].'px;width:'.$w.'px;height:'.$h.'px;" title="'.$tag['name'].'"></div>';
					$tmp['tag_links'] .= '<a rel="'.$tag['id'].'" class="gray" href="'.$this->framework->url().'/profiil/">'.$tag['name'].'</a> ';
				}
				$i++;
			}
			
			$tmp['title'] = $pic['title'];
			$tmp['description'] = $pic['description'];
			$tmp['album'] = $_GET['album'];
			$tmp['item'] = $_GET['item'];
			$tmp['size'] = $this->picsize;
			$tmp['resize_factor'] = $rf;
		}
		
		
		return $this->framework->templator->parse(dirname(__FILE__).'/frame.html',$tmp);
		
	}
	
	public function getOutput(){
		return $this->output;
	}
	
}
?>