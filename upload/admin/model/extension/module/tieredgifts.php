<?php 
class ModelExtensionModuletieredgifts extends Model {	

  	public function install() {
	    $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX ."gifts_on_order` (
	                      `order_product_id` int(11))");			   
  	} 
  
  	public function uninstall() {
  		
  	}

}