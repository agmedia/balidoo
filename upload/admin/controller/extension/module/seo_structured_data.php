<?php
class ControllerExtensionModuleSeoStructuredData extends Controller {
	private $error = array();
	
	private $eventGroup = "module_seo_structured_data";

	public function install() {
		$this->load->model("setting/event");
		$this->model_setting_event->addEvent($this->eventGroup, "catalog/view/common/header/before", "extension/module/seo_structured_data/seoStructuredDataPrepare");
		$this->model_setting_event->addEvent($this->eventGroup, "catalog/view/common/header/after", "extension/module/seo_structured_data/seoStructuredDataRender");
	}
	
	public function uninstall() {
		$this->load->model("setting/event");
		$this->model_setting_event->deleteEventByCode($this->eventGroup);
	}

	public function index() {
		$this->load->language('extension/module/seo_structured_data');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_seo_structured_data', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			if(!empty($this->request->post['save_and_stay'])) {
				$this->response->redirect($this->url->link('extension/module/seo_structured_data', 'user_token=' . $this->session->data['user_token'], true));
			} else {
				$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
			}
		}

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
			'href' => $this->url->link('extension/module/seo_structured_data', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/seo_structured_data', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_seo_structured_data_status'])) {
			$data['module_seo_structured_data_status'] = $this->request->post['module_seo_structured_data_status'];
		} else {
			$data['module_seo_structured_data_status'] = $this->config->get('module_seo_structured_data_status');
		}

		if (isset($this->request->post['module_seo_structured_data_fb_app_id'])) {
			$data['module_seo_structured_data_fb_app_id'] = $this->request->post['module_seo_structured_data_fb_app_id'];
		} else {
			$data['module_seo_structured_data_fb_app_id'] = $this->config->get('module_seo_structured_data_fb_app_id');
		}
		
		if (isset($this->request->post['module_seo_structured_data_twitter_creator'])) {
			$data['module_seo_structured_data_twitter_creator'] = $this->request->post['module_seo_structured_data_twitter_creator'];
		} else {
			$data['module_seo_structured_data_twitter_creator'] = $this->config->get('module_seo_structured_data_twitter_creator');
		}

		if (isset($this->request->post['module_seo_structured_data_width'])) {
			$data['module_seo_structured_data_width'] = $this->request->post['module_seo_structured_data_width'];
		} else {
			$data['module_seo_structured_data_width'] = $this->config->get('module_seo_structured_data_width');
		}

		if (isset($this->request->post['module_seo_structured_data_height'])) {
			$data['module_seo_structured_data_height'] = $this->request->post['module_seo_structured_data_height'];
		} else {
			$data['module_seo_structured_data_height'] = $this->config->get('module_seo_structured_data_height');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/seo_structured_data', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/seo_structured_data')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}