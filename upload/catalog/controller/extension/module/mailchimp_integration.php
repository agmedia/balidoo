<?php
//==============================================================================
// MailChimp Integration v302.3
// 
// Author: Clear Thinking, LLC
// E-mail: johnathan@getclearthinking.com
// Website: http://www.getclearthinking.com
// 
// All code within this file is copyright Clear Thinking, LLC.
// You may not copy or reuse code within this file without written permission.
//==============================================================================

class ControllerExtensionModuleMailchimpIntegration extends Controller {
	private $type = 'module';
	private $name = 'mailchimp_integration';
	private $popup = false;
	
	public function popup() {
		$prefix = (version_compare(VERSION, '3.0', '<')) ? '' : $this->type . '_';
		if (!$this->config->get($prefix . $this->name . '_modules_popup')) return;
		
		$this->popup = $this->config->get($prefix . $this->name . '_modules_popup');
		return $this->index();
	}
	
	public function index() {
		$data['settings'] = $this->getSettings();
		$data['type'] = $this->type;
		$data['name'] = $this->name;
		
		$data['popup'] = $this->popup;
		if (empty($_COOKIE[$this->name . '_popup']) && $data['popup'] == 'auto') {
			setcookie($this->name . '_popup', 'triggered');
			$data['trigger_popup'] = true;
		}
		
		$data = array_merge($data, $this->load->language('account/address'));
		$data = array_merge($data, $this->load->language('account/edit'));
		$data['language'] = $this->session->data['language'];
		
		if (empty($data['settings']['status'])) {
			return;
		}
		
		if (empty($data['settings']['apikey']) || empty($data['settings']['listid'])) {
			return '<span style="color: red">You must fill in your API Key, and choose a list to sync with in the List Settings tab, before the module will work.</span>';
		}
		
		// Set customer data
		$data['email'] = $this->customer->getEmail();
		$data['subscribed'] = $this->customer->getNewsletter();
		
		// Load library
		$address_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "address WHERE address_id = " . (int)$this->customer->getAddressId());
		$address = (!empty($address_query->row)) ? $address_query->row : array();
		
		if (version_compare(VERSION, '2.1', '<')) $this->load->library($this->name);
		$mailchimp_integration = new MailChimp_Integration($this->config, $this->db, $this->session, $this->url);
		$data['settings']['listid'] = $mailchimp_integration->determineList(array('customer_group_id' => (int)$this->customer->getGroupId()), $address);
		
		// Set country/zone data
		$this->load->model('localisation/country');
		$data['countries'] = $this->model_localisation_country->getCountries();
		$data['country_id'] = $this->config->get('config_country_id');
		$data['zone_id'] = $this->config->get('config_zone_id');
		
		// Render
		$theme = (version_compare(VERSION, '2.2', '<')) ? $this->config->get('config_template') : $this->config->get('theme_default_directory');
		$template = (file_exists(DIR_TEMPLATE . $theme . '/template/extension/' . $this->type . '/' . $this->name . '.twig')) ? $theme : 'default';
		$template_file = DIR_TEMPLATE . $template . '/template/extension/' . $this->type . '/' . $this->name . '.twig';
		
		if (version_compare(VERSION, '3.0', '>=')) {
			$override_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "theme WHERE theme = '" . $this->db->escape($theme) . "' AND route = 'extension/" . $this->type . "/" . $this->name . "'");
			if ($override_query->num_rows) {
				$cache_file = DIR_CACHE . $this->name . '.twig.' . strtotime($override_query->row['date_added']);
				
				if (!file_exists($cache_file)) {
					$old_files = glob(DIR_CACHE . $this->name . '.twig.*');
					foreach ($old_files as $old_file) unlink($old_file);
					file_put_contents($cache_file, html_entity_decode($override_query->row['code'], ENT_QUOTES, 'UTF-8'));
				}
				
				$template_file = $cache_file;
			}
		}
		
		if (is_file($template_file)) {
			extract($data);
			
			ob_start();
			require(class_exists('VQMod') ? VQMod::modCheck(modification($template_file)) : modification($template_file));
			$output = ob_get_clean();
			
			return $output;
		} else {
			return 'Error loading template file: ' . $template_file;
		}
	}
	
	//==============================================================================
	// Ajax functions
	//==============================================================================
	public function subscribe() {
		if (empty($this->request->post)) return;
		
		$customer_id = 0;
		$customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE email = '". $this->db->escape($this->request->post['email']) . "'");
		
		if ($customer_query->num_rows) {
			$customer_id = $customer_query->row['customer_id'];
			$this->request->post['customer_id'] = $customer_id;
		}
		if (isset($this->request->post['address'])) {
			$this->request->post['address_1'] = $this->request->post['address'];
			unset($this->request->post['address']);
		}
		if (isset($this->request->post['country'])) {
			$this->request->post['country_id'] = $this->request->post['country'];
		}
		if (isset($this->request->post['zone'])) {
			$this->request->post['zone_id'] = $this->request->post['zone'];
		}
		
		if (version_compare(VERSION, '2.1', '<')) $this->load->library($this->name);
		$mailchimp_integration = new MailChimp_Integration($this->config, $this->db, $this->session, $this->url);
		$data = array_merge($this->request->post, array('newsletter' => 1, 'update_existing' => (bool)($this->customer->isLogged() || !empty($settings['interest_groups']))));
		
		$error = $mailchimp_integration->send($data);
		
		if (!$error && $customer_id && empty($customer_query->row['newsletter'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "customer SET newsletter = 1 WHERE customer_id = " . $customer_id);
		}
		
		echo str_replace('Use PUT to insert or update list members.', '', $error);
	}
	
	public function webhook() {
		if (!isset($this->request->post['type']) || !isset($this->request->post['data'])) {
			echo 'The MailChimp Integration webhook is working.';
			if (!isset($this->request->get['key'])) {
				echo ' However, no key is set. This is required for the webhook data to be processed properly.';
			}
			return;
		}
		
		if ($this->request->get['key'] != md5($this->config->get('config_encryption'))) {
			$prefix = (version_compare(VERSION, '3.0', '<')) ? '' : $this->type . '_';
			if ($this->config->get($prefix . $this->name . '_testing_mode')) {
				$this->log->write(strtoupper($this->name) . ' WEBHOOK ERROR: webhook URL key ' . $this->request->get['key'] . ' does not match required key ' . md5($this->config->get('config_encryption')) . ' for action "' . $this->request->post['type'] . '" for e-mail address ' . $this->request->post['data']['email']);
			}
			return;
		}
		
		if (version_compare(VERSION, '2.1', '<')) $this->load->library($this->name);
		$mailchimp_integration = new MailChimp_Integration($this->config, $this->db, $this->session, $this->url);
		$mailchimp_integration->webhook($this->request->post['type'], $this->request->post['data']);
	}
	
	//==============================================================================
	// Private functions
	//==============================================================================
	private function getSettings() {
		// custom, to work with list IDs and interest group IDs
		$code = (version_compare(VERSION, '3.0', '<')) ? $this->name : $this->type . '_' . $this->name;
		
		$settings = array();
		$settings_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `code` = '" . $this->db->escape($code) . "' ORDER BY `key` ASC");
		
		foreach ($settings_query->rows as $setting) {
			$value = $setting['value'];
			if ($setting['serialized']) {
				$value = (version_compare(VERSION, '2.1', '<')) ? unserialize($setting['value']) : json_decode($setting['value'], true);
			}
			$settings[str_replace($code . '_', '', $setting['key'])] = $value;
		}
		
		return $settings;
	}
}
?>