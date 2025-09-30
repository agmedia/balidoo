<?php 
class ControllerExtensionPaymentKeksPay extends Controller {
	private $error = array(); 

	public function index() {
		$this->load->language('extension/payment/kekspay');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_kekspay', $this->request->post);				

			$this->session->data['success'] = $this->language->get('text_success');

          $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}
        
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        if (isset($this->error['cid'])) {
            $data['error_cid'] = $this->error['cid'];
        } else {
            $data['error_cid'] = '';
        }
        if (isset($this->error['tid'])) {
            $data['error_tid'] = $this->error['tid'];
        } else {
            $data['error_tid'] = '';
        }
        if (isset($this->error['password'])) {
            $data['error_password'] = $this->error['password'];
        } else {
            $data['error_password'] = '';
        }

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
        $data['text_successful'] = $this->language->get('text_successful');
        $data['text_declined'] = $this->language->get('text_declined');
        $data['text_off'] = $this->language->get('text_off');

        $data['text_edit'] = $this->language->get('text_edit');
        $data['help_entry_callback'] = $this->language->get('help_entry_callback');
        $data['help_entry_total'] = $this->language->get('help_entry_total');
        
        $data['entry_cid'] = $this->language->get('entry_cid');
        $data['entry_tid'] = $this->language->get('entry_tid');
		$data['entry_password'] = $this->language->get('entry_password');
        $data['entry_callback'] = $this->language->get('entry_callback');
        $data['entry_shop_title'] = $this->language->get('entry_shop_title');
		$data['entry_test'] = $this->language->get('entry_test');
		$data['entry_total'] = $this->language->get('entry_total');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/kekspay', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/kekspay', 'user_token=' . $this->session->data['user_token'], true);

		
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);
        
        if (isset($this->request->post['payment_kekspay_cid'])) {
            $data['payment_kekspay_cid'] = $this->request->post['payment_kekspay_cid'];
        } else {
            $data['payment_kekspay_cid'] = $this->config->get('payment_kekspay_cid');
        }
        
        if (isset($this->request->post['payment_kekspay_tid'])) {
            $data['payment_kekspay_tid'] = $this->request->post['payment_kekspay_tid'];
        } else {
            $data['payment_kekspay_tid'] = $this->config->get('payment_kekspay_tid');
        }
        
        if (isset($this->request->post['payment_kekspay_password'])) {
            $data['payment_kekspay_password'] = $this->request->post['payment_kekspay_password'];
        } else {
            $data['payment_kekspay_password'] = $this->config->get('payment_kekspay_password');
        }
        
        if (isset($this->request->post['payment_kekspay_shop_title'])) {
            $data['payment_kekspay_shop_title'] = $this->request->post['payment_kekspay_shop_title'];
        } else {
            $data['payment_kekspay_shop_title'] = $this->config->get('payment_kekspay_shop_title');
        }

		$data['callback'] = HTTP_CATALOG . 'index.php?route=extension/payment/kekspay/callback';

		if (isset($this->request->post['payment_kekspay_test'])) {
			$data['payment_kekspay_test'] = $this->request->post['payment_kekspay_test'];
		} else {
			$data['payment_kekspay_test'] = $this->config->get('payment_kekspay_test');
		}


		if (isset($this->request->post['payment_kekspay_total'])) {
			$data['payment_kekspay_total'] = $this->request->post['payment_kekspay_total'];
		} else {
			$data['payment_kekspay_total'] = $this->config->get('payment_kekspay_total');
		} 

		if (isset($this->request->post['payment_kekspay_order_status_id'])) {
			$data['payment_kekspay_order_status_id'] = $this->request->post['payment_kekspay_order_status_id'];
		} else {
			$data['payment_kekspay_order_status_id'] = $this->config->get('payment_kekspay_order_status_id');
		} 

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_kekspay_geo_zone_id'])) {
			$data['payment_kekspay_geo_zone_id'] = $this->request->post['payment_kekspay_geo_zone_id'];
		} else {
			$data['payment_kekspay_geo_zone_id'] = $this->config->get('payment_kekspay_geo_zone_id');
		} 

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_kekspay_status'])) {
			$data['payment_kekspay_status'] = $this->request->post['payment_kekspay_status'];
		} else {
			$data['payment_kekspay_status'] = $this->config->get('payment_kekspay_status');
		}

		if (isset($this->request->post['payment_kekspay_sort_order'])) {
			$data['payment_kekspay_sort_order'] = $this->request->post['payment_kekspay_sort_order'];
		} else {
			$data['payment_kekspay_sort_order'] = $this->config->get('payment_kekspay_sort_order');
		}

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/kekspay', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/kekspay')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
        
        if (!$this->request->post['payment_kekspay_cid']) {
            $this->error['cid'] = $this->language->get('error_cid');
        }
        
        if (!$this->request->post['payment_kekspay_tid']) {
            $this->error['tid'] = $this->language->get('error_tid');
        }

		/*if (!$this->request->post['payment_kekspay_password']) {
			$this->error['password'] = $this->language->get('error_password');
		}*/

        return !$this->error;

		/*if (!$this->error) {
			return true;
		} else {
			return false;
		}*/
	}
}
?>