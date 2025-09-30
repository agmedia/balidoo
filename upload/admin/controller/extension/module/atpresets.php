<?php
class ControllerExtensionModuleAtpresets extends Controller {
	private $error = array(); 

	public function index() {   
		$this->load->language('extension/module/atpresets');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {			
			$this->model_setting_setting->editSetting('module_atpresets', $this->request->post);		

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']. '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
		} else {
			$data['success'] = '';
		}		

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
			'separator' => false
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']. '&type=module', true),
			'separator' => ' :: '
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/atpresets', 'user_token=' . $this->session->data['user_token'], true),
			'separator' => ' :: '
		);

		$data['action'] = $this->url->link('extension/module/atpresets', 'user_token=' . $this->session->data['user_token'], true);
		
		$data['upgrade'] = $this->url->link('extension/module/atpresets/upgrade', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']. '&type=module', true);

		$data['user_token'] = $this->session->data['user_token'];

		/*
		$exists = $this->db->query("SHOW KEYS FROM " . DB_PREFIX . "product_attribute WHERE Key_name = 'PRIMARY' AND Column_name = 'preset_id'");
		
		if (!$exists->num_rows) {
			$data['uprade_needed'] = true;
		} else {
			$data['uprade_needed'] = false;
		}
		*/
		$data['uprade_needed'] = false;	

		if (isset($this->request->post['module_atpresets_status'])) {
			$data['module_atpresets_status'] = $this->request->post['module_atpresets_status'];
		} else {
			$data['module_atpresets_status'] = $this->config->get('module_atpresets_status');
		}	
		
		if (isset($this->request->post['module_atpresets_delete'])) {
			$data['module_atpresets_delete'] = $this->request->post['module_atpresets_delete'];
		} else {
			$data['module_atpresets_delete'] = $this->config->get('module_atpresets_delete');
		}	
		
		if (isset($this->request->post['module_atpresets_allow_multiple'])) {
			$data['module_atpresets_allow_multiple'] = $this->request->post['module_atpresets_allow_multiple'];
		} else {
			$data['module_atpresets_allow_multiple'] = $this->config->get('module_atpresets_allow_multiple');
		}			

		if (isset($this->request->post['module_atpresets_autoupdate'])) {
			$data['module_atpresets_autoupdate'] = $this->request->post['module_atpresets_autoupdate'];
		} else {
			$data['module_atpresets_autoupdate'] = $this->config->get('module_atpresets_autoupdate');
		}	
		
		if (isset($this->request->post['module_atpresets_selecttype'])) {
			$data['module_atpresets_selecttype'] = $this->request->post['module_atpresets_selecttype'];
		} else {
			$data['module_atpresets_selecttype'] = $this->config->get('module_atpresets_selecttype');
		}			
		
		if (isset($this->request->post['module_atpresets_limit'])) {
			$data['module_atpresets_limit'] = $this->request->post['module_atpresets_limit'];
		} else {
			$data['module_atpresets_limit'] = $this->config->get('module_atpresets_limit');
		}	

		if (isset($this->request->post['module_atpresets_use_newline'])) {
			$data['module_atpresets_use_newline'] = $this->request->post['module_atpresets_use_newline'];
		} else {
			$data['module_atpresets_use_newline'] = $this->config->get('module_atpresets_use_newline');
		}	

		if (isset($this->request->post['module_atpresets_other_character'])) {
			$data['module_atpresets_other_character'] = $this->request->post['module_atpresets_other_character'];
		} else {
			$data['module_atpresets_other_character'] = $this->config->get('module_atpresets_other_character');
		}	
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/atpresets', $data));	
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/atpresets')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
	
	public function install() {   
		$this->load->model('extension/atpresets/install');
		$this->model_extension_atpresets_install->install();	
	}
	
	public function uninstall() {   
		$this->load->model('extension/atpresets/install');
		$this->model_extension_atpresets_install->uninstall();		
	}	

	public function getPresetTexts() {
		$json = array();

		if (isset($this->request->get['preset_id'])) {
			$this->load->model('extension/atpresets/atpresets');

			$json['texts'] = $this->model_extension_atpresets_atpresets->getPresetTexts($this->request->get['preset_id']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}	
	
	public function getManyPresetsTexts() {
		$json = array();

		if (isset($this->request->get['preset_id'])) {
			$this->request->get['preset_id'] = trim($this->request->get['preset_id'],'_');
			$presets = explode('_',$this->request->get['preset_id']);
			$this->load->model('extension/atpresets/atpresets');
			$presets_text = $this->model_extension_atpresets_atpresets->getManyPresetsTexts($presets);
			foreach ($presets_text as $texts) {
				foreach ($texts as $key => $text) {
					if (!isset($json[$key])) {
						$json[$key] = $text;
					} else {
						$json[$key] .= ', '.$text;
					}
				}
			}
			
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}		
		
	public function autocomplete() {
		$json = array();

		if ((isset($this->request->get['filter_name'])) && (isset($this->request->get['attribute_id']))){
			$this->load->model('extension/atpresets/atpresets');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'attribute_id' => $this->request->get['attribute_id'],
				'start'       => 0,
				'limit'       => $this->config->get('module_atpresets_limit')?$this->config->get('module_atpresets_limit'):8
			);

			$results = $this->model_extension_atpresets_atpresets->getPresetsAuto($filter_data);
			if ($this->request->get['attribute_id']!='') {
				foreach ($results as $result) {
					$json[] = array(
						'preset_id'		=> $result['preset_id'],
						'text'			=> html_entity_decode((utf8_strlen($result['text'])>50)?substr($result['text'], 0, 50).'...':$result['text'],ENT_QUOTES, 'UTF-8'),
						'not_decoded_text'			=> (utf8_strlen($result['text'])>50)?substr($result['text'], 0, 50).'...':$result['text'],
						'texts'			=> $this->model_extension_atpresets_atpresets->getPresetTexts($result['preset_id'])
					);
				}
			} else {
				foreach ($results as $result) {
					$json[] = array(
						'preset_id'		=> $result['preset_id'],
						'text'			=> html_entity_decode((utf8_strlen($result['text'])>50)?substr($result['text'], 0, 50).'...':$result['text'],ENT_QUOTES, 'UTF-8'),
						'not_decoded_text'			=> (utf8_strlen($result['text'])>50)?substr($result['text'], 0, 50).'...':$result['text'],
						'texts'			=> $this->model_extension_atpresets_atpresets->getPresetTexts($result['preset_id']),
						'attribute_name'=> $result['name'],	
						'attribute_id'	=> $result['attribute_id']					
					);
				}

			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}	

	public function insert() {
		$json = array();
		$this->load->language('extension/module/atpresets');
		$this->load->model('extension/atpresets/atpresets');
		

			if ($this->request->post['attribute_id'] != 0) {
				
				if (isset($this->request->post['texts'])) {
					//check if descriptions are empty
					$all_null = true;
					foreach ($this->request->post['texts'] as $key => $text) {
						if ($text != '') {
							$all_null = false;
						}
					}				
				} else {
					$all_null = false;
				}
				if (!$all_null) {								
					$data = array();
					$data['attribute_id'] = $this->request->post['attribute_id'];
					$data['descriptions'] = array();
					foreach ($this->request->post['texts'] as $key => $text) {
						$data['descriptions'][$key] = $text;	
					}			
					$results = $this->model_extension_atpresets_atpresets->addPreset($data);
					if ($results['exists']==0) {
						$json['message'] = $this->language->get('text_new_preset_added');
						$json['new_added'] = 1;
					} else {
						$json['message'] = $this->language->get('text_new_preset_exists');
						$json['new_added'] = 0;
					}
					$json['preset_id'] = $results['preset_id'];
					$json['preset_decoded'] = html_entity_decode($data['descriptions'][$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8');
					$json['preset'] = $data['descriptions'][$this->config->get('config_language_id')];
				} else {
					$json['message'] = $this->language->get('text_new_preset_error_empty');
				}
			} else 
				$json['message'] = $this->language->get('text_new_preset_error');
		

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function get_attributes() {
		$json = array();
		$this->load->language('extension/module/atpresets');
		$this->load->model('extension/atpresets/atpresets');

		if (!$this->user->hasPermission('modify', 'extension/module/atpresets')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		if (!$json) {

			if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else {
				$page = 1;
			}

			$limit = 100;
			$start = ($page - 1) * $limit;
			$end = $start + $limit;	
			
			$attributes_total_query = $this->db->query("SELECT COUNT(*) AS total FROM (SELECT * FROM " . DB_PREFIX . "product_attribute WHERE text<>'' group by product_id, attribute_id) as temp");
			$attributes_total = $attributes_total_query->row['total'] + $start;		
			
			$attributes = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attribute WHERE text<>'' AND preset_id=0 group by product_id, attribute_id ORDER BY product_id ASC,attribute_id ASC,language_id ASC LIMIT " . (int)$limit);
			
			if ($attributes->num_rows) {

				foreach ($attributes->rows as $attribute) {
					$values = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attribute WHERE text<>'' AND product_id = '" . (int)$attribute['product_id'] . "'AND attribute_id = '" . (int)$attribute['attribute_id'] . "' ORDER BY language_id ASC");
					$data=array();
					$data['attribute_id'] = $attribute['attribute_id'];
					$data['descriptions'] = array();
					foreach ($values->rows as $value){
						$data['descriptions'][$value['language_id']] = $value['text'];
					}
					
					$results = $this->model_extension_atpresets_atpresets->addPreset($data);
					$this->db->query("UPDATE " . DB_PREFIX . "product_attribute SET preset_id = '" . (int)$results['preset_id'] . "' WHERE product_id = '" . (int)$attribute['product_id'] . "' AND attribute_id = '" . (int)$attribute['attribute_id'] . "'");

				}
				
				if ($end < $attributes_total) {
					$json['success'] = sprintf($this->language->get('text_moving'), $end, $attributes_total);
				} else {				
					$json['success'] = $this->language->get('text_transfer_success');
				}

				if ($end < $attributes_total) {
					$json['next'] = str_replace('&amp;', '&', $this->url->link('extension/module/atpresets/get_attributes', 'user_token=' . $this->session->data['user_token'] . '&page=' . ($page + 1), true));
				} else {
					$json['next'] = '';
				}

			} else $json['success'] = $this->language->get('text_transfer_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}	

	public function upgrade() {
/*		
		$json = array();
		$this->load->language('extension/module/atpresets');
		
		$exists = $this->db->query("SHOW KEYS FROM " . DB_PREFIX . "product_attribute WHERE Key_name = 'PRIMARY' AND Column_name = 'preset_id'");
		$exists_v13 = $this->db->query("SHOW TABLES LIKE '". DB_PREFIX . "product_attribute_multiple'");
		
		if (!$exists->num_rows) {
			if (!$this->user->hasPermission('modify', 'extension/module/atpresets')) {
				$json['error']['warning'] = $this->language->get('error_permission');
			}
			
			if (!$json) {				
				$this->install();

				if ($exists_v13->num_rows) {
					//UPGRADE from v1.3
					$this->load->model('extension/atpresets/atpresets');
					
					if (isset($this->request->get['page'])) {
						$page = $this->request->get['page'];
					} else {
						$page = 1;
					}

					$assigned_presets_total_query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_attribute_multiple");
					$assigned_presets_total = $assigned_presets_total_query->row['total'];
				
					$limit = 100;				
					$start = ($page - 1) * $limit;
					$end = $start + $limit;	
					
					$presets = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attribute_multiple ORDER BY product_id ASC,attribute_id ASC LIMIT " . (int)$start . "," . (int)$limit);
					
					if ($presets->num_rows) {

						foreach ($presets->rows as $preset) {
							$texts = $this->model_extension_atpresets_atpresets->getPresetTexts($preset['preset_id']);
							$this->db->query("DELETE FROM  " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$preset['product_id'] . "' AND attribute_id = '" . (int)$preset['attribute_id'] . "' AND preset_id=0");								
							foreach ($texts as $language_id => $text) {
								$this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "product_attribute SET text = '".$this->db->escape($text)."', preset_id = '" . (int)$preset['preset_id'] . "', product_id = '" . (int)$preset['product_id'] . "', attribute_id = '" . (int)$preset['attribute_id'] . "', language_id ='".$language_id."'");								
							}
						}
					}
					
					if ($end < $assigned_presets_total) {
						$json['success'] = sprintf($this->language->get('text_moving'), $start, $assigned_presets_total);
					} else {
						$sql="drop TABLE IF EXISTS `" . DB_PREFIX . "product_attribute_multiple`";
						$query = $this->db->query($sql);							
						$this->session->data['success'] = $this->language->get('text_success_update');
					}

					if ($end < $assigned_presets_total) {
						$json['next'] = str_replace('&amp;', '&', $this->url->link('extension/module/atpresets/upgrade', 'user_token=' . $this->session->data['user_token'] . '&page=' . ($page + 1), true));
					} else {
						$json['next'] = '';
					}
				}
			}
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));		
		}	
*/		
	}
}
?>