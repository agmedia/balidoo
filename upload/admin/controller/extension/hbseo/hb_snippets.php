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
define('EXTN_VERSION', '9.3.16'); 
class ControllerExtensionHbseoHbSnippets extends Controller {
	
	private $error = array(); 
	
	public function index() {   
		$data['extension_version'] = EXTN_VERSION;
		
		if (isset($this->request->get['store_id'])){
			$data['store_id'] = (int)$this->request->get['store_id'];
		}else{
			$data['store_id'] = 0;
		}
		
		$this->load->language(EXTN_ROUTE.'/hb_snippets');
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/store');
		$data['stores'] = $this->model_setting_store->getStores();

		$this->load->model('setting/setting');
		
		//Save the settings if the user has submitted the admin form (ie if someone has pressed save).
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('hb_snippets', $this->request->post, $this->request->get['store_id']);	
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link(EXTN_ROUTE.'/hb_snippets', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME].'&store_id='.$data['store_id'], true));
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		
		$text_strings = array(
				'heading_title',
				'tab_sd','tab_contact','tab_og','tab_tc',
				'button_save',
				'button_cancel','button_remove',
				'btn_generate'
		);
		
		foreach ($text_strings as $text) {
			$data[$text] = $this->language->get($text);
		}
	
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->error['logo'])) {
			$data['error_logo'] = $this->error['logo'];
		} else {
			$data['error_logo'] = '';
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
			'href'      => $this->url->link(EXTN_ROUTE.'/hb_snippets', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME].'&store_id='.$data['store_id'], true)
   		);
		
		$data['action'] = $this->url->link(EXTN_ROUTE.'/hb_snippets', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME].'&store_id='.$data['store_id'], true);
		
		$data['cancel'] = $this->url->link(EXTENSION_BASE, TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME] . '&type=hbseo', true);
		$data[TOKEN_NAME] = $this->session->data[TOKEN_NAME];
		$data['base_route'] = EXTN_ROUTE;
		
		$this->load->model('localisation/stock_status');
		$data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();
		
		$data['availability'] = array('Discontinued','InStock','InStoreOnly','LimitedAvailability','OnlineOnly','OutOfStock','PreOrder','PreSale','SoldOut');
		
		$store_info = $this->model_setting_setting->getSetting('hb_snippets', $this->request->get['store_id']);
				
		if (isset($this->request->post['hb_snippets_logo'])) {
			$data['hb_snippets_logo'] = $this->request->post['hb_snippets_logo'];
		} elseif (isset($store_info['hb_snippets_logo'])) {
			$data['hb_snippets_logo'] = $store_info['hb_snippets_logo'];
		} else {
			$data['hb_snippets_logo'] = '';
		}

		$this->load->model('tool/image');

		if (isset($this->request->post['hb_snippets_logo']) && is_file(DIR_IMAGE . $this->request->post['hb_snippets_logo'])) {
			$data['logo_thumb'] = $this->model_tool_image->resize($this->request->post['hb_snippets_logo'], 250, 100);
		} elseif (isset($store_info['hb_snippets_logo']) && is_file(DIR_IMAGE . $store_info['hb_snippets_logo'])) {
			$data['logo_thumb'] = $this->model_tool_image->resize($store_info['hb_snippets_logo'], 250, 100);
		} else {
			$data['logo_thumb'] = $this->model_tool_image->resize('no_image.png', 250, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 250, 100);

		$data['hb_snippets_prod_enable'] = isset($store_info['hb_snippets_prod_enable'])? $store_info['hb_snippets_prod_enable']:'';
		$data['hb_snippets_description'] = isset($store_info['hb_snippets_description'])? $store_info['hb_snippets_description']:'meta_description';
		$data['hb_snippets_incl_tax'] = isset($store_info['hb_snippets_incl_tax'])? $store_info['hb_snippets_incl_tax']:'';
		$data['hb_snippets_pricevalid'] = isset($store_info['hb_snippets_pricevalid'])? $store_info['hb_snippets_pricevalid']:'';
		$data['hb_snippets_pricevaliddate'] = isset($store_info['hb_snippets_pricevaliddate'])? $store_info['hb_snippets_pricevaliddate']:date('Y-m-d', strtotime('+3 years'));
		$data['hb_snippets_brand'] = isset($store_info['hb_snippets_brand'])? $store_info['hb_snippets_brand']:'';
		$data['hb_snippets_stock'] = isset($store_info['hb_snippets_stock'])? $store_info['hb_snippets_stock'] : array();
		$data['hb_snippets_bc_enable'] = isset($store_info['hb_snippets_bc_enable'])? $store_info['hb_snippets_bc_enable']:'';
		$data['hb_snippets_bc_type'] = isset($extn_info['hb_snippets_bc_type'])? $extn_info['hb_snippets_bc_type']:'smart';
		$data['hb_snippets_list_enable'] = isset($store_info['hb_snippets_list_enable'])? $store_info['hb_snippets_list_enable']:'';

		$data['hb_snippets_contact'] = isset($store_info['hb_snippets_contact'])?$store_info['hb_snippets_contact']:'';
		$data['hb_snippets_socials'] = isset($store_info['hb_snippets_socials'])?$store_info['hb_snippets_socials']:'';	
		$data['hb_snippets_search_enable'] = isset($store_info['hb_snippets_search_enable'])?$store_info['hb_snippets_search_enable']:'';
		
		$data['hb_snippets_kg_enable'] = isset($store_info['hb_snippets_kg_enable'])?$store_info['hb_snippets_kg_enable']:'';		
		
		$data['hb_snippets_og_enable'] = isset($store_info['hb_snippets_og_enable'])?$store_info['hb_snippets_og_enable']:'';
		$data['hb_snippets_og_id'] = isset($store_info['hb_snippets_og_id'])?$store_info['hb_snippets_og_id']:'';
		$data['hb_snippets_ogp'] = isset($store_info['hb_snippets_ogp'])?$store_info['hb_snippets_ogp']:'';
		$data['hb_snippets_ogc'] = isset($store_info['hb_snippets_ogc'])?$store_info['hb_snippets_ogc']:'';
		$data['hb_snippets_og_diw'] = isset($store_info['hb_snippets_og_ciw'])?$store_info['hb_snippets_og_diw']:'820';
		$data['hb_snippets_og_dih'] = isset($store_info['hb_snippets_og_cih'])?$store_info['hb_snippets_og_dih']:'312';
		$data['hb_snippets_og_piw'] = isset($store_info['hb_snippets_og_piw'])?$store_info['hb_snippets_og_piw']:'500';
		$data['hb_snippets_og_pih'] = isset($store_info['hb_snippets_og_pih'])?$store_info['hb_snippets_og_pih']:'500';
		$data['hb_snippets_og_ciw'] = isset($store_info['hb_snippets_og_ciw'])?$store_info['hb_snippets_og_ciw']:'500';
		$data['hb_snippets_og_cih'] = isset($store_info['hb_snippets_og_cih'])?$store_info['hb_snippets_og_cih']:'500';
				
		$data['hb_snippets_og_img'] = isset($store_info['hb_snippets_og_img'])?$store_info['hb_snippets_og_img']:'';
		
		if (isset($data['hb_snippets_og_img']) && is_file(DIR_IMAGE . $data['hb_snippets_og_img'])) {
			$data['ogimg'] = $this->model_tool_image->resize($data['hb_snippets_og_img'], 340, 126);
		} else {
			$data['ogimg'] = $this->model_tool_image->resize('no_image.png', 340, 126);
		}	
		
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 340, 126);
		
		$data['hb_snippets_tc_enable'] = isset($store_info['hb_snippets_tc_enable'])?$store_info['hb_snippets_tc_enable']:'';
		$data['hb_snippets_tc_username'] = isset($store_info['hb_snippets_tc_username'])?$store_info['hb_snippets_tc_username']:'';
		$data['hb_snippets_tcp'] = isset($store_info['hb_snippets_tcp'])?$store_info['hb_snippets_tcp']:'';
		$data['hb_snippets_tcc'] = isset($store_info['hb_snippets_tcc'])?$store_info['hb_snippets_tcc']:'';
		
		$data['hb_snippets_local_name'] = isset($store_info['hb_snippets_local_name'])?$store_info['hb_snippets_local_name']:'';
		$data['hb_snippets_local_st'] = isset($store_info['hb_snippets_local_st'])?$store_info['hb_snippets_local_st']:'';
		$data['hb_snippets_local_location'] = isset($store_info['hb_snippets_local_location'])?$store_info['hb_snippets_local_location']:'';
		$data['hb_snippets_local_state'] = isset($store_info['hb_snippets_local_state'])?$store_info['hb_snippets_local_state']:'';	
		$data['hb_snippets_local_postal'] = isset($store_info['hb_snippets_local_postal'])?$store_info['hb_snippets_local_postal']:'';
		$data['hb_snippets_local_country'] = isset($store_info['hb_snippets_local_country'])?$store_info['hb_snippets_local_country']:'';
		$data['hb_snippets_store_image'] = isset($store_info['hb_snippets_store_image'])?$store_info['hb_snippets_store_image']:'';
		$data['hb_snippets_price_range'] = isset($store_info['hb_snippets_price_range'])?$store_info['hb_snippets_price_range']:'';
		$data['hb_snippets_local_snippet'] = isset($store_info['hb_snippets_local_snippet'])?$store_info['hb_snippets_local_snippet']:'';
		$data['hb_snippets_local_enable'] = isset($store_info['hb_snippets_local_enable'])?$store_info['hb_snippets_local_enable']:'';	
					
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/hbseo/'.TEMPLATE_FOLDER.'/hb_snippets'.TEMPLATE_EXTN, $data));

	}
	
	private function validate() {
		if (!$this->user->hasPermission('modify', EXTN_ROUTE.'/hb_snippets')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
				
		if ($this->request->post['hb_snippets_logo']) {
			$image = DIR_IMAGE.$this->request->post['hb_snippets_logo'];
			list($width, $height) = getimagesize($image);
			if ($width < 112 or $height < 112) {
				$this->error['logo'] = 'Please upload a larger resolution logo image. Image must be 112 X 112px minimum';
			}
		}
		
		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}
	
	public function generatelocalsnippet(){
		$data['store_id'] = $this->request->get['store_id'];
		
		$name = $this->request->post['name'];
		$street = $this->request->post['street'];
		$location = $this->request->post['location'];
		$postal = $this->request->post['postal'];
		
		$phone= $this->config->get('config_telephone');
		$country = $this->request->post['country'];
		$store_image = $this->request->post['store_image'];
		$price_range = $this->request->post['price_range'];
		$state = $this->request->post['state'];
		
		if ($data['store_id'] == 0){
			$store_url = HTTPS_CATALOG;
		}else{
			$store = $this->db->query("SELECT `url` FROM ".DB_PREFIX."store WHERE store_id = '".$data['store_id']."'");
			$store_url = $store->row['url'];
		}
				
		$code = '<script type="application/ld+json">
		{
  "@context": "http://schema.org",
  "@type": "Store",
  "@id": "'.$store_url.'",
  "image": "'.$store_image.'",
  "name": "'.$name.'",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "'.$street.'",
    "addressLocality": "'.$location.'",
    "addressRegion": "'.$state.'",
    "postalCode": "'.$postal.'",
    "addressCountry": "'.$country.'"
  },
"telephone": "'.$phone.'",
"priceRange": "'.$price_range.'"
}</script>';
		$json['success'] = $code;
		$this->response->setOutput(json_encode($json));
	}
	
	public function install(){		
		if ((version_compare(VERSION,'2.0.0.0','>=' )) and (version_compare(VERSION,'2.3.0.0','<' ))) {
			$ocmod_filename = 'ocmod_hb_seo_snippets_2000_2200.txt';
			$ocmod_name = 'SEO - Structured Data Markup [2000-2200]';
		}else if ((version_compare(VERSION,'2.3.0.0','>=' )) and (version_compare(VERSION,'3.0.0.0','<' ))) {
			$ocmod_filename = 'ocmod_hb_seo_snippets_23xx.txt';
			$ocmod_name = 'SEO - Structured Data Markup [23xx]';
		}else if (version_compare(VERSION,'3.0.0.0','>=' )) {
			$ocmod_filename = 'ocmod_hb_seo_snippets_3xxx.txt';
			$ocmod_name = 'SEO - Structured Data Markup [3xxx]';
		}
		
		$ocmod_version = EXTN_VERSION;
		$ocmod_code = 'huntbee_seo_snippet_ocmod';	
		$ocmod_author = 'HuntBee OpenCart Services';
		$ocmod_link = 'https://www.huntbee.com';
		
		$file = DIR_APPLICATION . 'view/template/extension/hbseo/ocmod/'.$ocmod_filename;
		if (file_exists($file)) {
			$ocmod_xml = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
			$this->db->query("INSERT INTO " . DB_PREFIX . "modification SET code = '" . $this->db->escape($ocmod_code) . "', name = '" . $this->db->escape($ocmod_name) . "', author = '" . $this->db->escape($ocmod_author) . "', version = '" . $this->db->escape($ocmod_version) . "', link = '" . $this->db->escape($ocmod_link) . "', xml = '" . $this->db->escape($ocmod_xml) . "', status = '1', date_added = NOW()");
		}
		
		$data['success'] = 'This extension has been installed successfully';
	}
	
	public function uninstall(){
		$this->db->query("DELETE FROM " . DB_PREFIX . "modification WHERE `code` = 'huntbee_seo_snippet_ocmod'");
		$data['success'] = 'This extension is uninstalled successfully';
	}
}
?>