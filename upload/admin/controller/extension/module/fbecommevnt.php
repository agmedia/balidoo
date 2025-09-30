<?php
class ControllerExtensionModulefbecommevnt extends Controller {	
	private $error = array();
	private $modpath = 'module/fbecommevnt'; 
	private $modtpl = 'module/fbecommevnt.tpl';
	private $modname = 'fbecommevnt';
  	private $modssl = 'SSL';
 	private $token_str = '';
	private $modurl = 'extension/module';
	private $modurltext = '';

	public function __construct($registry) {
		parent::__construct($registry);
 		
		if(substr(VERSION,0,3)>='3.0' || substr(VERSION,0,3)=='2.3') { 
			$this->modtpl = 'extension/module/fbecommevnt';
			$this->modpath = 'extension/module/fbecommevnt';
		} else if(substr(VERSION,0,3)=='2.2') {
			$this->modtpl = 'module/fbecommevnt';
		} 
		
		if(substr(VERSION,0,3)>='3.0') { 
			$this->modname = 'module_fbecommevnt';
			$this->modurl = 'marketplace/extension'; 
			$this->token_str = 'user_token=' . $this->session->data['user_token'] . '&type=module';
		} else if(substr(VERSION,0,3)=='2.3') {
			$this->modurl = 'extension/extension';
			$this->token_str = 'token=' . $this->session->data['token'] . '&type=module';
		} else {
			$this->token_str = 'token=' . $this->session->data['token'];
		}
		
		if(substr(VERSION,0,3)>='3.0' || substr(VERSION,0,3)=='2.3' || substr(VERSION,0,3)=='2.2') { 
			$this->modssl = true;
		} 
 	} 
	
	public function index() {
		$data = $this->load->language($this->modpath);
		$this->modurltext = $this->language->get('text_extension');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting($this->modname, $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			if(! (isset($this->request->post['svsty']) && $this->request->post['svsty'] == 1)) {
				$this->response->redirect($this->url->link($this->modurl, $this->token_str, $this->modssl));
			}
		}

		$data['heading_title'] = $this->language->get('heading_title');
 
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->token_str, $this->modssl)
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->modurltext,
			'href' => $this->url->link($this->modurl, $this->token_str, $this->modssl)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->modpath, $this->token_str, $this->modssl)
		);

		$data['action'] = $this->url->link($this->modpath, $this->token_str, $this->modssl);
		
		$data['cancel'] = $this->url->link($this->modurl, $this->token_str , $this->modssl); 
		
		if(substr(VERSION,0,3)>='3.0') { 
			$data['user_token'] = $this->session->data['user_token'];
		} else {
			$data['token'] = $this->session->data['token'];
		}
		
		$this->load->model('localisation/language');
  		$languages = $this->model_localisation_language->getLanguages();
		foreach($languages as $language) {
			if(substr(VERSION,0,3)>='3.0' || substr(VERSION,0,3)=='2.3' || substr(VERSION,0,3)=='2.2') {
				$imgsrc = "language/".$language['code']."/".$language['code'].".png";
			} else {
				$imgsrc = "view/image/flags/".$language['image'];
			}
			$data['languages'][] = array("language_id" => $language['language_id'], "name" => $language['name'], "imgsrc" => $imgsrc);
		}
		
		$this->load->model('setting/store');
		$store_default = array(0=> array("name"=>'Default',"store_id"=>0));
		$data['stores'] = $this->model_setting_store->getStores();	
		$data['stores'] = array_merge($store_default,$data['stores']);
		
		foreach ($data['stores'] as $store) {
			$data[$this->modname.'_sts'][$store['store_id']] = $this->setvalue($this->modname.'_sts'.$store['store_id']);
			$data[$this->modname.'_fb_pixel_id'][$store['store_id']] = $this->setvalue($this->modname.'_fb_pixel_id'.$store['store_id']);
			$data[$this->modname.'_fb_product_catalog_id'][$store['store_id']] = $this->setvalue($this->modname.'_fb_product_catalog_id'.$store['store_id']);
 		}
		
  		$data[$this->modname.'_status'] = $this->setvalue($this->modname.'_status');
		
		$data[$this->modname.'_lang'] = $this->setvalue($this->modname.'_lang');
		
		$lang = array('text_atc', 'text_wishlist', 'text_compare', 'text_removecart', 'text_loginevent', 'text_regevent', 'text_chkstp_onename', 'text_chkstp_twoname', 'text_chkstp_threename', 'text_chkstp_fourname', 'text_chkstp_fivename','text_chkstp_sixname');
		$data['lang'] = $lang;
		$data['entrylang'] = array();
		foreach($lang as $lng) {
			$data['entrylang'][$lng] = $data['entry_'.$lng];
		}
		
		foreach($languages as $language) {			
			foreach($lang as $lng) {
				$data[$this->modname.'_lang'][$lng][$language['language_id']] = isset($data[$this->modname.'_lang'][$lng][$language['language_id']]) ? $data[$this->modname.'_lang'][$lng][$language['language_id']] : '';
			}
		}
   		  
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->modtpl, $data));
	}
	
	protected function setvalue($postfield) {
		if (isset($this->request->post[$postfield])) {
			$postfield_value = $this->request->post[$postfield];
		} else {
			$postfield_value = $this->config->get($postfield);
		} 	
 		return $postfield_value;
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', $this->modpath)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
	
	public function install() {
		$this->load->model('extension/fbecommevnt');
		$this->model_extension_fbecommevnt->checkdb();
	}
	public function uninstall() { 
		// uninstalled       
	}
}