<?php
class ControllerExtensionfbecommevnt extends Controller {
	private $modpath = 'extension/fbecommevnt'; 
 	private $modname = 'fbecommevnt';
  	private $modssl = 'SSL';
   	private $langid = 0;
	private $storeid = 0;
	private $custgrpid = 0;
	
	public function __construct($registry) {
		parent::__construct($registry);
 		$this->langid = (int)$this->config->get('config_language_id');
		$this->storeid = (int)$this->config->get('config_store_id');
		$this->custgrpid = (int)$this->config->get('config_customer_group_id');
		
		if(substr(VERSION,0,3)>='3.0' || substr(VERSION,0,3)=='2.3' || substr(VERSION,0,3)=='2.2') { 
  			$this->modssl = true;
 		} 
 		if(substr(VERSION,0,3)>='3.0') { 
			$this->modname = 'module_fbecommevnt';
		} 
  	}	
	public function getcache() {
		$this->load->model($this->modpath);
		if($this->model_extension_fbecommevnt->getmodstatus()) {
 			$json['langdata'] = $this->model_extension_fbecommevnt->getlang();
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}
	}
	public function trackevent() {
		$this->load->model($this->modpath);
		$this->load->model('catalog/product');
 		if(isset($this->request->post['product_id'])) { 
 			$product_data = array();
  			$product_info = $this->model_catalog_product->getProduct($this->request->post['product_id']);
 			
			if ($product_info) { 
 				$price = $product_info['special'] ? $product_info['special'] : $product_info['price'];
 				$price = $this->tax->calculate($price , $product_info['tax_class_id'], $this->config->get('config_tax')); 
 			
				$product_data = array(
					"product_catalog_id" => $this->model_extension_fbecommevnt->getFBCATALOGID(),
					"content_ids" => array($product_info['product_id']),
					"content_type" => 'product',
					"content_name" => htmlspecialchars_decode($product_info['name']),
					"content_category" => $this->model_extension_fbecommevnt->getProdCatName($product_info['product_id']),
					"value" => $this->model_extension_fbecommevnt->getcurval($price),
					"currency" => $this->session->data['currency'], 
				); 
				
				$event_data['items'] = $product_data;
 				
				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($event_data));
			} 
		} 
	}
}