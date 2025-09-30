<?php
if (version_compare(VERSION,'3.0.0.0','>=' )) {
	define('TEMPLATE_FOLDER', 'oc3');
	define('EXTENSION_BASE', 'marketplace/extension');
	define('TOKEN_NAME', 'user_token');
	define('TEMPLATE_EXTN', '');
	define('EXTN_ROUTE', 'extension/hbseo');
}else if (version_compare(VERSION,'2.2.0.0','<=' )) {
	define('TEMPLATE_FOLDER', 'oc2');
	define('EXTENSION_BASE', 'extension/hbseo');
	define('TOKEN_NAME', 'token');
	define('TEMPLATE_EXTN', '.tpl');
	define('EXTN_ROUTE', 'hbseo');
}else{
	define('TEMPLATE_FOLDER', 'oc2');
	define('EXTENSION_BASE', 'extension/extension');
	define('TOKEN_NAME', 'token');
	define('TEMPLATE_EXTN', '');
	define('EXTN_ROUTE', 'extension/hbseo');
}
define('EXTN_VERSION', '5.1.0'); 
class ControllerExtensionHbseoHbSeourl extends Controller {
	
	private $error = array(); 
	
	public function index() {   
		$data['extension_version'] = EXTN_VERSION;
		
		if (isset($this->request->get['store_id'])){
			$data['store_id'] = (int)$this->request->get['store_id'];
		}else{
			$data['store_id'] = 0;
		}
		
		$this->load->language(EXTN_ROUTE.'/hb_seourl');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/hbseo/hb_seourl');
		$this->load->model('setting/setting');
		$this->load->model('setting/store');

		$data['stores'] = $this->model_setting_store->getStores();
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('hb_seourl', $this->request->post, $this->request->get['store_id']);	
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link(EXTN_ROUTE.'/hb_seourl', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME].'&store_id='.$data['store_id'], true));
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		
		$text_strings = array(
				'heading_title','text_extension',
				'tab_dashboard','tab_setting','tab_routes','tab_raw',
				'button_save','button_cancel'
		);
		
		foreach ($text_strings as $text) {
			$data[$text] = $this->language->get($text);
		}
	
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME], true)
   		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link(EXTENSION_BASE, TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME] . '&type=hbseo', true)
		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link(EXTN_ROUTE.'/hb_seourl', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME].'&store_id='.$data['store_id'], true)
   		);
		
		$data['action'] = $this->url->link(EXTN_ROUTE.'/hb_seourl', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME].'&store_id='.$data['store_id'], true);
		
		$data['cancel'] = $this->url->link(EXTENSION_BASE, TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME] . '&type=hbseo', true);
		$data[TOKEN_NAME] = $this->session->data[TOKEN_NAME];
		$data['base_route'] = EXTN_ROUTE;
		
		$store_info = $this->model_setting_setting->getSetting('hb_seourl', $this->request->get['store_id']);

		//dashboard
		if ($data['store_id'] == 0){ 
			$data['store_url'] = HTTPS_CATALOG;
		}else{
			$results = $this->model_setting_store->getStore($data['store_id']);
			$data['store_url'] = $results['url'];
		}
		
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		//settings
		$data['hb_seourl_keyword_product'] 		= isset($store_info['hb_seourl_keyword_product'])		?$store_info['hb_seourl_keyword_product']		:'{product_name}-{model}';
		$data['hb_seourl_use_pattern'] 	= isset($store_info['hb_seourl_use_pattern'])		?$store_info['hb_seourl_use_pattern']		:'';
		
		$data['hb_seourl_trans'] 	= isset($store_info['hb_seourl_trans'])		?$store_info['hb_seourl_trans']		:'';
		$data['hb_seourl_auto'] 	= isset($store_info['hb_seourl_auto'])		?$store_info['hb_seourl_auto']		:'';
		$data['hb_seourl_preserve'] = isset($store_info['hb_seourl_preserve'])	?$store_info['hb_seourl_preserve']	:'';
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/hbseo/'.TEMPLATE_FOLDER.'/hb_seourl'.TEMPLATE_EXTN, $data));
	}
	
	public function dashboard() {   		
		$store_id = (int)$this->request->get['store_id'];
		
		$this->load->language(EXTN_ROUTE.'/hb_seourl');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/hbseo/hb_seourl');
		$this->load->model('setting/setting');
		$data[TOKEN_NAME] = $this->session->data[TOKEN_NAME];
		
		$store_info = $this->model_setting_setting->getSetting('hb_seourl', $this->request->get['store_id']);

		//settings
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		foreach ($data['languages'] as $language){
	 		$language_id = $language['language_id'];	
			$data['total_product_count'][$language_id] = $this->model_extension_hbseo_hb_seourl->getProductCount($language_id, $store_id);
			$data['total_category_count'][$language_id] = $this->model_extension_hbseo_hb_seourl->getCategoryCount($language_id, $store_id);
			$data['total_brand_count'][$language_id] = $this->model_extension_hbseo_hb_seourl->getBrandCount($language_id, $store_id);
			$data['total_information_count'][$language_id] = $this->model_extension_hbseo_hb_seourl->getInformationCount($language_id, $store_id);
		
			$data['available_product_count'][$language_id] = $this->model_extension_hbseo_hb_seourl->getKeywordCountbyType('product_id=', $language_id, $store_id);
			$data['available_category_count'][$language_id] = $this->model_extension_hbseo_hb_seourl->getKeywordCountbyType('category_id=', $language_id, $store_id);
			$data['available_brand_count'][$language_id] = $this->model_extension_hbseo_hb_seourl->getKeywordCountbyType('manufacturer_id=', $language_id, $store_id);
			$data['available_information_count'][$language_id] = $this->model_extension_hbseo_hb_seourl->getKeywordCountbyType('information_id=', $language_id, $store_id);
			
			if ($data['total_product_count'][$language_id] == 0){
				$data['percent_product_count'][$language_id] = 0;
			}else{
				$data['percent_product_count'][$language_id] = ceil(($data['available_product_count'][$language_id]/$data['total_product_count'][$language_id]) * 100);
			}
			if ($data['total_category_count'][$language_id] == 0){
				$data['percent_category_count'][$language_id] = 0;
			}else{
				$data['percent_category_count'][$language_id] = ceil(($data['available_category_count'][$language_id]/$data['total_category_count'][$language_id]) * 100);
			}
			if ($data['total_brand_count'][$language_id] == 0){
				$data['percent_brand_count'][$language_id] = 0;
			}else{
				$data['percent_brand_count'][$language_id] = ceil(($data['available_brand_count'][$language_id]/$data['total_brand_count'][$language_id]) * 100);
			}
			if ($data['total_information_count'][$language_id] == 0){
				$data['percent_information_count'][$language_id] = 0;
			}else{
				$data['percent_information_count'][$language_id] = ceil(($data['available_information_count'][$language_id]/$data['total_information_count'][$language_id]) * 100);
			}
		}
			
		$this->response->setOutput($this->load->view('extension/hbseo/'.TEMPLATE_FOLDER.'/hb_seourl_dashboard'.TEMPLATE_EXTN, $data));
	}
	
	public function routes() {  
		$store_id = (int)$this->request->get['store_id'];
		$this->load->model('extension/hbseo/hb_seourl');

		$data[TOKEN_NAME] = $this->session->data[TOKEN_NAME];	
		
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		foreach ($data['languages'] as $language){
	 		$language_id = $language['language_id'];
		
			$records = $this->model_extension_hbseo_hb_seourl->getRoutes($store_id,$language_id);
			$data['records'][$language_id] = array();
		
			if ($records) {
				foreach ($records as $record) {
					$data['records'][$language_id][] = array(
						'id' 			=> $record['id'],
						'route' 		=> $record['route'],
						'keyword' 		=> $record['keyword'],
						'date_added'	=> date($this->language->get('date_format_short'), strtotime($record['date_added']))
					);
				}
			}
		}//endfor languages
		$this->response->setOutput($this->load->view('extension/hbseo/'.TEMPLATE_FOLDER.'/hb_seourl_routes'.TEMPLATE_EXTN, $data));
	}
	
	
	public function addlink(){
		$store_id = (int)$this->request->get['store_id'];
		$route = trim($this->request->post['route']);
		$language_id = $this->request->post['language_id'];
		$keyword = urlencode(trim($this->request->post['keyword']));
		
		if (empty($route) or empty($keyword)){
			$json['error'] = 'Please enter values';
		}else {
			$count = $this->db->query("SELECT count(*) as count FROM  `" . DB_PREFIX . "hb_url` WHERE `route` = '".$route."' AND store_id = '".(int)$store_id."' AND language_id = '".(int)$language_id."'");
			if ($count->row['count'] == 0){
				$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_url` (`route`,`keyword`,`language_id`,`store_id`) VALUES ('".$this->db->escape($route)."','".$this->db->escape($keyword)."','".(int)$language_id."','".(int)$store_id."')");
				$json['success'] = 'Route and keyword Added';
			}else{
				$json['error'] = 'Route Already Exists';
			}
		}
		$this->response->setOutput(json_encode($json));	
	}
	
	public function deletelink(){
		$count = 0;
		if (!isset($this->request->post['selected'])){
			$json['warning'] = 'No Record Selected!';
		}else{
			foreach ($this->request->post['selected'] as $id) {
				$this->db->query("DELETE FROM `" . DB_PREFIX . "hb_url` WHERE `id` = '".(int)$id."'");
				$count = $count + 1;
			}
			$json['success'] = $count.' Route(s) Deleted';
		}
		
		$this->response->setOutput(json_encode($json));
	}
	
	
	public function clearseourl(){
		$store_id = (int)$this->request->get['store_id'];	
		$query = $this->request->post['query'];
		$this->load->model('extension/hbseo/hb_seourl');
		$this->model_extension_hbseo_hb_seourl->clearKeywordType($query,$store_id);
		$json['success'] = 'SEO URL Cleared';
		$this->response->setOutput(json_encode($json));
	}	
	
	public function preserveseourl(){
		$store_id = (int)$this->request->get['store_id'];	
		$this->load->model('extension/hbseo/hb_seourl');
		$last_preserved_date = $this->model_extension_hbseo_hb_seourl->getPreserveDate($store_id);
		if ($last_preserved_date == false) {
			$this->model_extension_hbseo_hb_seourl->preserveKeyword($store_id);
			$json['success'] = 'SEO URL Preserved in database';
		}else{
			$json['warning'] = 'To preserve the latest SEO URL data, you need to clear the previous preserved data on '.date('d-m-Y',strtotime($last_preserved_date));
		}
		
		$this->response->setOutput(json_encode($json));
	}
	
	public function clearpreserveseourl(){
		$store_id = (int)$this->request->get['store_id'];	
		$this->load->model('extension/hbseo/hb_seourl');
		$this->model_extension_hbseo_hb_seourl->clearPreserve($store_id);
		$json['success'] = 'Preserved SEO URL data cleared from database';
		$this->response->setOutput(json_encode($json));
	}
	
	public function seourltobrokenlinks(){
		$store_id = (int)$this->request->get['store_id'];
		$this->load->model('setting/store');
		if ($store_id == 0){ 
			$store_url = HTTPS_CATALOG;
		}else{
			$results = $this->model_setting_store->getStore($data['store_id']);
			$store_url = $results['url'];
		}
		$this->load->model('extension/hbseo/hb_seourl');
		$isRedirectManagerInstalled = $this->model_extension_hbseo_hb_seourl->isRedirectManagerInstalled();
		if ($isRedirectManagerInstalled === true){
			$this->load->model('extension/hbseo/hb_brokenlinks');
			$records = $this->model_extension_hbseo_hb_seourl->getPreserveData($store_id);
			if ($records) {
				foreach ($records as $record) {
					$old_path = urlencode($store_url.$record['old_keyword']);
					$new_path = urlencode($store_url.$record['new_keyword']);
					$this->model_extension_hbseo_hb_brokenlinks->insertRecord($old_path, $new_path, $type = '301', $author = 3, $store_id);
				}
				$json['success'] = 'Record(s) added to Redirect Manager';
			}else{
				$json['warning'] = 'No Data found to send to Redirect Manager';
			}
		}else{
			$json['warning'] = 'Table Not found';
		}
		
		$this->response->setOutput(json_encode($json));
	}
	
	public function batch_generate() {
		$store_id = (int)$this->request->get['store_id'];	

		$this->load->model('extension/hbseo/hb_seourl');
		$this->model_extension_hbseo_hb_seourl->clearEmptyKeyword();
		$this->load->model('setting/setting');
		$this->load->model('localisation/language');
		
		$store_info = $this->model_setting_setting->getSetting('hb_seourl', $store_id);
		$preserve = isset($store_info['hb_seourl_preserve'])? true:false;
		$transliterate = isset($store_info['hb_seourl_trans'])? true:false;
		$use_pattern 	= isset($store_info['hb_seourl_use_pattern'])? true:false;
		$hb_seourl_keyword_product 	= isset($store_info['hb_seourl_keyword_product'])? $store_info['hb_seourl_keyword_product'] :'{product_name}';
		$shortcodes = array('product_name','model','brand','sku','upc','isbn','mpn');

		$languages = $this->model_localisation_language->getLanguages();
		foreach ($languages as $language) {
			$language_code[$language['language_id']] = substr($language['code'],0,2);
		}
		
		$options = array(
				'delimiter' 	=> '-',
				'lowercase' 	=> true,
				'transliterate' => $transliterate,
				);

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$total_entries = 0;

		$products = array();
		$total_entries = $this->model_extension_hbseo_hb_seourl->getTotalProductsEntries($store_id);
		
		$limit = 5000;
		$start = ($page - 1) * $limit;
		
		$results = $this->model_extension_hbseo_hb_seourl->getBatchProducts($store_id, $start, $limit);
		
		if ($results) {
			$start = ($page - 1) * $limit;
			$end = $start + $limit;

			if ($end < $total_entries) {
				$json['success'] = 'Product SEO URL Keyword successfully added for '.$start.' of '.$total_entries.' Product Entries!';
			} else {
				$json['success'] = 'Product SEO URL Keyword added for all batches!';
			}

			if ($end < $total_entries) {
				$json['next'] = str_replace('&amp;', '&', $this->url->link(EXTN_ROUTE.'/hb_seourl/batch_generate', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME] . '&store_id='.$store_id.'&page=' . ($page + 1), true));
			} else {
				$json['next'] = '';
			}

			foreach ($results as $result) {
				$id 			= $result['product_id'];
				$language_id 	= $result['language_id'];
				$name 			= $result['name'];
				
				$query = 'product_id='.$id;
				if ($this->model_extension_hbseo_hb_seourl->isKeywordNotAvailable($query, $language_id, $store_id)){
					if ($use_pattern) {		
						$product_info = $this->model_extension_hbseo_hb_seourl->getProductInfo($id,$language_id);
						$slug = $hb_seourl_keyword_product;
						
						foreach ($shortcodes as $code) {
							$slug = str_replace('{'.$code.'}',$product_info[$code],$slug);
						}
						
						$slug = $this->urlslug($slug, $options);
					}else{
						$slug = $this->urlslug($name, $options);
					}
					if ($this->model_extension_hbseo_hb_seourl->checkURLKeyword($slug, $store_id) > 0) {
						$slug = $slug.'-'.$id.'-'.$language_code[$language_id];
					} 
					$this->model_extension_hbseo_hb_seourl->addurlentry($query, $slug, $language_id, $store_id, $preserve);
				}//endif
			}//endfor
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function generatekeyword($store_id=0, $type='product'){
		$store_id = (int)$this->request->get['store_id'];	
		$type = $this->request->get['type'];
		$this->load->model('extension/hbseo/hb_seourl');
		$this->model_extension_hbseo_hb_seourl->clearEmptyKeyword();
		$this->load->model('setting/setting');
		$this->load->model('localisation/language');
		$store_info 	= $this->model_setting_setting->getSetting('hb_seourl', $store_id);
		$preserve 		= isset($store_info['hb_seourl_preserve'])? true:false;
		$transliterate 	= isset($store_info['hb_seourl_trans'])? true:false;
		$use_pattern 	= isset($store_info['hb_seourl_use_pattern'])? true:false;
		$hb_seourl_keyword_product 	= isset($store_info['hb_seourl_keyword_product'])? $store_info['hb_seourl_keyword_product'] :'{product_name}';
		
		$languages = $this->model_localisation_language->getLanguages();
		foreach ($languages as $language) {
			$language_code[$language['language_id']] = substr($language['code'],0,2);
		}
		
		$options = array(
				'delimiter' 	=> '-',
				'lowercase' 	=> true,
				'transliterate' => $transliterate,
				);
		
		$json['success'] = 'SEO URL Keyword Already Generated';
		$counter = 0;
		if ($type == 'product'){
			$shortcodes = array('product_name','model','brand','sku','upc','isbn','mpn');

			$results = $this->model_extension_hbseo_hb_seourl->getProducts($store_id);
			if ($results){
				foreach ($results as $result) {
					$id = $result['product_id'];
					$language_id = $result['language_id'];
					$name = $result['name'];

					$query = 'product_id='.$id;
					if ($this->model_extension_hbseo_hb_seourl->isKeywordNotAvailable($query, $language_id, $store_id)){
						$counter = $counter+1;
						if ($use_pattern) {		
							$product_info = $this->model_extension_hbseo_hb_seourl->getProductInfo($id,$language_id);
							$slug = $hb_seourl_keyword_product;							
							foreach ($shortcodes as $code) {
								$slug = str_replace('{'.$code.'}',$product_info[$code],$slug);
							}
							
							$slug = $this->urlslug($slug, $options);
						}else{
							$slug = $this->urlslug($name, $options);
						}
						
						if ($this->model_extension_hbseo_hb_seourl->checkURLKeyword($slug, $store_id) > 0) {
							$slug = $slug.'-'.$id.'-'.$language_code[$language_id];
						} 
						$this->model_extension_hbseo_hb_seourl->addurlentry($query, $slug, $language_id, $store_id, $preserve);
						$json['success'] = $counter.' Product SEO URL Keyword Added';
					}//endif
				}//endfor
			}//endif results
		}
		if ($type == 'category'){
			$results = $this->model_extension_hbseo_hb_seourl->getCategories($store_id);
			if ($results){
				foreach ($results as $result) {
					$id = $result['category_id'];
					$language_id = $result['language_id'];
					$name = $result['name'];
					
					$query = 'category_id='.$id;
					if ($this->model_extension_hbseo_hb_seourl->isKeywordNotAvailable($query, $language_id, $store_id)){
						$counter = $counter+1;
						$slug = $this->urlslug($name, $options);
						if ($this->model_extension_hbseo_hb_seourl->checkURLKeyword($slug, $store_id) > 0) {
							$slug = $slug.'-'.$id.'-'.$language_code[$language_id];
						} 
						$this->model_extension_hbseo_hb_seourl->addurlentry($query, $slug, $language_id, $store_id, $preserve);
						$json['success'] = $counter.' Category SEO URL Keyword Added';
					}
				}
			}
		}
		if ($type == 'brand'){
			$results = $this->model_extension_hbseo_hb_seourl->getBrands($store_id);
			if ($results){
				foreach ($results as $result) {
					$id = $result['manufacturer_id'];
					if (isset($result['language_id'])) {
						$language_id = $result['language_id'];
					} else {
						$language_id = $this->model_extension_hbseo_hb_seourl->defaultLanguage();
					}
					
					$name = $result['name'];
					
					$query = 'manufacturer_id='.$id;
					$languages = $this->model_localisation_language->getLanguages();
					foreach ($languages as $language) {
						$language_id = $language['language_id'];
					
						if ($this->model_extension_hbseo_hb_seourl->isKeywordNotAvailable($query, $language_id, $store_id)){
							$counter = $counter+1;
							$slug = $this->urlslug($name, $options);
							if ($this->model_extension_hbseo_hb_seourl->checkURLKeyword($slug, $store_id) > 0) {
								$slug = $slug.'-'.$id.'-'.$language_code[$language_id];
							} 
							$this->model_extension_hbseo_hb_seourl->addurlentry($query, $slug, $language_id, $store_id, $preserve);
							$json['success'] = $counter.' Brand SEO URL Keyword Added';
						}//endif
					}//endfor
				}//endfor
			}//endif results
		}
		if ($type == 'information'){
			$results = $this->model_extension_hbseo_hb_seourl->getInformations($store_id);
			if ($results){
				foreach ($results as $result) {
					$id = $result['information_id'];
					$language_id = $result['language_id'];
					$name = $result['name'];
					
					$query = 'information_id='.$id;
					if ($this->model_extension_hbseo_hb_seourl->isKeywordNotAvailable($query, $language_id, $store_id)){
						$counter = $counter+1;
						$slug = $this->urlslug($name, $options);
						if ($this->model_extension_hbseo_hb_seourl->checkURLKeyword($slug, $store_id) > 0) {
							$slug = $slug.'-'.$id.'-'.$language_code[$language_id];
						} 
						$this->model_extension_hbseo_hb_seourl->addurlentry($query, $slug, $language_id, $store_id, $preserve);
						$json['success'] = $counter.' Information SEO URL Keyword Added';
					}
				}
			}
		}
		
		$this->response->setOutput(json_encode($json));

	}
	
	public function install(){
			$this->load->model('extension/hbseo/hb_seourl');
			$this->model_extension_hbseo_hb_seourl->install();
	}
	
	public function uninstall(){
			$this->load->model('extension/hbseo/hb_seourl');
			$this->model_extension_hbseo_hb_seourl->uninstall();
	}
		
	private function validate() {
		if (!$this->user->hasPermission('modify', EXTN_ROUTE.'/hb_seourl')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}
	
	public function urlslug($str, $options = array()) {
		$str = htmlspecialchars_decode($str);
		// Make sure string is in UTF-8 and strip invalid UTF-8 characters
		$str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());
		
		//$str = transliterator_transliterate("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();", $str);
		
		$char_map = array(
		// Latin
		'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
		'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
		'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
		'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
		'ß' => 'ss',
		'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
		'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
		'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
		'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
		'ÿ' => 'y',
		 
		// Latin symbols
		'©' => '(c)',
		 
		// Greek
		'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
		'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
		'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
		'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
		'Ϋ' => 'Y', 
		'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
		'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
		'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
		'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
		'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
		 
		// Turkish
		'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
		'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g',
		 
		// Russian
		'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
		'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
		'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
		'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
		'Я' => 'Ya',
		'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
		'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
		'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
		'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
		'я' => 'ya',
		 
		// Ukrainian
		'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
		'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
		 
		// Czech
		'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U',
		'Ž' => 'Z',
		'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
		'ž' => 'z',
		 
		// Polish
		'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
		'Ż' => 'Z',
		'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
		'ż' => 'z',
		
		//Arabic
		"ا"=>"a", "أ"=>"a", "آ"=>"a", "إ"=>"e", "ب"=>"b", "ت"=>"t", "ث"=>"th", "ج"=>"j",
		"ح"=>"h", "خ"=>"kh", "د"=>"d", "ذ"=>"d", "ر"=>"r", "ز"=>"z", "س"=>"s", "ش"=>"sh",
		"ص"=>"s", "ض"=>"d", "ط"=>"t", "ظ"=>"z", "ع"=>"'e", "غ"=>"gh", "ف"=>"f", "ق"=>"q",
		"ك"=>"k", "ل"=>"l", "م"=>"m", "ن"=>"n", "ه"=>"h", "و"=>"w", "ي"=>"y", "ى"=>"a",
		"ئ"=>"'e", "ء"=>"'",   
		"ؤ"=>"'e", "لا"=>"la", "ة"=>"h", "؟"=>"?", "!"=>"!", 
		"ـ"=>"", 
		"،"=>",", 
		"َ‎"=>"a", "ُ"=>"u", "ِ‎"=>"e", "ٌ"=>"un", "ً"=>"an", "ٍ"=>"en", "ّ"=>"",
		
		//persian
		"ا" => "a", "أ" => "a", "آ" => "a", "إ" => "e", "ب" => "b", "ت" => "t", "ث" => "th",
		"ج" => "j", "ح" => "h", "خ" => "kh", "د" => "d", "ذ" => "d", "ر" => "r", "ز" => "z",
		"س" => "s", "ش" => "sh", "ص" => "s", "ض" => "d", "ط" => "t", "ظ" => "z", "ع" => "'e",
		"غ" => "gh", "ف" => "f", "ق" => "q", "ك" => "k", "ل" => "l", "م" => "m", "ن" => "n",
		"ه" => "h", "و" => "w", "ي" => "y", "ى" => "a", "ئ" => "'e", "ء" => "'", 
		"ؤ" => "'e", "لا" => "la", "ک" => "ke", "پ" => "pe", "چ" => "che", "ژ" => "je", "گ" => "gu",
		"ی" => "a", "ٔ" => "", "ة" => "h", "؟" => "?", "!" => "!", 
		"ـ" => "", 
		"،" => ",", 
		"َ‎" => "a", "ُ" => "u", "ِ‎" => "e", "ٌ" => "un", "ً" => "an", "ٍ" => "en", "ّ" => "",
		 
		// Latvian
		'Ā'  =>  'A', 'Č'  =>  'C', 'Ē'  =>  'E', 'Ģ'  =>  'G', 'Ī'  =>  'i', 'Ķ'  =>  'k', 'Ļ'  =>  'L', 'Ņ'  =>  'N',
		'Š'  =>  'S', 'Ū'  =>  'u', 'Ž'  =>  'Z',
		'ā'  =>  'a', 'č'  =>  'c', 'ē'  =>  'e', 'ģ'  =>  'g', 'ī'  =>  'i', 'ķ'  =>  'k', 'ļ'  =>  'l', 'ņ'  =>  'n',
		'š'  =>  's', 'ū'  =>  'u', 'ž'  =>  'z'
		);

		// Transliterate characters to ASCII
		if ($options['transliterate']) {
		$str = str_replace(array_keys($char_map), $char_map, $str);
		}
		// Replace non-alphanumeric characters with our delimiter
		$str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
		// Remove duplicate delimiters
		$str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

		// Remove delimiter from ends
		$str = trim($str, $options['delimiter']);
		return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
	}
	
	
}
?>