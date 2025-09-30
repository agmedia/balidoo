<?php
class ControllerExtensionTotaltieredgiftstotal extends Controller {
	private $totalName;
    private $totalPath;
    private $totalsLink;
    private $tokek;
    private $error = array(); 
    private $data = array();
	

	public function __construct($registry) {
        parent::__construct($registry);
        $this->config->load('isenselabs/tieredgifts');

        // Module VERSION
        $this->moduleVersion = $this->config->get('tieredgifts_moduleVersion');        
        
		//total/tieredgiftstotal

        $this->token = $this->config->get('tieredgifts_token') . '=' . $this->session->data[$this->config->get('tieredgifts_token')];

        /* OC version-specific declarations - Begin */
        $this->totalName        = $this->config->get('tieredgifts_totalName');        
        $this->totalPath        = $this->config->get('tieredgifts_totalPath');
        $this->totalsLink    	= $this->url->link($this->config->get('tieredgifts_totalsLink'), $this->token.$this->config->get('tieredgifts_totalsLink_type'), 'SSL');
        /* OC version-specific declarations - End */
    }

	public function index() {

		$this->load->language($this->totalPath);
        
        $this->load->model('setting/setting');

		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting($this->TotalName, $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->totalsLink);
		}

		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

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
			'href' => $this->url->link('common/dashboard', $this->token, 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_total'),
			'href' => $this->totalsLink
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->totalPath, $this->token, 'SSL')
		);

		$data['action'] = $this->url->link($this->totalPath, $this->token, 'SSL');

		$data['cancel'] = $this->totalsLink;

		if (isset($this->request->post[$this->totalName.'_status'])) {
			$data['status'] = $this->request->post[$this->totalName.'_status'];
		} else {
			$data['status'] = $this->config->get($this->totalName.'_status');
		}

		if (isset($this->request->post[$this->totalName.'_sort_order'])) {
			$data['sort_order'] = $this->request->post[$this->totalName.'_sort_order'];
		} else {
			$data['sort_order'] = $this->config->get($this->totalName.'_sort_order');
		}

		$data['total_status_input_name'] = $this->totalName.'_status';
		$data['total_sort_order_input_name'] = $this->totalName.'_sort_order';

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->totalPath, $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', $this->totalPath)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}