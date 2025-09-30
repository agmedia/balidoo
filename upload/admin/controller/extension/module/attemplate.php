<?php
class ControllerExtensionModuleAttemplate extends Controller {
	private $error = array();
 
	public function index() {
		$this->load->language('extension/module/attemplate');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('extension/atpresets/attemplate');
		
		$this->getList();
	} 

	public function update() {
		$this->load->language('extension/module/attemplate');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('extension/atpresets/attemplate');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_atpresets_attemplate->editAttemplate($this->request->get['attemplate_id'], $this->request->post);
			
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
						
			$this->response->redirect($this->url->link('extension/module/attemplate', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}
	
	public function insert() {
		$this->load->language('extension/module/attemplate');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('extension/atpresets/attemplate');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_atpresets_attemplate->addAttemplate($this->request->post);
			
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
						
			$this->response->redirect($this->url->link('extension/module/attemplate', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}
	
	public function delete() { 
		$this->load->language('extension/module/attemplate');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('extension/atpresets/attemplate');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $attemplate_id) {
				$this->model_extension_atpresets_attemplate->deleteAttemplate($attemplate_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
						
			$this->response->redirect($this->url->link('extension/module/attemplate', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
			
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}
		
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
				
		$url = '';
					
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/attemplate', 'user_token=' . $this->session->data['user_token'] . $url, true)
   		);
							
		$data['insert'] = $this->url->link('extension/module/attemplate/insert', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('extension/module/attemplate/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);	

		$data['attemplates'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);
		
		$attemplate_total = $this->model_extension_atpresets_attemplate->getTotalAttemplates($filter_data);
		
		$results = $this->model_extension_atpresets_attemplate->getAttemplates($filter_data);
    	
		foreach ($results as $result) {

			$data['attemplates'][] = array(
				'attemplate_id'  => $result['attemplate_id'],
				'name'     => $result['name'],
				'status'     => $result['status'],			
				'status_text'     => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),		
				'selected'   => isset($this->request->post['selected']) && in_array($result['attemplate_id'], $this->request->post['selected']),
				'edit'		=> $this->url->link('extension/module/attemplate/update', 'user_token=' . $this->session->data['user_token'] . '&attemplate_id=' . $result['attemplate_id'] . $url, true)
			);
		}	
	
 		$data['user_token'] = $this->session->data['user_token'];
		
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$url = '';
		
		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		$data['sort_name'] = $this->url->link('extension/module/attemplate', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);		
		$data['sort_status'] = $this->url->link('extension/module/attemplate', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $attemplate_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/module/attemplate', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);
			
		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($attemplate_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($attemplate_total - $this->config->get('config_limit_admin'))) ? $attemplate_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $attemplate_total, ceil($attemplate_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/attemplate_list', $data));		
	}

	protected function getForm() {
		
		$data['module_atpresets_overwrite_general'] = $this->config->get('module_atpresets_overwrite_general');
		$data['module_atpresets_overwrite_content'] = $this->config->get('module_atpresets_overwrite_content');
		
		$data['button_remove'] = $this->language->get('button_remove');
		$data['button_attribute_add'] = $this->language->get('button_attribute_add');
		
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
 		
 		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		$url = '';
	
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
				
   		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true)
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/attemplate', 'user_token=' . $this->session->data['user_token'] . $url, true)
   		);
										
		if (!isset($this->request->get['attemplate_id'])) { 
			$data['action'] = $this->url->link('extension/module/attemplate/insert', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('extension/module/attemplate/update', 'user_token=' . $this->session->data['user_token'] . '&attemplate_id=' . $this->request->get['attemplate_id'] . $url, true);
		}
		
		$data['cancel'] = $this->url->link('extension/module/attemplate', 'user_token=' . $this->session->data['user_token'] . $url, true);
		
		$data['attemplate_id'] ='0';
		
		if (isset($this->request->get['attemplate_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$attemplate_info = $this->model_extension_atpresets_attemplate->getAttemplate($this->request->get['attemplate_id']);
			$data['attemplate_id'] = $attemplate_info['attemplate_id'];			
		}

		$data['user_token'] = $this->session->data['user_token'];
		$data['categories2'] = array();
		$data['products_assigned'] = array();

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($attemplate_info)) {
			$data['name'] = $attemplate_info['name'];
		} else {
			$data['name'] = '';
		}
		
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($attemplate_info)) {
			$data['status'] = $attemplate_info['status'];
		} else {
			$data['status'] = '1';
		}

		// Attributes
		$this->load->model('catalog/attribute');

		if (isset($this->request->post['attributes'])) {
			$attributes = $this->request->post['attributes'];
		} elseif (isset($this->request->get['attemplate_id'])) {
			$attributes = $this->model_extension_atpresets_attemplate->getAttemplateAttributes($this->request->get['attemplate_id']);
		} else {
			$attributes = array();
		}

		$data['attributes'] = array();

		foreach ($attributes as $attribute) {
			$attribute_info = $this->model_catalog_attribute->getAttribute($attribute['attribute_id']);

			$preset_id = array();
			if (count($attribute['preset_id']) == 1) {
				$this->load->model('extension/atpresets/atpresets');
				$preset_id = $attribute['preset_id'];
				$attribute['preset'] = html_entity_decode($this->model_extension_atpresets_atpresets->getPresetTitle($attribute['preset_id'][0]),ENT_QUOTES, 'UTF-8');
			} else if (count($attribute['preset_id']) > 1) {
				$preset_id = $attribute['preset_id'];				
				$attribute['preset'] = '';
			} else {
				$attribute['preset'] = '';
			}
			
			if ($attribute_info) {
				$data['attributes'][] = array(
					'attribute_id'                  => $attribute['attribute_id'],
					'preset_id'                  	=> $preset_id,
					'allow_multiple'             	=> count($preset_id)>1 && $this->config->get('module_atpresets_allow_multiple'),
					'preset'                  	 	=> $attribute['preset'],
					'preset_esc'                  	=> htmlentities($attribute['preset'],ENT_QUOTES),
					'name'                          => $attribute_info['name']
				);
			}
		}

		$data['module_atpresets_installed'] = $this->config->get('module_atpresets_installed');
		$data['module_atpresets_allow_multiple'] = $this->config->get('module_atpresets_allow_multiple');		
		if ($data['module_atpresets_allow_multiple']==1)
			$data['module_atpresets_selecttype'] = 1;
		else 
			$data['module_atpresets_selecttype'] = $this->config->get('module_atpresets_selecttype');
		
		if ($data['module_atpresets_selecttype']==1) {
			$data['all_presets'] = array();
			
			$this->load->model('extension/atpresets/atpresets');

			$results = $this->model_extension_atpresets_atpresets->getPresetsAuto(array('attribute_id'=>''));

			if ($results) {
				foreach ($results as $result) {
					$text = (utf8_strlen($result['text'])>50)?substr($result['text'], 0, 50).'...':$result['text'];
					$data['all_presets'][] = array(
						'preset_id'			=> $result['preset_id'],
						'text'				=> $text,
						'text_esc'			=> htmlentities(html_entity_decode($text,ENT_QUOTES, 'UTF-8'),ENT_QUOTES),
						'text_esc2'			=> htmlentities(html_entity_decode(str_replace(array("\r\n", "\n", "\r"), '', $text),ENT_QUOTES, 'UTF-8'),ENT_QUOTES),
						'attribute_name'	=> $result['name'],
						'attribute_name_esc'=> htmlentities($result['name'],ENT_QUOTES),
						'attribute_id'		=> $result['attribute_id']					
					);
				}
			}
		} else {
			$data['all_presets'] = null;
		}
			
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/attemplate_form', $data));			
	}
	
	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'extension/module/attemplate')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 1) || (utf8_strlen($this->request->post['name']) > 96)) {
			$this->error['name'] = $this->language->get('error_name');
		}
		
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}			
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'extension/module/attemplate')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}	
	
	public function update_attributes() {
		
		$json = array();
		$json['product_attributes'] = array();

		$this->load->model('catalog/product');
		$this->load->model('catalog/attribute');
		$this->load->model('extension/atpresets/attemplate');
			
		if ($this->request->get['option'] != 0 && $this->request->get['attemplate_id'] != -1) {
			if ($this->request->get['option'] != 3) {
				//get template attributes and current attributes
				$attributes = $this->model_extension_atpresets_attemplate->getAttemplateAttributes($this->request->get['attemplate_id']);
			} else {
				$results =$this->model_catalog_attribute->getAttributes(array('filter_attribute_group_id'=>$this->request->get['attemplate_id']));
				$attributes = array();
				foreach ($results as $result) {
					$attributes[] = array(
						'attribute_id'               => $result['attribute_id'],
						'preset_id'                  => array()					
					);					
				}
			}			
			if (isset($this->request->post['product_attribute']))
				$old_attributes = $this->request->post['product_attribute'];
			else
				$old_attributes = array();
			
			//remove attributes from the form that don't belong to the template
			if ($this->request->get['option'] == 2 && !empty($attributes)) {
				$old_attributes = array();
				/*foreach ($old_attributes as $key => $old_attribute) {
					$exists = false;
					foreach ($attributes as $attribute) {
						if ($attribute['attribute_id'] == $old_attribute['attribute_id'])
							$exists = true;
					}
					if (!$exists) unset($old_attributes[$key]);
				}*/					
			}
			
			//remove template attributes that are already in the form
			foreach ($attributes as $key => $attribute) {
				foreach ($old_attributes as $old_attribute) {
					if ($attribute['attribute_id'] == $old_attribute['attribute_id'])
						unset($attributes[$key]);
				}
			}		
			$this->load->model('extension/atpresets/atpresets');
			//prepare template attributes for the response
			foreach ($attributes as $attribute) {
				$attribute_info = $this->model_catalog_attribute->getAttribute($attribute['attribute_id']);

				$preset_id = array();
				if (count($attribute['preset_id']) == 1) {
					
					$preset_id = $attribute['preset_id'];
					$attribute['preset'] = html_entity_decode($this->model_extension_atpresets_atpresets->getPresetTitle($attribute['preset_id'][0]),ENT_QUOTES, 'UTF-8');
				} else if (count($attribute['preset_id']) > 1) {
					$preset_id = $attribute['preset_id'];				
					$attribute['preset'] = '';
				
				} else {
					$attribute['preset'] = '';
				}

				$preset = array();
				$presets_text = $this->model_extension_atpresets_atpresets->getManyPresetsTexts($preset_id);
				foreach ($presets_text as $texts) {
					foreach ($texts as $key => $text) {
						if (!isset($preset[$key])) {
							$preset[$key] = array();
							$preset[$key]['text'] = $text;
						} else {
							$preset[$key]['text'] .= ', '.$text;
						}
					}
				}	
						
				if ($attribute_info) {
					$json['product_attributes'][] = array(
						'attribute_id'                  => $attribute['attribute_id'],
						'preset_id'                  	=> $preset_id,
						'allow_multiple'             	=> count($preset_id)>1 && $this->config->get('module_atpresets_allow_multiple'),
						'preset'                  	 	=> $attribute['preset'],
						'name'                          => $attribute_info['name'],
						'product_attribute_description' => $preset
					);
				}
			}
			//prepare old form attributes for the response
			foreach ($old_attributes as $old_attribute) {
				$attribute_info = $this->model_catalog_attribute->getAttribute($old_attribute['attribute_id']);

				$preset_id = array();
				if (count($old_attribute['preset_id']) == 1) {
					$this->load->model('extension/atpresets/atpresets');
					$preset_id = $old_attribute['preset_id'];
					$old_attribute['preset'] = html_entity_decode($this->model_extension_atpresets_atpresets->getPresetTitle($old_attribute['preset_id'][0]),ENT_QUOTES, 'UTF-8');
				} else if (count($old_attribute['preset_id']) > 1) {
					$preset_id = $old_attribute['preset_id'];				
					$old_attribute['preset'] = '';
				} else {
					$old_attribute['preset'] = '';
				}
				if (!isset($old_attribute['allow_multiple']))
					$old_attribute['allow_multiple'] = 0;
				foreach ($old_attribute['product_attribute_description'] as $key=>$text) {
					$old_attribute['product_attribute_description'][$key]['text'] = html_entity_decode($old_attribute['product_attribute_description'][$key]['text']);
				}							
				if ($attribute_info) {
					$json['product_attributes'][] = array(
						'attribute_id'                  => $old_attribute['attribute_id'],
						'preset_id'                  	=> $old_attribute['preset_id'],
						'allow_multiple'             	=> $old_attribute['allow_multiple'],
						'preset'                  	 	=> $old_attribute['preset'],
						'name'                          => $attribute_info['name'],
						'product_attribute_description' => $old_attribute['product_attribute_description']
					);
				}
			}
			
		} else if ($this->request->get['option'] == 0) {
			//get attributes already stored in the database for that product
			$product_attributes = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);			
			
			foreach ($product_attributes as $product_attribute) {
				$preset_id = array();
				if (count($product_attribute['preset_id']) == 1) {
					$this->load->model('extension/atpresets/atpresets');
					$preset_id = $product_attribute['preset_id'];
					$product_attribute['preset'] = html_entity_decode($this->model_extension_atpresets_atpresets->getPresetTitle($product_attribute['preset_id'][0]),ENT_QUOTES, 'UTF-8');
				} else if (count($product_attribute['preset_id']) > 1) {
					$preset_id = $product_attribute['preset_id'];						
					$product_attribute['preset'] = '';
				} else {
					$product_attribute['preset'] = '';
				}				
				$attribute_info = $this->model_catalog_attribute->getAttribute($product_attribute['attribute_id']);
				foreach ($product_attribute['product_attribute_description'] as $key=>$text) {
					$product_attribute['product_attribute_description'][$key]['text'] = html_entity_decode($product_attribute['product_attribute_description'][$key]['text'],ENT_QUOTES, 'UTF-8');
				}
				if ($attribute_info) {
					$json['product_attributes'][] = array(
						'attribute_id'                  => $product_attribute['attribute_id'],
						'preset_id'                  	=> $preset_id,
						'allow_multiple'             	=> count($preset_id)>1 && $this->config->get('module_atpresets_allow_multiple'),
						'preset'                  	 	=> $product_attribute['preset'],						
						'name'                          => $attribute_info['name'],
						'product_attribute_description' => $product_attribute['product_attribute_description']
					);
				}
			}
		}
		
		$this->response->setOutput(json_encode($json));
	}
	
	public function save_attemplate() {
		
		$json = array();		
		$this->load->language('extension/module/attemplate');
		
		if (!$this->user->hasPermission('modify', 'extension/module/attemplate')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			if ((utf8_strlen($this->request->post['new_attemplate_name']) < 1) || (utf8_strlen($this->request->post['new_attemplate_name']) > 96)) {
				$json['error'] = $this->language->get('error_name');
			}		
			if (!isset($this->request->post['product_attribute'])) {
				$json['error'] = $this->language->get('error_no_attributes');
			}							
		}

		if (!isset($json['error'])) {
			$data = array();
			$this->load->model('extension/atpresets/attemplate');			
			
			$data['name'] = $this->request->post['new_attemplate_name'];
			$data['status'] = '1';
			$data['attributes'] = $this->request->post['product_attribute'];

			$attemplate_id = $this->model_extension_atpresets_attemplate->addAttemplate($data);
			if ($attemplate_id)
				$json['success'] = $this->language->get('text_success_new');
		}
		$this->response->setOutput(json_encode($json));
	}

	public function install() {   
		$this->load->model('extension/attemplate/install');
		$this->model_extension_attemplate_install->install();	
	}
	
	public function uninstall() {   
		$this->load->model('extension/attemplate/install');
		$this->model_extension_attemplate_install->uninstall();		
	}	
}
