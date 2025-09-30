<?php
class ControllerExtensionModuleTieredgifts extends Controller {

    private $moduleData_module = 'tieredgifts_module';

    private $moduleModel;
    private $moduleName;
    private $modulePath;
    private $callModel;
    private $moduleVersion;
    private $extensionLink;
    private $token;
    private $moduleSettings;

    private $event_code = 'tieredgifts_event';

    private $error = array();

    public function __construct($registry) {
        parent::__construct($registry);

        $this->config->load('isenselabs/tieredgifts');

        $this->moduleName = $this->config->get('tieredgifts_moduleName');
        $this->modulePath = $this->config->get('tieredgifts_modulePath');
        $this->moduleVersion = $this->config->get('tieredgifts_moduleVersion');

        $this->token = $this->config->get('tieredgifts_token') . '=' . $this->session->data[$this->config->get('tieredgifts_token')];

        $this->extensionLink = $this->url->link($this->config->get('tieredgifts_extensionLink'), $this->token.$this->config->get('tieredgifts_extensionLink_type') , 'SSL');

        $this->load->model($this->modulePath);
        if (!$this->request->get['route'] == 'catalog/product') {
            $this->load->language($this->modulePath);
        }

        $this->load->model('tool/image');

        $this->callModel = $this->config->get('tieredgifts_callModel');
        $this->moduleModel = $this->{$this->callModel};

        //Module loaders
        $this->load->model('setting/store');
        $this->load->model('localisation/language');
        $this->load->model('design/layout');

        $this->load->model($this->config->get('tieredgifts_setting_model_path'));
        $this->moduleSettings = $this->{$this->config->get('tieredgifts_setting_model')};

        // Exclude update from module install / uninstall
        if (strpos($this->request->get['route'], 'extension/extension/module/') === false) {
            $this->update();
        }
    }

    private function hookEvents() {
        $this->load->model('setting/event');
        $this->model_setting_event->addEvent($this->event_code, "catalog/*/before", $this->modulePath . '/customUrlFunctionality');
        $this->model_setting_event->addEvent($this->event_code, 'catalog/view/checkout/cart/before', $this->modulePath . '/modifyGiftsPriceAndShowNotification');
        $this->model_setting_event->addEvent($this->event_code, 'catalog/view/common/cart/before', $this->modulePath . '/modifyGiftsPriceAndShowNotification');
         $this->model_setting_event->addEvent($this->event_code, 'catalog/view/checkout/confirm/before', $this->modulePath . '/modifyGiftsPriceAndShowNotification');
        $this->model_setting_event->addEvent($this->event_code, 'catalog/view/checkout/checkout/before', $this->modulePath . '/modifyGiftsPriceAndShowNotification');
        $this->model_setting_event->addEvent($this->event_code, 'catalog/view/checkout/checkout/before', $this->modulePath . '/redirectToListingIfNeeded');
        $this->model_setting_event->addEvent($this->event_code, 'catalog/view/journal2/checkout/checkout/before', $this->modulePath . '/redirectToListingIfNeeded');
        $this->model_setting_event->addEvent($this->event_code, 'catalog/view/journal3/checkout/checkout/before', $this->modulePath . '/redirectToListingIfNeeded');
		$this->model_setting_event->addEvent($this->event_code, 'catalog/view/extension/quickcheckout/checkout/before', $this->modulePath . '/redirectToListingIfNeeded');

        $this->model_setting_event->addEvent($this->event_code . '_mainmenu', 'catalog/view/common/menu/before', $this->modulePath . '/modifyMenuLink');
        $this->model_setting_event->addEvent($this->event_code . '_autocomplete', 'admin/model/catalog/product/getProducts/before', $this->modulePath . '/modifyAutocompleteModelParams');
        $this->model_setting_event->addEvent($this->event_code . '_autocomplete', 'admin/model/catalog/product/getProducts/after', $this->modulePath . '/modifyAutocompleteModelResults');
    }

    private function deleteEvents() {
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode($this->event_code);

        $this->model_setting_event->deleteEventByCode($this->event_code . '_mainmenu');
        $this->model_setting_event->deleteEventByCode($this->event_code . '_autocomplete');
    }

    public function index() {

        $this->load->language($this->modulePath);

        $data['moduleName'] = $this->moduleName;
        $data['moduleNameSmall'] = $this->moduleName;
        $data['modulePath'] = $this->modulePath;
        $data['moduleModel'] = $this->callModel;

        $this->load->model('catalog/product');
        $this->load->model('catalog/category');

        $this->document->addStyle('view/stylesheet/'.$this->moduleName.'/'.$this->moduleName.'.css');
        $this->document->addScript('view/javascript/'.$this->moduleName.'/nprogress.js');

        $this->document->setTitle($this->language->get('heading_title'));

        if(!isset($this->request->get['store_id'])) {
           $this->request->get['store_id'] = 0;
        }

        $store = $this->getCurrentStore($this->request->get['store_id']);

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            if (!empty($_POST['OaXRyb1BhY2sgLSBDb21'])) {
                $this->request->post[$this->moduleName]['LicensedOn'] = $_POST['OaXRyb1BhY2sgLSBDb21'];
            }
            if (!empty($_POST['cHRpbWl6YXRpb24ef4fe'])) {
                $this->request->post[$this->moduleName]['License'] = json_decode(base64_decode($_POST['cHRpbWl6YXRpb24ef4fe']), true);
            }
            if ($this->request->post[$this->moduleName]['Enabled'] == 'yes'){
                $this->model_setting_setting->editSetting('module_'.(strtolower($this->moduleName)), array('module_'.(strtolower($this->moduleName)).'_status' => 1));
            } else{
                $this->model_setting_setting->editSetting('module_'.(strtolower($this->moduleName)), array('module_'.(strtolower($this->moduleName)).'_status' => 0));
            }
            $this->model_setting_setting->editSetting($this->moduleName, $this->request->post, $this->request->get['store_id']);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link($this->modulePath, 'store_id='.$this->request->get['store_id'] . '&'.$this->token, 'SSL'));
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs']   = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->token, 'SSL'),
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('marketplace/extension', $this->token . '&type=module', 'SSL'),
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link($this->modulePath, $this->token, 'SSL'),
        );

        $data['heading_title']              = $this->language->get('heading_title').' '.$this->moduleVersion;

        $data['currency']                   = $this->config->get('config_currency');
        $data['stores']                     = array_merge(array(0 => array('store_id' => '0', 'name' => $this->config->get('config_name') . ' (' . $this->language->get('text_default').')', 'url' => HTTP_SERVER, 'ssl' => HTTPS_SERVER)), $this->model_setting_store->getStores());
        $data['languages']                  = $this->model_localisation_language->getLanguages();

        foreach ($data['languages'] as $key => $value) {
            $data['languages'][$key]['flag_url'] = 'language/'.$data['languages'][$key]['code'].'/'.$data['languages'][$key]['code'].'.png"';
        }

        $data['totalCats'] = 0;
        foreach ($this->model_catalog_category->getCategories() as $key => $value) {
            if($value['parent_id'] == 0) $data['totalCats']++;
        }

        $data['tabs']                   = $this->getTabs();

        $data['language_id']                = $this->config->get('config_language_id');
        $data['store']                      = $store;
        $data['token']                      = $this->token;
        $data['action']                     = $this->url->link($this->modulePath, $this->token, 'SSL');
        $data['cancel']                     = $this->extensionLink;

            $moduleSettings                     = $this->model_setting_setting->getSetting($this->moduleName, $store['store_id']);

        $moduleData                         = (isset($moduleSettings[$this->moduleName])) ? $moduleSettings[$this->moduleName] : array();

        if (!empty($moduleData['GiftCategory'])) {
            foreach ($moduleData['GiftCategory'] as $key => &$setting) {
                if(isset($setting['GiftProducts'])) {
                    foreach ($setting['GiftProducts'] as &$product_data) {
                        $product_info = $this->model_catalog_product->getProduct($product_data);
                        if($product_info) {
                            $product_data = $product_info;
                        }
                    }
                }
            }
        }

        $data['moduleData'] = $moduleData;

		$data['model_catalog_product']      = $this->model_catalog_product;

        //support tab
        $data['unlicensedHtml'] = (empty($data['moduleData']['LicensedOn'])) ? base64_decode('ICAgIDxkaXYgY2xhc3M9ImFsZXJ0IGFsZXJ0LWRhbmdlciBmYWRlIGluIj4NCiAgICAgICAgPGJ1dHRvbiB0eXBlPSJidXR0b24iIGNsYXNzPSJjbG9zZSIgZGF0YS1kaXNtaXNzPSJhbGVydCIgYXJpYS1oaWRkZW49InRydWUiPsOXPC9idXR0b24+DQogICAgICAgIDxoND5XYXJuaW5nISBZb3UgYXJlIHJ1bm5pbmcgdW5saWNlbnNlZCB2ZXJzaW9uIG9mIHRoZSBtb2R1bGUhPC9oND4NCiAgICAgICAgPHA+WW91IGFyZSBydW5uaW5nIGFuIHVubGljZW5zZWQgdmVyc2lvbiBvZiB0aGlzIG1vZHVsZSEgWW91IG5lZWQgdG8gZW50ZXIgeW91ciBsaWNlbnNlIGNvZGUgdG8gZW5zdXJlIHByb3BlciBmdW5jdGlvbmluZywgYWNjZXNzIHRvIHN1cHBvcnQgYW5kIHVwZGF0ZXMuPC9wPjxkaXYgc3R5bGU9ImhlaWdodDo1cHg7Ij48L2Rpdj4NCiAgICAgICAgPGEgY2xhc3M9ImJ0biBidG4tZGFuZ2VyIiBocmVmPSJqYXZhc2NyaXB0OnZvaWQoMCkiIG9uY2xpY2s9IiQoJ2FbaHJlZj0jaXNlbnNlLXN1cHBvcnRdJykudHJpZ2dlcignY2xpY2snKSI+RW50ZXIgeW91ciBsaWNlbnNlIGNvZGU8L2E+DQogICAgPC9kaXY+') : '';
        $data['licenseDataBase64'] = !empty($data['moduleData']['License']) ? base64_encode(json_encode($data['moduleData']['License'])) : '';
        $data['supportTicketLink'] = 'http://isenselabs.com/tickets/open/' . base64_encode('Support Request') . '/' . base64_encode('392') . '/' . base64_encode($_SERVER['SERVER_NAME']);
        $data['LicenseExpireDate'] = !empty($data['moduleData']['License']) ? date("F j, Y", strtotime($data['moduleData']['License']['licenseExpireDate'])) : "";

        $hostname = (!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : '' ;
        $hostname = (strstr($hostname,'http://') === false) ? 'http://'.$hostname: $hostname;

        $data['hostname'] = $hostname;

        $data['base64_hostname'] = base64_encode($hostname);

        $data['time'] = time();

        $data['header']                     = $this->load->controller('common/header');
        $data['column_left']                = $this->load->controller('common/column_left');
        $data['footer']                     = $this->load->controller('common/footer');
		
		foreach ($data['tabs'] as $key => $tab) {
			$tab['template'] = str_replace('\\', '/', $tab['template']);
			$data['tabs'][$key]['content'] = $this->load->view($tab['template'], $data);
		}

        $this->response->setOutput($this->load->view($this->modulePath, $data));
    }

    private function modification_vqmod($file) {
        if (class_exists('VQMod')) {
            return VQMod::modCheck(modification($file), $file);
        } else {
            return modification($file);
        }
    }

    private function getTabs() {

        $dir = 'extension' . DIRECTORY_SEPARATOR . 'module' . DIRECTORY_SEPARATOR . 'tieredgifts' . DIRECTORY_SEPARATOR;

        $name_map = array(
            'tab_controlpanel' => array(
                'name' => 'Control Panel',
                'id' => 'tab-control-panel'
            ),
            'tab_giftcategories' => array(
                'name' => 'Gift Categories',
                'id' => 'tab-gift-categories'
            ),
            'tab_support' => array(
                'name' => 'Support',
                'id' => 'isense-support'
            ),
        );

        $tabs_data = array();

        foreach ($name_map as $file => $info) {
            $tabs_data[] = array(
                'file'     => $this->modification_vqmod($dir . $file . '.twig'),
				'template' => $this->modification_vqmod($dir . $file),
                'name' => $info['name'],
                'id' => $info['id']
            );
        }

        return $tabs_data;
    }

    protected function validateForm() {
        if (!$this->user->hasPermission('modify', $this->modulePath)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }

    public function get_gift_settings() {
        $this->load->language($this->modulePath);
		$data['languages'] = $this->model_localisation_language->getLanguages();

        foreach ($data['languages'] as $key => $value) {
            $data['languages'][$key]['flag_url'] = 'language/'.$data['languages'][$key]['code'].'/'.$data['languages'][$key]['code'].'.png"';
        }

        $data['language_id']                    = $this->config->get('config_language_id');
        $data['giftcategory']['id']             = $this->request->get['giftcategory_id'];
        $store_id                               = $this->request->get['store_id'];
        $data['data']                           = $this->model_setting_setting->getSetting($this->moduleName, $store_id);
        $data['moduleName']                     = $this->moduleName;
        $data['moduleData']                     = (isset($data['data'][$this->moduleName])) ? $data['data'][$this->moduleName] : array();
        $data['default_eligible']               = $this->language->get('default_eligible');
        $data['default_not_eligible']           = $this->language->get('default_not_eligible');
        $data['currency']                       = $this->config->get('config_currency');

        $data['newAddition']                    = true;

        $data['giftcategory_name'] = $data['moduleName'].'[GiftCategory]['.$this->request->get['giftcategory_id'].']';
        $data['giftcategory_data'] = array();

        $this->response->setOutput($this->load->view($this->modulePath.'/tab_gift', $data));
    }

    public function install() {
        $this->load->model($this->modulePath);
        $this->moduleModel->install();
        $this->hookEvents();
    }

    public function update() {
        $this->load->model('setting/event');

        $event = $this->model_setting_event->getEventByCode($this->event_code . '_mainmenu');
        if (empty($event)) {
            $this->model_setting_event->addEvent($this->event_code . '_mainmenu', 'catalog/view/common/menu/before', $this->modulePath . '/modifyMenuLink');
            $this->model_setting_event->addEvent($this->event_code . '_autocomplete', 'admin/model/catalog/product/getProducts/before', $this->modulePath . '/modifyAutocompleteModelParams');
            $this->model_setting_event->addEvent($this->event_code . '_autocomplete', 'admin/model/catalog/product/getProducts/after', $this->modulePath . '/modifyAutocompleteModelResults');
        }
    }

    public function uninstall() {
        $this->load->model($this->modulePath);
        $this->moduleModel->uninstall();
        $this->deleteEvents();
    }

    private function getCatalogURL() {

        if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
            $storeURL = HTTPS_CATALOG;
        } else {
            $storeURL = HTTP_CATALOG;
        }

        return $storeURL;

    }

    private function getServerURL() {

        if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
            $storeURL = HTTPS_SERVER;
        } else {
            $storeURL = HTTP_SERVER;
        }
        return $storeURL;

    }

    private function getCurrentStore($store_id) {

        if($store_id && $store_id != 0) {
            $store = $this->model_setting_store->getStore($store_id);
        } else {
            $store['store_id'] = 0;
            $store['name'] = $this->config->get('config_name');
            $store['url'] = $this->getCatalogURL();
        }

        return $store;

    }

    /**
     * Listen trigger: admin/model/catalog/product/getProducts/before
     */
    public function modifyAutocompleteModelParams(&$route, &$args)
    {
        if (isset($this->request->get['reqByTieredGifts'])) {
            $args[0]['filter_status'] = isset($this->request->get['filter_status']) ? $this->request->get['filter_status'] : 1;
        }
    }

    /**
     * Listen trigger: admin/model/catalog/product/autocomplete/after
     */
    public function modifyAutocompleteModelResults(&$route, &$args, &$output)
    {
        if (isset($this->request->get['reqByTieredGifts'])) {
            $data    = array();
            $results = $output;

            foreach ($results as $result) {
                if ($result['quantity'] < 1 ) {
                    continue;
                }

                $data[] = $result;
            }

            $output = $data;
        }
    }
}
