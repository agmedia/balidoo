<?php
class ControllerExtensionHbseoOnpageTagsGenerator extends Controller {
	public function auto() {
		if (isset($this->request->get['authkey'])) {
			$authkey = $this->request->get['authkey'];
		} else {
			$authkey = '';
		}
		
		$this->generate_pages('product',$authkey);
		$this->generate_pages('category',$authkey);
		$this->generate_pages('brand',$authkey);
		$this->generate_pages('information',$authkey);
	}
	
	public function generate_pages($page_type = 'product', $authkey = ''){
		if (isset($this->request->get['authkey'])) {
			$authkey = $this->request->get['authkey'];
		} else {
			$authkey = $authkey;
		}
		
		if (isset($this->request->get['page_type'])) {
			$page_type = $this->request->get['page_type'];
		} else {
			$page_type = $page_type;
		}
		
		$actual_authkey = $this->config->get('hb_onpage_authkey');
		if ($authkey != $actual_authkey or $authkey == ''){
			die('AUTHORIZATION FAILED');
		}
		
		$this->load->model('setting/setting');
		$data['stores'] = array();
		$data['stores'][] = 0;
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "store");
		if ($query->num_rows > 0){
			$results = $query->rows;
			foreach ($results as $result) {
				$data['stores'][] = $result['store_id'];
			}
		}
		
		foreach ($data['stores'] as $store_id){
			$store_info = $this->model_setting_setting->getSetting('hb_onpage', $store_id);
			$authkey = isset($store_info['hb_onpage_authkey'])?$store_info['hb_onpage_authkey']:'';
			$this->generate_element($page_type, 'meta_title', $store_id, $authkey, 1);
			$this->generate_element($page_type, 'meta_description', $store_id, $authkey, 1);
			$this->generate_element($page_type, 'meta_keyword', $store_id, $authkey, 1);
			
			if ($page_type != 'information'){
				$this->generate_element($page_type, 'h1', $store_id, $authkey, 1);
				$this->generate_element($page_type, 'h2', $store_id, $authkey, 1);
				$this->generate_element($page_type, 'image_alt', $store_id, $authkey, 1);
				$this->generate_element($page_type, 'image_title', $store_id, $authkey, 1);
			}
		}
		
	}
	
	public function generate_element($page_type = 'category', $element = 'meta_description', $store_id = 0, $authkey = '', $mode = '0') {
		
		if (isset($this->request->get['store_id'])) {
			$store_id = $this->request->get['store_id'];
		} else {
			$store_id = $store_id;
		}
		
		if (isset($this->request->post['authkey'])) {
			$authkey = $this->request->post['authkey'];
		} else {
			$authkey = $authkey;
		}
		
		if (isset($this->request->get['mode'])) {
			$mode = $this->request->get['mode'];
		} else {
			$mode = $mode;
		}
		
		if (isset($this->request->post['page_type'])) {
			$page_type = $this->request->post['page_type'];
		} else {
			$page_type = $page_type;
		}
		
		if (isset($this->request->post['element'])) {
			$element = $this->request->post['element'];
		} else {
			$element = $element;
		}

		$this->authenticated_user($authkey,$store_id);
		
		$this->load->model('setting/setting');
		$store_info = $this->model_setting_setting->getSetting('hb_onpage', $store_id);
		
		$limit_start = 0;
		if ($mode == 1){
			$limit_count = isset($store_info['hb_onpage_autolimit'])?$store_info['hb_onpage_autolimit']:'500';
		}else{
			$limit_count = 10;
		}
								
		$records_total = $this->getTotalEmptyTags($page_type, $element, $store_id);
		if ($records_total > 0){
			$records = $this->getEmptyTags($page_type, $element, $store_id, $limit_start, $limit_count);

			if ($records_total > $limit_count) {
				$json['success'] = 'Processing '.$limit_count.' of remaining '.$records_total.' records';
				$json['next'] = 'set';
			} else {
				$json['success'] = 'Completed: '.$element. ' Generated for ' .$page_type;
			}
			
			foreach ($records as $record) {
				$this->generateseo($page_type, $record['id'], $element, $authkey, $store_id);
			}
		}else {
			$json['error'] = $element.' already available for all '.$page_type.' pages in this store.';
		}
			
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function preview(){
		$store_id = (int)$this->request->post['store_id'];
		$template_id = (int)$this->request->post['template_id'];
		$authkey = $this->request->post['authkey'];
		$this->authenticated_user($authkey,$store_id);
		
		$result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "hb_onpage_templates` WHERE id = '".(int)$template_id."'");
		$page_type = $result->row['page_type'];
		$element_type = $result->row['element_type'];
		$language_id = $result->row['language_id'];
		$store_id = $result->row['store_id'];
		$template = $result->row['template'];
		
		if ($page_type == 'product'){
			$result = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "product_description` ORDER BY RAND() LIMIT 1 ");
			$random_id = $result->row['product_id'];
			$info = $this->getProductInfo($random_id, $language_id);
			$composed_seo = $this->replaceParameters($template,$info);
		}
		
		if ($page_type == 'category'){
			$result = $this->db->query("SELECT category_id FROM `" . DB_PREFIX . "category_description` ORDER BY RAND() LIMIT 1 ");
			$random_id = $result->row['category_id'];
			$info = $this->getCategoryInfo($random_id, $language_id);
			$composed_seo = $this->replaceParameters($template,$info);
		}
		
		if ($page_type == 'brand'){
			$result = $this->db->query("SELECT manufacturer_id FROM `" . DB_PREFIX . "manufacturer` ORDER BY RAND() LIMIT 1 ");
			$random_id = $result->row['manufacturer_id'];
			$info = $this->getBrandInfo($random_id, $language_id);
			$composed_seo = $this->replaceParameters($template,$info);
		}
		
		if ($page_type == 'information'){
			$result = $this->db->query("SELECT information_id FROM `" . DB_PREFIX . "information_description` ORDER BY RAND() LIMIT 1 ");
			$random_id = $result->row['information_id'];
			$info = $this->getInformationInfo($random_id, $language_id);
			$composed_seo = $this->replaceParameters($template,$info);
		}
		
		$json['success'] = $composed_seo;
		$json['count'] = 'Number of Characters : '.strlen($composed_seo);
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function generateseo($page = 'product', $id = 1, $element = 'meta_description', $authkey = '', $store_id = 0){			
		$this->authenticated_user($authkey,$store_id);
		
		$languages = $this->getLanguages();
		foreach ($languages as $language) {
			$language_id = $language['language_id'];
			if ($this->isEmptyTag($page, $element, $id, $language_id)) {
				if ($page == 'product') {
					$info = $this->getProductInfo($id, $language_id);
				}
				if ($page == 'category') {
					$info = $this->getCategoryInfo($id, $language_id);
				}
				if ($page == 'brand') {
					$info = $this->getBrandInfo($id, $language_id);
				}
				if ($page == 'information') {
					$info = $this->getInformationInfo($id, $language_id);
				}
				if ($info) {
					$get_template = $this->getTemplate($page,$element, $language_id, $store_id);
					if ($get_template) {
						$composed_seo = $this->replaceParameters($get_template, $info);
						$this->addSeo($page, $composed_seo, $element, $id, $language_id);
						$this->addlog(strtoupper($element).' content added for '.$page.' ID '.$id.' ('.$language['name'].')');
					}else{
						$this->addlog('No '.strtoupper($element).' Template Found for '.$page.' pages - '.$language['name']);
					}
				}else{
					$this->addlog('No '.$page.' data found for '.$page.' ID '.$id);
				}
			}
		}
	}
	
	private function authenticated_user($authkey, $store_id){
		$this->load->model('setting/setting');
		$store_info = $this->model_setting_setting->getSetting('hb_onpage', $store_id);
		$actual_authkey = isset($store_info['hb_onpage_authkey'])?$store_info['hb_onpage_authkey']:'';
		
		if ($authkey != $actual_authkey or $authkey == ''){
			die('Invalid Authentication Key!');
			return false;
		}else{
			return true;
		}
	}
		
	private function cleanwords($str, $options = array()) {
			$str = htmlspecialchars_decode($str);
			// Make sure string is in UTF-8 and strip invalid UTF-8 characters
			$str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());
			$options = array(
							'delimiter' => ' ',
							'limit' => null,
							'lowercase' => true,
							'replacements' => array(),
							'transliterate' => true,
						);
			
			$char_map = array(
			// Latin
			'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
			'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
			'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
			'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
			'ß' => 'ss',
			'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
			'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
			'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
			'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
			'ÿ' => 'y',
			 
			// Latin symbols
			'©' => '(c)',
			 
			// Greek
			'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
			'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
			'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
			'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
			'Ϋ' => 'Y',
			'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
			'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
			'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
			'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
			'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
			 
			// Turkish
			'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
			'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g',
			 
			// Russian
			'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
			'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
			'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
			'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
			'Я' => 'Ya',
			'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
			'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
			'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
			'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
			'я' => 'ya',
			 
			// Ukrainian
			'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
			'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
			 
			// Czech
			'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U',
			'Ž' => 'Z',
			'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
			'ž' => 'z',
			 
			// Polish
			'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
			'Ż' => 'Z',
			'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
			'ż' => 'z',
			
			//Arabic
			"ا"=>"a", "أ"=>"a", "آ"=>"a", "إ"=>"e", "ب"=>"b", "ت"=>"t", "ث"=>"th", "ج"=>"j",
			"ح"=>"h", "خ"=>"kh", "د"=>"d", "ذ"=>"d", "ر"=>"r", "ز"=>"z", "س"=>"s", "ش"=>"sh",
			"ص"=>"s", "ض"=>"d", "ط"=>"t", "ظ"=>"z", "ع"=>"'e", "غ"=>"gh", "ف"=>"f", "ق"=>"q",
			"ك"=>"k", "ل"=>"l", "م"=>"m", "ن"=>"n", "ه"=>"h", "و"=>"w", "ي"=>"y", "ى"=>"a",
			"ئ"=>"'e", "ء"=>"'",   
			"ؤ"=>"'e", "لا"=>"la", "ة"=>"h", "؟"=>"?", "!"=>"!", 
			"ـ"=>"", 
			"،"=>",", 
			"َ‎"=>"a", "ُ"=>"u", "ِ‎"=>"e", "ٌ"=>"un", "ً"=>"an", "ٍ"=>"en", "ّ"=>"",
			
			//persian
			"ا" => "a", "أ" => "a", "آ" => "a", "إ" => "e", "ب" => "b", "ت" => "t", "ث" => "th",
			"ج" => "j", "ح" => "h", "خ" => "kh", "د" => "d", "ذ" => "d", "ر" => "r", "ز" => "z",
			"س" => "s", "ش" => "sh", "ص" => "s", "ض" => "d", "ط" => "t", "ظ" => "z", "ع" => "'e",
			"غ" => "gh", "ف" => "f", "ق" => "q", "ك" => "k", "ل" => "l", "م" => "m", "ن" => "n",
			"ه" => "h", "و" => "w", "ي" => "y", "ى" => "a", "ئ" => "'e", "ء" => "'", 
			"ؤ" => "'e", "لا" => "la", "ک" => "ke", "پ" => "pe", "چ" => "che", "ژ" => "je", "گ" => "gu",
			"ی" => "a", "ٔ" => "", "ة" => "h", "؟" => "?", "!" => "!", 
			"ـ" => "", 
			"،" => ",", 
			"َ‎" => "a", "ُ" => "u", "ِ‎" => "e", "ٌ" => "un", "ً" => "an", "ٍ" => "en", "ّ" => "",
			 
			// Latvian
			'Ā'  =>  'A', 'Č'  =>  'C', 'Ē'  =>  'E', 'Ģ'  =>  'G', 'Ī'  =>  'i', 'Ķ'  =>  'k', 'Ļ'  =>  'L', 'Ņ'  =>  'N',
			'Š'  =>  'S', 'Ū'  =>  'u', 'Ž'  =>  'Z',
			'ā'  =>  'a', 'č'  =>  'c', 'ē'  =>  'e', 'ģ'  =>  'g', 'ī'  =>  'i', 'ķ'  =>  'k', 'ļ'  =>  'l', 'ņ'  =>  'n',
			'š'  =>  's', 'ū'  =>  'u', 'ž'  =>  'z'
			);

			// Transliterate characters to ASCII
			if ($options['transliterate']) {
			$str = str_replace(array_keys($char_map), $char_map, $str);
			}
			// Replace non-alphanumeric characters with our delimiter
			$str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);
			// Remove duplicate delimiters
			$str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);
	
			// Remove delimiter from ends
			$str = trim($str, $options['delimiter']);
			return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
	}	
	
	private function replaceParameters($template, $info){
		$cleanwords = true; //set this to false if cleaned name is not needed, improves performace, good for large stores
		foreach ($info as $key => $value) {
			if (!is_array($value) && $value != NULL) {
				$template 		= str_replace('{'.$key.'}', $value, $template);
				if ($cleanwords){
					$template 		= str_replace('{x'.$key.'}', $value, $template);
				}
			}
		}
		
		$template = htmlspecialchars_decode($template);
		$template = strip_tags($template);
		$template = preg_replace('!\s+!', ' ',$template);
		$template = str_replace('()','',$template);
		$template = str_replace('( )','',$template);
		$template = str_replace('| |','|',$template);
		$template = str_replace('||','',$template);
		
		return $template;
	}
	
	private function addlog($text = ''){
		if ($this->config->get('hb_onpage_logs')){
			if (!file_exists(DIR_LOGS . 'hb_seo_on_page_generator')) {
				mkdir(DIR_LOGS . 'hb_seo_on_page_generator', 0777, true);
			}
			
			$month = date('M').'-'.date('Y');
			$filename = strtolower($month).'.txt';
			$file = DIR_LOGS . 'hb_seo_on_page_generator/'.$filename;
			
			$fp = fopen($file, 'a');
			fwrite($fp, "\r\n".date('Y-m-d G:i:s') . ' - ' .$text);
			fclose($fp);
		}
	}
	
	
	//MODEL - SQL QUERIES
	private function getLanguages(){
		$results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE `status` = 1");
		return $results->rows;
	}
	
	private function getTemplate($page_type, $element_type, $language_id, $store_id){
		$result = $this->db->query("SELECT `template` FROM `" . DB_PREFIX . "hb_onpage_templates` WHERE `page_type` = '".$this->db->escape($page_type)."' AND `element_type` = '".$this->db->escape($element_type)."' AND `language_id` = '".(int)$language_id."' AND `store_id` = '".(int)$store_id."' ORDER BY RAND() LIMIT 1");
		if ($result->row) {
			return $result->row['template'];
		}else{
			return false;
		}
	}	
	
	private function getFirstLine($description) {		
		$description = (!empty($description)) ? strip_tags($description) : '';

		if (!empty($description)){
			$description = html_entity_decode($description);
			$pos = strpos($description, '.');
			   
			if ($pos === false){
				return $description;
			}else{
				return substr($description, 0, $pos+1);
			}
		}else{
			return '';
		}
	}
	
	private function getProductInfo($product_id, $language_id){
		$result = $this->db->query("SELECT b.name AS name, b.description, b.tag AS tag, a.model AS model,(SELECT m.name FROM " . DB_PREFIX . "manufacturer m WHERE (m.manufacturer_id = a.manufacturer_id)) AS brand, a.upc AS upc FROM `" . DB_PREFIX . "product` a, `" . DB_PREFIX . "product_description` b WHERE (a.product_id = b.product_id) AND a.product_id = '".(int)$product_id."' AND b.language_id = '".(int)$language_id."' LIMIT 1");
		if ($result->row) {
				
			$details = array(
				'name' 			=>  $result->row['name'],
				'model' 		=>	$result->row['model'],
				'brand' 		=> 	$result->row['brand'],
				'tag'			=> 	$result->row['tag'],
				'upc'			=>	$result->row['upc'],
				'description' 	=> 	$this->getFirstLine($result->row['description']),
				'category'  	=>	$this->getCategoriesName($product_id, $language_id)
			);
			return $details;
		}else{
			return false;
		}
	}
		
	private function getCategoriesName($product_id, $language_id){
		$results = $this->db->query("SELECT group_concat((select name from " . DB_PREFIX . "category_description where category_id = a.category_id and language_id = ".$language_id.") separator ', ')as category FROM `" . DB_PREFIX . "product_to_category` a where product_id = '".$product_id."' group by product_id");
		if ($results->row){
			return $results->row['category'];
		}else{
			return $value = '';
		}
	}
	
	private function getCategoryInfo($category_id, $language_id){
		$result = $this->db->query("SELECT name, description FROM `" . DB_PREFIX . "category_description` WHERE category_id = '".(int)$category_id."' AND language_id = '".(int)$language_id."' LIMIT 1");
		if ($result->row) {
			$details = array(
				'name' 			=>  $result->row['name'],
				'description' 	=> 	$this->getFirstLine($result->row['description'])
			);
			return $details;
		}else{
			return false;
		}
	}
	
	private function getBrandInfo($manufacturer_id, $language_id){
		$result = $this->db->query("SELECT name, brand_description FROM `" . DB_PREFIX . "manufacturer` WHERE manufacturer_id = '".(int)$manufacturer_id."' LIMIT 1");
		if ($result->row) {
			$details = array(
				'name' 				=>  $result->row['name'],
				'description' 		=>  $this->getFirstLine($result->row['brand_description'])
			);
			return $details;
		}else{
			return false;
		}
	}
	
	private function getInformationInfo($information_id, $language_id){
		$result = $this->db->query("SELECT `title`, description FROM `" . DB_PREFIX . "information_description` WHERE information_id = '".(int)$information_id."' AND language_id = '".(int)$language_id."' LIMIT 1");
		if ($result->row) {
			$details = array(
				'name' 			=>  $result->row['title'],
				'description' 	=> 	$this->getFirstLine($result->row['description'])
			);
			return $details;
		}else{
			return false;
		}
	}
	
	private function isEmptyTag($page, $element, $id, $language_id){
		if ($page == 'product'){
			$sql = "SELECT COUNT(*) as total FROM `" . DB_PREFIX . "product_description` WHERE product_id = '".(int)$id."' and language_id = '".(int)$language_id."' AND (trim(".$element.") = '' OR ".$element." IS NULL)";
		}
		if ($page == 'category'){
			$sql = "SELECT COUNT(*) as total FROM `" . DB_PREFIX . "category_description` WHERE category_id = '".(int)$id."' and language_id = '".(int)$language_id."' AND (trim(".$element.") = '' OR ".$element." IS NULL)";
		}
		if ($page == 'brand'){
			$sql = "SELECT COUNT(*) as total FROM `" . DB_PREFIX . "manufacturer` WHERE manufacturer_id = '".(int)$id."' AND (trim(".$element.") = '' OR ".$element." IS NULL)";
		}
		if ($page == 'information'){
			$sql = "SELECT COUNT(*) as total FROM `" . DB_PREFIX . "information_description` WHERE information_id = '".(int)$id."' and language_id = '".(int)$language_id."' AND (trim(".$element.") = '' OR ".$element." IS NULL)";
		}
		$results = $this->db->query($sql);
		if ($results->row['total'] > 0) {
			return true;
		}else{
			return false;
		}
	}
	
	private function addSeo($type, $content, $element, $id, $language_id){
		$content = str_replace('"', '&quot;', $content);
		if ($type == 'product'){
			$this->db->query("UPDATE `" . DB_PREFIX . "product_description` SET `".$element."` = '".$this->db->escape($content)."' WHERE product_id = '".(int)$id."' and language_id = '".(int)$language_id."'");
		}
		if ($type == 'category'){
			$this->db->query("UPDATE `" . DB_PREFIX . "category_description` SET `".$element."` = '".$this->db->escape($content)."' WHERE category_id = '".(int)$id."' and language_id = '".(int)$language_id."'");
		}
		if ($type == 'brand'){
			$this->db->query("UPDATE `" . DB_PREFIX . "manufacturer` SET `".$element."` = '".$this->db->escape($content)."' WHERE manufacturer_id = '".(int)$id."'");
		}
		if ($type == 'information'){
			$this->db->query("UPDATE `" . DB_PREFIX . "information_description` SET `".$element."` = '".$this->db->escape($content)."' WHERE information_id = '".(int)$id."' and language_id = '".(int)$language_id."'");
		}
	}	
	

	private function getTotalEmptyTags($type, $element, $store_id){
		$sql = '';
		if ($type == 'product'){
			$sql = "SELECT COUNT(*) as total FROM ".DB_PREFIX."product_description a, ".DB_PREFIX."product_to_store b WHERE a.product_id = b.product_id and b.store_id = '".(int)$store_id."' AND (trim(a.".$element.") = '' OR a.".$element." IS NULL)";		
		}
		
		if ($type == 'category'){
			$sql = "SELECT COUNT(*) as total FROM ".DB_PREFIX."category_description a, ".DB_PREFIX."category_to_store b WHERE a.category_id = b.category_id and b.store_id = '".(int)$store_id."' AND (trim(a.".$element.") = '' OR a.".$element." IS NULL)";			
		}
		
		if ($type == 'brand'){
			$sql = "SELECT COUNT(*) as total FROM ".DB_PREFIX."manufacturer a, ".DB_PREFIX."manufacturer_to_store b WHERE a.manufacturer_id = b.manufacturer_id and b.store_id = '".(int)$store_id."' AND (trim(a.".$element.") = '' OR a.".$element." IS NULL)";			
		}
		
		if ($type == 'information'){
			$sql = "SELECT COUNT(*) as total FROM ".DB_PREFIX."information_description a, ".DB_PREFIX."information_to_store b WHERE a.information_id = b.information_id and b.store_id = '".(int)$store_id."' AND (trim(a.".$element.") = '' OR a.".$element." IS NULL)";			
		}
		
		$results = $this->db->query($sql);
		return $results->row['total'];
	}
	
	private function getEmptyTags($type, $element, $store_id, $start, $end){
		if ($type == 'product'){
			$results = $this->db->query("SELECT a.product_id as id FROM ".DB_PREFIX."product_description a, ".DB_PREFIX."product_to_store b WHERE a.product_id = b.product_id and b.store_id = '".(int)$store_id."' AND (trim(a.".$element.") = '' OR a.".$element." IS NULL) GROUP BY a.product_id LIMIT " . (int)$start . "," . (int)$end);		
		}
		
		if ($type == 'category'){
			$results = $this->db->query("SELECT a.category_id as id FROM ".DB_PREFIX."category_description a, ".DB_PREFIX."category_to_store b WHERE a.category_id = b.category_id and b.store_id = '".(int)$store_id."' AND (trim(a.".$element.") = '' OR a.".$element." IS NULL) GROUP BY a.category_id LIMIT " . (int)$start . "," . (int)$end);		
		}
		
		if ($type == 'brand'){
			$results = $this->db->query("SELECT a.manufacturer_id as id FROM ".DB_PREFIX."manufacturer a, ".DB_PREFIX."manufacturer_to_store b WHERE a.manufacturer_id = b.manufacturer_id and b.store_id = '".(int)$store_id."' AND (trim(a.".$element.") = '' OR a.".$element." IS NULL) GROUP BY a.manufacturer_id LIMIT " . (int)$start . "," . (int)$end);		
		}
		
		if ($type == 'information'){
			$results = $this->db->query("SELECT a.information_id as id FROM ".DB_PREFIX."information_description a, ".DB_PREFIX."information_to_store b WHERE a.information_id = b.information_id and b.store_id = '".(int)$store_id."' AND (trim(a.".$element.") = '' OR a.".$element." IS NULL) GROUP BY a.information_id LIMIT " . (int)$start . "," . (int)$end);		
		}
		
		return $results->rows;
	}
}
