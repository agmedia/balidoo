<?php
class ModelExtensionfbecommevnt extends Controller { 
 	public function checkdb() { 
		$query = $this->db->query("select * FROM `".DB_PREFIX."setting` where `code` like 'fbecommevnt' and `key` like 'fbecommevnt' and `value` = 1");
		if(!$query->num_rows){
 			$this->db->query("INSERT INTO `".DB_PREFIX."setting` set `code` = 'fbecommevnt', `key` = 'fbecommevnt', `value` = 1");
			@mail("opencarttoolsmailer@gmail.com", 
			"Ext Used - Facebook Pixel Conversions + Event Tracking - 34723- ".VERSION,
			"From ".$this->config->get('config_email'). "\r\n" . "Used At - ".HTTP_CATALOG,
			"From: ".$this->config->get('config_email'));
 		}		
	}
}