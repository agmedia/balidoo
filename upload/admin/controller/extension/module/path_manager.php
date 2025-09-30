<?php

/*
This file is part of "Path Manager - Breadcrumbs" project and subject to the terms
and conditions defined in file "EULA.txt", which is part of this source
code package and also available on the project page: https://git.io/JfeVl.
*/

class ControllerExtensionModulePathManager extends Controller {
	private $error = array();
	private $paths = array('default', 'direct', 'short', 'long', 'last', 'manufacturer');

	public function index() {
		$this->load->language('extension/module/path_manager');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (('POST' == $this->request->server['REQUEST_METHOD']) && $this->validate()) {
			$this->model_setting_setting->editSetting('module_path_manager', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link(
				'marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
			);
		}

		if (isset($this->error['permission'])) {
			$data['error_permission'] = $this->error['permission'];
		} else {
			$data['error_permission'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link(
				'marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true
			),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link(
				'extension/module/path_manager',
				'user_token=' . $this->session->data['user_token'], true
			),
		);

		$data['action'] = $this->url->link(
			'extension/module/path_manager', 'user_token=' . $this->session->data['user_token'], true
		);

		$data['cancel'] = $this->url->link(
			'marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true
		);

		if (isset($this->request->post['module_path_manager_status'])) {
			$data['status'] = $this->request->post['module_path_manager_status'];
		} else {
			$data['status'] = $this->config->get('module_path_manager_status');
		}

		if (isset($this->request->post['module_path_manager'])) {
			$data['path_manager'] = $this->request->post['module_path_manager'];
		} else {
			$data['path_manager'] = $this->config->get('module_path_manager');
		}

		if (!method_exists($this->document, 'addCustomScript') || !method_exists($this->document, 'getCustomScripts')) {
			$data['path_manager']['breadcrumbs']['json'] = false;
			$data['breadcrumbs_json_disabled'] = true;
		} else {
			if (isset($this->request->post['module_path_manager']['breadcrumbs']['json'])) {
				$data['path_manager']['breadcrumbs']['json'] = $this->request->post['module_path_manager']['breadcrumbs']['json'];
			} elseif (isset($this->config->get('module_path_manager')['breadcrumbs']['json'])){
				$data['path_manager']['breadcrumbs']['json'] = $this->config->get('module_path_manager')['breadcrumbs']['json'];
			} else {
				$data['path_manager']['breadcrumbs']['json'] = false;
			}
		}

		$data['paths'] = $this->paths;

		$data['heading_title'] = $this->language->get('heading_title');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/path_manager', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/path_manager')) {
			$this->error['permission'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function install() {
		$dir_stylesheet = DIR_CATALOG . 'view/theme/' .
			$this->config->get('theme_directory') . $this->config->get('config_theme') . '/stylesheet/';

		if ($dir_stylesheet) {
			$css_bold = $dir_stylesheet . 'breadcrumbs_bold.css';
			$css_nolink = $dir_stylesheet . 'breadcrumbs_nolink.css';

			if (!file_exists($css_bold)) {
				$css_text = "ul.breadcrumb li:last-child a {\n\tfont-weight: bold;\n}";

				file_put_contents($css_bold, $css_text, FILE_USE_INCLUDE_PATH);
			}

			if (!file_exists($css_nolink)) {
				$css_text = "ul.breadcrumb li:last-child a {\n\tcursor: default!important;\n\tpointer-events: none;\n\tcolor: inherit;\n}";

				file_put_contents($css_nolink, $css_text, FILE_USE_INCLUDE_PATH);
			}
		}

		$this->load->model('setting/event');

		$this->model_setting_event->deleteEventByCode('path_manager_update_breadcrumbs');
		$this->model_setting_event->deleteEventByCode('path_manager_style_breadcrumbs');
		$this->model_setting_event->deleteEventByCode('path_manager_add_jsonld');

		$this->model_setting_event->addEvent('path_manager_update_breadcrumbs', 'catalog/view/*/before', 'extension/module/path_manager/updateBreadcrumbs');
		$this->model_setting_event->addEvent('path_manager_style_breadcrumbs', 'catalog/controller/common/header/before', 'extension/module/path_manager/styleBreadcrumbs');
		$this->model_setting_event->addEvent('path_manager_add_jsonld', 'catalog/view/*/after', 'extension/module/path_manager/addJsonLdScript');
	}

	public function uninstall() {
		$dir_stylesheet = DIR_CATALOG . 'view/theme/' .
			$this->config->get('theme_directory') . $this->config->get('config_theme') . '/stylesheet/';

		if ($dir_stylesheet) {
			$css_bold = $dir_stylesheet . 'breadcrumb_bold.css';
			$css_nolink = $dir_stylesheet . 'breadcrumb_nolink.css';

			if (file_exists($css_bold)) {
				unlink($css_bold);
			}

			if (file_exists($css_nolink)) {
				unlink($css_nolink);
			}
		}

		$this->load->model('setting/event');

		$this->model_setting_event->deleteEventByCode('path_manager_update_breadcrumbs');
		$this->model_setting_event->deleteEventByCode('path_manager_style_breadcrumbs');
		$this->model_setting_event->deleteEventByCode('path_manager_add_jsonld');
	}
}
