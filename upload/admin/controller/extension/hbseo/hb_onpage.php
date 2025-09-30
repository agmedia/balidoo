<?php
if (version_compare(VERSION,'3.0.0.0','>=' )) {
	define('TEMPLATE_FOLDER', 'oc3');
	define('EXTENSION_BASE', 'marketplace/extension');
	define('TOKEN_NAME', 'user_token');
	define('TEMPLATE_EXTN', '');
	define('EXTN_ROUTE', 'extension/hbseo');
}else if (version_compare(VERSION,'2.2.0.0','<=' )) {
	define('TEMPLATE_FOLDER', 'oc2');
	define('EXTENSION_BASE', 'extension/hbseo');
	define('TOKEN_NAME', 'token');
	define('TEMPLATE_EXTN', '.tpl');
	define('EXTN_ROUTE', 'hbseo');
}else{
	define('TEMPLATE_FOLDER', 'oc2');
	define('EXTENSION_BASE', 'extension/extension');
	define('TOKEN_NAME', 'token');
	define('TEMPLATE_EXTN', '');
	define('EXTN_ROUTE', 'extension/hbseo');
}
define('EXTN_VERSION', '8.2.2'); 
class ControllerExtensionHbseoHbOnpage extends Controller {
	
	private $error = array(); 
	
	public function index() {   
		$data['extension_version'] =  EXTN_VERSION;
		
		if (isset($this->request->get['store_id'])){
			$data['store_id'] = (int)$this->request->get['store_id'];
		}else{
			$data['store_id'] = 0;
		}
		
		$store_id = $data['store_id'];
		
		$this->load->language(EXTN_ROUTE.'/hb_onpage');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/hbseo/hb_onpage');
		$this->load->model('setting/setting');
		$this->load->model('setting/store');

		$data['stores'] = $this->model_setting_store->getStores();
		
		//Save the settings if the user has submitted the admin form (ie if someone has pressed save).
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('hb_onpage', $this->request->post, $this->request->get['store_id']);	
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link(EXTN_ROUTE.'/hb_onpage', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME].'&store_id='.$data['store_id'], true));
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		$text_strings = array(
				'heading_title','text_extension',
				'tab_dashboard','tab_product','tab_category','tab_brand','tab_information','tab_setting','tab_logs',
				'text_loading','button_save','button_cancel'
		);
		
		foreach ($text_strings as $text) {
			$data[$text] = $this->language->get($text);
		}
	
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME], true)
   		);
		
		$data['breadcrumbs'][] = array(
			'text' 		=> $this->language->get('text_extension'),
			'href' 		=> $this->url->link(EXTENSION_BASE, TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME] . '&type=hbseo', true)
		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link(EXTN_ROUTE.'/hb_onpage', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME].'&store_id='.$data['store_id'], true)
   		);
		
		$data['action'] 	= $this->url->link(EXTN_ROUTE.'/hb_onpage', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME].'&store_id='.$data['store_id'], true);
		
		$data['cancel'] 	= $this->url->link(EXTENSION_BASE, TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME] . '&type=hbseo', true);
		$data[TOKEN_NAME] 	= $this->session->data[TOKEN_NAME];
		$data['base_route'] = EXTN_ROUTE;
		
		$store_info 		= $this->model_setting_setting->getSetting('hb_onpage', $this->request->get['store_id']);

		//settings
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		$first_language = reset($data['languages']);
		$data['first_language'] = $first_language['language_id'];
	
		$data['hb_onpage_logs'] 		= isset($store_info['hb_onpage_logs'])?$store_info['hb_onpage_logs']:'';
		$data['hb_onpage_auto'] 		= isset($store_info['hb_onpage_auto'])?$store_info['hb_onpage_auto']:'';
		$data['hb_onpage_autolimit'] 	= isset($store_info['hb_onpage_autolimit'])?$store_info['hb_onpage_autolimit']:'500';
		$data['hb_onpage_authkey'] 		= isset($store_info['hb_onpage_authkey'])?$store_info['hb_onpage_authkey']:md5(rand());
		
		$this->load->model('setting/store');
		if ($data['store_id'] == 0){ 
			$store_url = HTTP_CATALOG;
		}else{
			 $results = $this->model_setting_store->getStore($data['store_id']);
			 $store_url = $results['url'];
		}
		$data['hb_onpage_cron'] =  'wget --quiet --delete-after "'.$store_url.'index.php?route=extension/hbseo/onpage_tags_generator/auto&authkey='.$data['hb_onpage_authkey'].'"';
		
		//LOGS
		if (!file_exists(DIR_LOGS . 'hb_seo_on_page_generator')) {
			mkdir(DIR_LOGS . 'hb_seo_on_page_generator', 0777, true);
		}
		if (isset($this->request->get['log'])){
			$data['filename'] = strtolower($this->request->get['log']);
		}else{
			$month = date('M').'-'.date('Y');
			$data['filename'] = strtolower($month).'.txt';
		}
		$data['all_files'] = array_diff(scandir(DIR_LOGS . 'hb_seo_on_page_generator'), array('.', '..'));
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/hbseo/'.TEMPLATE_FOLDER.'/hb_onpage'.TEMPLATE_EXTN, $data));

	}
	
	public function dashboard() { 
		$this->load->model('extension/hbseo/hb_onpage');
		$store_id = (int)$this->request->get['store_id'];
		
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		$data['product_elements'] = array(
			'meta_title' 		=> 'Meta Title',
			'meta_description'	=> 'Meta Description',
			'meta_keyword'		=> 'Meta Keyword',
			'h1'				=> 'H1 Tag',
			'h2'				=> 'H2 Tag',
			'image_alt'			=> 'Image Alt Tag',
			'image_title'		=> 'Image Title Tag'
		);
		
		$data['category_elements'] = array(
			'meta_title' 		=> 'Meta Title',
			'meta_description'	=> 'Meta Description',
			'meta_keyword'		=> 'Meta Keyword',
			'h1'				=> 'H1 Tag',
			'h2'				=> 'H2 Tag',
			'image_alt'			=> 'Image Alt Tag',
			'image_title'		=> 'Image Title Tag'
		);
		
		$data['brand_elements'] = array(
			'meta_title' 		=> 'Meta Title',
			'meta_description'	=> 'Meta Description',
			'meta_keyword'		=> 'Meta Keyword',
			'h1'				=> 'H1 Tag',
			'h2'				=> 'H2 Tag',
			'image_alt'			=> 'Image Alt Tag',
			'image_title'		=> 'Image Title Tag'
		);
		
		$data['information_elements'] = array(
			'meta_title' 		=> 'Meta Title',
			'meta_description'	=> 'Meta Description',
			'meta_keyword'	=> 'Meta Keyword'
		);
		
		foreach ($data['languages'] as $language){
	 		$language_id = $language['language_id'];
			foreach ($data['product_elements'] as $key => $value) {
				$data['product'][$key][$language_id] = $this->model_extension_hbseo_hb_onpage->getCount('product',$key,$language_id,$store_id);
			}
			foreach ($data['category_elements'] as $key => $value) {
				$data['category'][$key][$language_id] = $this->model_extension_hbseo_hb_onpage->getCount('category',$key,$language_id,$store_id);
			}
			foreach ($data['brand_elements'] as $key => $value) {
				$data['brand'][$key][$language_id] = $this->model_extension_hbseo_hb_onpage->getCount('brand',$key,$language_id,$store_id);
			}
			foreach ($data['information_elements'] as $key => $value) {
				$data['information'][$key][$language_id] = $this->model_extension_hbseo_hb_onpage->getCount('information',$key,$language_id,$store_id);
			}
		}
		
		
		
		$data['total_products'] = $this->model_extension_hbseo_hb_onpage->getTotalItems('product',$store_id);
		$data['total_categories'] = $this->model_extension_hbseo_hb_onpage->getTotalItems('category',$store_id);
		$data['total_brands'] = $this->model_extension_hbseo_hb_onpage->getTotalItems('brand',$store_id);
		$data['total_informations'] = $this->model_extension_hbseo_hb_onpage->getTotalItems('information',$store_id);
		
		$data['pages'][] = array(
			'title'    		=> 'Product Pages',
			'code'			=> 'product',
			'total_items'	=> $data['total_products'],
			'items' 		=> $data['product_elements'],
			'counts'    	=> $data['product']
		);
		
		$data['pages'][] = array(
			'title'    		=> 'Category Pages',
			'code'			=> 'category',
			'total_items'	=> $data['total_categories'],
			'items' 		=> $data['category_elements'],
			'counts'    	=> $data['category']
		);
		
		$data['pages'][] = array(
			'title'    		=> 'Brand Pages',
			'code'			=> 'brand',
			'total_items'	=> $data['total_brands'],
			'items' 		=> $data['brand_elements'],
			'counts'    	=> $data['brand']
		);
		
		$data['pages'][] = array(
			'title'    		=> 'Information Pages',
			'code'			=> 'information',
			'total_items'	=> $data['total_informations'],
			'items' 		=> $data['information_elements'],
			'counts'    	=> $data['information']
		);
		
		$this->response->setOutput($this->load->view('extension/hbseo/'.TEMPLATE_FOLDER.'/hb_onpage_dashboard'.TEMPLATE_EXTN, $data));
	}
	
	public function logs(){
		if (!file_exists(DIR_LOGS . 'hb_seo_on_page_generator')) {
			mkdir(DIR_LOGS . 'hb_seo_on_page_generator', 0777, true);
		}
		
		if (isset($this->request->get['log'])){
			$data['filename'] = strtolower($this->request->get['log']);
		}else{
			$month = date('M').'-'.date('Y');
			$data['filename'] = strtolower($month).'.txt';
		}

		$file = DIR_LOGS . 'hb_seo_on_page_generator/'.$data['filename'];
		if (file_exists($file)) {
			$data['log'] = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
		}else{
			$data['log'] = '';
		}
		$this->response->setOutput($this->load->view('extension/hbseo/'.TEMPLATE_FOLDER.'/hb_onpage_worklog'.TEMPLATE_EXTN, $data));
	}
	
	public function cleartags(){
		$store_id = (int)$this->request->get['store_id'];
		$page_type = $this->request->post['page_type'];
		$element = $this->request->post['element'];
		$this->load->model('extension/hbseo/hb_onpage');		
		$this->model_extension_hbseo_hb_onpage->clearTags($page_type, $element, $store_id);
		
		$json['success'] = $element.' data deleted for '.$page_type.' pages';
		$this->response->setOutput(json_encode($json));	
	}
	
	public function setsamples(){
		$store_id = (int)$this->request->get['store_id'];
		$this->load->model('extension/hbseo/hb_onpage');
		
		$this->load->model('setting/store');
		if ($store_id == 0){ 
			 $store_name = $this->config->get('config_name');
		}else{
			 $results = $this->model_setting_store->getStore($store_id);
			 $store_name = $results['name'];
		}
		
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		foreach ($data['languages'] as $language){
	 		$language_id = $language['language_id'];
			$this->model_extension_hbseo_hb_onpage->sampletemplates($language_id, $store_id, $store_name);
		}
		$json['success'] = 'Sample Templates has been loaded. Please note these are only sample templates; For better SEO, you need to understand your website and use templates that will better suit your website content.';
		$this->response->setOutput(json_encode($json));
	}
	
	public function loadblock(){
		$this->load->model('extension/hbseo/hb_onpage');
		$store_id = (int)$this->request->get['store_id'];
		$data['language_id'] 	= $language_id = (int)$this->request->get['language_id'];
		$data['page_type'] 		= $page_type = $this->request->get['page_type'];
		$data['element_type']	= $element_type = $this->request->get['element_type'];
		
		if ($element_type == 'meta_title'){
			$et = 'title';
		}elseif ($element_type == 'meta_description'){
			$et = 'desc';
		}elseif ($element_type == 'meta_keyword'){
			$et = 'keyword';
		}elseif ($element_type == 'h1'){
			$et = 'h1';
		}elseif ($element_type == 'h2'){
			$et = 'h2';
		}elseif ($element_type == 'image_alt'){
			$et = 'imgalt';
		}elseif ($element_type == 'image_title'){
			$et = 'imgtitle';
		}
		
		$data['block']			= $page_type.'-'.$et.'-block'.$language_id;
		$data['refreshlink']	= $this->url->link(EXTN_ROUTE.'/hb_onpage/loadblock', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME].'&store_id='.$store_id.'&page_type='.$page_type.'&element_type='.$element_type.'&language_id='.$language_id, true);
		
		$data['templates'] 		= $this->model_extension_hbseo_hb_onpage->getTemplates($page_type,$element_type,$language_id,$store_id);
		$data['table_heading'] 	= strtoupper(str_replace('_',' ',$element_type)).' TEMPLATES';
		
		$this->response->setOutput($this->load->view('extension/hbseo/'.TEMPLATE_FOLDER.'/hb_onpage_templates'.TEMPLATE_EXTN, $data));
	}
	
	public function tools(){
		$data = array();
		$this->load->model('extension/hbseo/hb_onpage');
		
		$data['product_language_check'] 	= $this->model_extension_hbseo_hb_onpage->invalidLanguageEntries('product');
		$data['category_language_check'] 	= $this->model_extension_hbseo_hb_onpage->invalidLanguageEntries('category');
		$data['brand_language_check'] 		= $this->model_extension_hbseo_hb_onpage->invalidLanguageEntries('brand');
		$data['information_language_check'] = $this->model_extension_hbseo_hb_onpage->invalidLanguageEntries('information');
		
		if ($data['product_language_check'] == 0 and $data['category_language_check'] == 0 and $data['brand_language_check'] == 0 and $data['information_language_check'] == 0){
			$data['language_check_fine'] = true;
		}else{
			$data['language_check_fine'] = false;
		}
		
		$data['product_title_check'] 	= $this->model_extension_hbseo_hb_onpage->titleLengthIssues('product');
		$data['category_title_check'] 	= $this->model_extension_hbseo_hb_onpage->titleLengthIssues('category');
		$data['brand_title_check'] 		= $this->model_extension_hbseo_hb_onpage->titleLengthIssues('brand');
		$data['information_title_check'] = $this->model_extension_hbseo_hb_onpage->titleLengthIssues('information');
		
		$data['product_md_check'] 	= $this->model_extension_hbseo_hb_onpage->mdLengthIssues('product');
		$data['category_md_check'] 	= $this->model_extension_hbseo_hb_onpage->mdLengthIssues('category');
		$data['brand_md_check'] 	= $this->model_extension_hbseo_hb_onpage->mdLengthIssues('brand');
		$data['information_md_check'] = $this->model_extension_hbseo_hb_onpage->mdLengthIssues('information');
		
		$this->response->setOutput($this->load->view('extension/hbseo/'.TEMPLATE_FOLDER.'/hb_onpage_tools'.TEMPLATE_EXTN, $data));
	}
	
	public function fixlanguageentries(){
		$this->load->model('extension/hbseo/hb_onpage');		
		$this->model_extension_hbseo_hb_onpage->fixLanguageEntries();
		
		$json['success'] = 'Invalid rows deleted.';
		$this->response->setOutput(json_encode($json));	
	}
	
	public function clearmetatitleissues(){
		$page_type = $this->request->get['page_type'];
		$this->load->model('extension/hbseo/hb_onpage');		
		$this->model_extension_hbseo_hb_onpage->deleteLengthIssues($page_type);
		
		$json['success'] = 'Meta titles cleared. You can set the templates according to your requirement and regenerate meta titles for the missing ones.';
		$this->response->setOutput(json_encode($json));	
	}
	
	public function clearmetadescissues(){
		$page_type = $this->request->get['page_type'];
		$this->load->model('extension/hbseo/hb_onpage');		
		$this->model_extension_hbseo_hb_onpage->deletemdLengthIssues($page_type);
		
		$json['success'] = 'Meta descriptions cleared for length less than 100. You can set the templates according to your requirement and regenerate meta description for the missing ones such that the length is above 100 .';
		$this->response->setOutput(json_encode($json));	
	}
	
	public function addtemplate(){
		$this->load->model('extension/hbseo/hb_onpage');
		$store_id = (int)$this->request->get['store_id'];
		$page_type = $this->request->post['page_type'];
		$element_type = $this->request->post['element_type'];
		$language_id = $this->request->post['language_id'];
		$template = $this->request->post['template'];
		
		if (empty($template)){
			$json['warning'] = 'Please fill all fields!';
		}else{
			$this->model_extension_hbseo_hb_onpage->addTemplate($page_type,$element_type,$language_id,$template,$store_id);
			$json['success'] = 'Template Added';
		}
		
		$this->response->setOutput(json_encode($json));
	}
	
	public function deletetemplate(){
		$this->load->model('extension/hbseo/hb_onpage');
		$id = (int)$this->request->post['id'];
		$this->model_extension_hbseo_hb_onpage->deleteTemplate($id);
		$json['success'] = 'Template Deleted';
		$this->response->setOutput(json_encode($json));
	}
	
	public function install(){
		$this->load->model('extension/hbseo/hb_onpage');
		$this->model_extension_hbseo_hb_onpage->install();
	}
	
	public function uninstall(){
			$this->load->model('extension/hbseo/hb_onpage');
			$this->model_extension_hbseo_hb_onpage->uninstall();
	}
	
	private function validate() {
		if (!$this->user->hasPermission('modify', EXTN_ROUTE.'/hb_onpage')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}
	
	
}
?>