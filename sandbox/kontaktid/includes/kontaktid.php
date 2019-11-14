<?php 
/*
 * Oviir's family contacts
 * kontaktid class to process data
 * 
 * Creator: Mihkel Oviir
 * 05.2011
 * 
 */

class kontaktid {
	
	var $output=array();
	
	function __construct(){}
	
	public function front(){
		global $sess,$func;
		$tmp = array();
		
		$q = "SELECT ancestor FROM ".TBL_CONTACTS." WHERE deleted = 0 GROUP BY ancestor ORDER BY ancestor ASC";
		$result = $func->makeQuery($q);
		
		$tmp['data_ancestors'] = '';
		while($row = mysql_fetch_assoc($result)){
			$tmp['data_ancestors'] .= '<option value="'.$row['ancestor'].'">'.$row['ancestor'].'</option>';
		}

		$this->output = $tmp;
	}
	
	public function getContacts(){
		global $sess,$func,$page,$rp,$sortname,$sortorder,$filter;
		$tmp = array();
		
		$json=array('page'=>$page,'rows'=>array());
		
		// check if user is logged in
		if($sess->oviir_admin_is_logged_in === true){
			
			$reqData = $this->parseReqData($filter);
			
			$fq = "";
			if($reqData['firstname'] != '')
				$fq.=" AND LOWER(firstname) LIKE LOWER('%".$reqData['firstname']."%')";
			if($reqData['lastname'] != '')
				$fq.=" AND LOWER(lastname) LIKE LOWER('%".$reqData['lastname']."%')";
			if($reqData['address'] != '')
				$fq.=" AND LOWER(address) LIKE LOWER('%".$reqData['address']."%')";
			if($reqData['ancestor'] != '*')
				$fq.=" AND ancestor LIKE '".$reqData['ancestor']."'";
			if($reqData['active'] == 'ei')
				$fq.=" AND (active = 0 OR (email LIKE '' AND address LIKE ''))";
			elseif($reqData['active'] == 'email')
				$fq.=" AND (active = 1 AND email NOT LIKE '')";
			elseif($reqData['active'] == 'post')
				$fq.=" AND (active = 1 AND email LIKE '' AND address NOT LIKE '')";
			
			$q = "SELECT * FROM ".TBL_CONTACTS." WHERE deleted = 0".$fq." ORDER BY ".$sortname." ".$sortorder;
			
			$result = $func->makeQuery($q);
			$json['total'] = mysql_num_rows($result);
			
			$i = 0;
			while($row = mysql_fetch_assoc($result)){
				$invitation = 'ei';
				if($row['active'] == 1){
					if($row['email'] != '')
						$invitation = 'e-post';
					elseif($row['address'] != '')
						$invitation = 'post';
				}
				
				$json['rows'][$i]['id'] = $row['id'];
				$json['rows'][$i]['cell'] = array($i+1,trim($row['firstname']),trim($row['lastname']),trim($row['address']),trim($row['email']),trim($row['phone']),trim($row['ancestor']),$invitation,'<a href="javascript:void(0);" onclick="doDetail(\''.$row['id'].'\');return false;" title="Muudetud: '.$row['changed'].'">muuda</a>','<a href="javascript:void(0);" onclick="doDelete(\''.$row['id'].'\');return false;">kustuta</a>');
				$i++;
			}
		}

        $tmp['content_type'] = 'json';
		$tmp['content'] = $json;
		$this->output = $tmp;
	}

	// delete contact
	public function delContact(){
		global $func,$sess,$id;
		$tmp = array();
		$tmp['content']['status']=0;
		
		if($sess->oviir_admin_is_logged_in === true){
			$q = "UPDATE ".TBL_CONTACTS." SET deleted = 1, changed = NOW() WHERE id = ".$id;
			$result = $func->makeQuery($q);
			if($result)
				$tmp['content']['status']=1;
		}
		$tmp['content_type'] = 'json';
		$this->output = $tmp;
	}
	
	// save data
	public function saveContact() {
		global $func,$sess,$data;
		$tmp = array();
		
		if($sess->oviir_admin_is_logged_in === true){
			$reqData = $this->parseReqData($data);
			
			if($reqData['id'] != -1)
				$q = "UPDATE ".TBL_CONTACTS." SET firstname='".$reqData['firstname']."',lastname='".$reqData['lastname']."',address='".$reqData['address']."',email='".$reqData['email']."',phone='".$reqData['phone']."',ancestor='".$reqData['ancestor']."',active=".$reqData['active'].",changed=NOW() WHERE id = ".$reqData['id'];
			else
				$q = "INSERT INTO ".TBL_CONTACTS." (firstname,lastname,address,email,phone,ancestor,active,changed) VALUES ('".$reqData['firstname']."','".$reqData['lastname']."','".$reqData['address']."','".$reqData['email']."','".$reqData['phone']."','".$reqData['ancestor']."',".$reqData['active'].",NOW())";
				
			$result = $func->makeQuery($q);
			if($result)
				$tmp['content']=array('status'=>1);
			else
				$tmp['content']=array('status'=>0);
		}
		$tmp['content_type'] = 'json';
		$this->output = $tmp;
	}
    
    // get all data for page printing and return printpage
    public function getPrintContent(){
    	global $func,$sess,$data;

        $tmp = array();
        $tmp['content'] = '';
        
        // check if user is logged in
		if($sess->oviir_admin_is_logged_in === true){
			
			$reqData = $this->parseReqData($data);
			
			$fq = "";
			if($reqData['firstname'] != '')
				$fq.=" AND LOWER(firstname) LIKE LOWER('%".$reqData['firstname']."%')";
			if($reqData['lastname'] != '')
				$fq.=" AND LOWER(lastname) LIKE LOWER('%".$reqData['lastname']."%')";
			if($reqData['address'] != '')
				$fq.=" AND LOWER(address) LIKE LOWER('%".$reqData['address']."%')";
			if($reqData['ancestor'] != '*')
				$fq.=" AND ancestor LIKE '".$reqData['ancestor']."'";
			if($reqData['active'] == 'ei')
				$fq.=" AND (active = 0 OR (email LIKE '' AND address LIKE ''))";
			elseif($reqData['active'] == 'email')
				$fq.=" AND (active = 1 AND email NOT LIKE '')";
			elseif($reqData['active'] == 'post')
				$fq.=" AND (active = 1 AND email LIKE '' AND address NOT LIKE '')";
			
			$q = "SELECT * FROM ".TBL_CONTACTS." WHERE deleted = 0".$fq." ORDER BY ancestor ASC, address ASC, lastname ASC";
			
			$result = $func->makeQuery($q);
			
			$htmlarr = array();
			$i = 0;
			while($row = mysql_fetch_assoc($result)){
				$invitation = 'ei';
					if($row['active'] == 1){
						if($row['email'] != '')
							$invitation = 'e-mail';
						elseif($row['address'] != '')
							$invitation = 'post';
					}
				$htmlarr[$i] = array('firstname'=>trim($row['firstname']),'lastname'=>trim($row['lastname']),'address'=>trim($row['address']),'email'=>trim($row['email']),'phone'=>trim($row['phone']),'ancestor'=>trim($row['ancestor']),'active'=>$invitation);
				$i++;
			}
			$htmlarr = $this->arrayBuild($htmlarr,'ancestor');
			
			$html['data_content'] = '';
			foreach($htmlarr as $ancestor=>$childrens){
				$html['data_content'].='<h2>'.$ancestor.'</h2><h3>Järeltulijad</h3><table>';
				foreach($childrens as $children){
					$html['data_content'].='<tr><td><b>'.$children['firstname'].' '.$children['lastname'].'</b></td><td>'.$children['address'].'</td><td>'.$children['email'].'</td><td>'.$children['phone'].'</td></tr>';
				}
				$html['data_content'].='</table>';
			}
			
			$tmp['content'] = $func->replace_tags(PATH_TMPL.TEMPLATE.'/html/print.html',$html);
		}
        
		$tmp['content_type'] = 'html';
		$this->output = $tmp;
    }
    
    private function arrayBuild($arr,$key){
    	$newarr = array();
    	foreach($arr as $line){
    		$newarr[$line[$key]][]=$line;
    	}
    	return $newarr;
    }
    
    private function parseReqData($data){
    	$tmp = array();
    	$fp = explode('&',$data);
		foreach($fp as $param){
			list($k,$v)=explode('=',$param);
			$tmp[$k] = urldecode(trim($v));
		}
		return $tmp;
    }

	// return output data
	function getResult() {
		return $this->output;
	}
}

?>