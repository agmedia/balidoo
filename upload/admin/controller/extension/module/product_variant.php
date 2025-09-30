<?php
/*
 * Advanced Product Variants
 *
 * @author    Nxtal <support@nxtal.com>
 * @copyright Nxtal 2020
 * @license   Commercial
 * @version   1.0.0
 *
*/

class ControllerExtensionModuleProductVariant extends Controller {
	private $error = array(); 
	
	private $settings = array(
		"variant_text" => array(),
		"quantity" => 0,
		"image_width" => 30,
		"image_height" => 30);
	
	public function __construct($registry) {
		parent::__construct($registry);	
		$this->registry = $registry;
	}	
	
	public function index() {   
		$this->language->load('extension/module/product_variant');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
				
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			$this->model_setting_setting->editSetting('product_variant_setting', $this->request->post);		
					
			$this->session->data['success'] = $this->language->get('text_success');
			
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
			
		}
				
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_active'] = $this->language->get('text_active');
		$data['text_inactive'] = $this->language->get('text_inactive');
		$data['text_hide'] = $this->language->get('text_hide');
		$data['text_text_variant'] = $this->language->get('text_text_variant');
		$data['text_swatch_variant'] = $this->language->get('text_swatch_variant');
		$data['option_variant_types'] = $this->language->get('option_variant_types');

		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		
		$data['entry_text'] = $this->language->get('entry_text');
		$data['entry_catalog'] = $this->language->get('entry_catalog');
		$data['entry_variant_type'] = $this->language->get('entry_variant_type');
		$data['entry_soldout'] = $this->language->get('entry_soldout');
		$data['entry_image_width'] = $this->language->get('entry_image_width');
		$data['entry_image_height'] = $this->language->get('entry_image_height');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_ajax'] = $this->language->get('entry_ajax');
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

  		$data['breadcrumbs'] = array();
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/product_variant', 'user_token=' . $this->session->data['user_token'], true)
		);
		
		$data['action'] = $this->url->link('extension/module/product_variant', 'user_token=' . $this->session->data['user_token'], true);
		
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		
		$data['variant'] = array();
		
		if (isset($this->request->post['product_variant_setting'])) {
			$data['variant'] = $this->request->post['product_variant_setting'];
		} elseif ($this->config->get('product_variant_setting')) { 
			$data['variant'] = $this->config->get('product_variant_setting');
		}		
		
		if (isset($this->request->post['product_variant_setting_status'])) {
			$data['variant_status'] = $this->request->post['product_variant_setting_status'];
		} elseif ($this->config->get('product_variant_setting_status')) { 
			$data['variant_status'] = $this->config->get('product_variant_setting_status');
		}
		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/product_variant', $data));
		
	}	
	
	public function install() { 
		$this->validate(__FUNCTION__);
		
		$this->load->model('setting/setting');
		
		$this->db->query("
		CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "product_variant` (
		`variant_id` text NOT NULL) default CHARSET=utf8");	
		
		$settings = array('product_variant_setting' => $this->settings, 'product_variant_setting_status' => 0);
		$this->model_setting_setting->editSetting('product_variant_setting', $settings);
		
		$this->load->model('user/user_group');		
		
		$this->model_user_user_group->addPermission($this->user->getId(), 'access', 'module/product_variant');
		$this->model_user_user_group->addPermission($this->user->getId(), 'modify', 'module/product_variant');		
		
	}

	public function uninstall() {
		$this->validate(__FUNCTION__);
		
		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('product_variant_setting');
		$this->model_setting_setting->deleteSetting('product_variant');
		$this->db->query("DROP TABLE IF EXISTS
		`" . DB_PREFIX . "product_variant`");
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/product_variant')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>