<?php
class ModelExtensionHbseoHbSeourl extends Model {
	public function install(){
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hb_url` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `route` VARCHAR(200) NOT NULL,
			  `keyword` VARCHAR(300) NOT NULL,
			  `language_id` int(11) NOT NULL,
			  `store_id` int(11) NOT NULL,
			  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8");
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hb_url_preserve` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `store_id` int(11) NOT NULL,
			  `language_id` int(11) NOT NULL,
			  `query` VARCHAR(255) NOT NULL,
			  `old_keyword` VARCHAR(255) NOT NULL,
			  `new_keyword` VARCHAR(255) NOT NULL,
			  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8");
		
		if (version_compare(VERSION,'3.0.0.0','<')){	
			$language_id = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "url_alias` LIKE 'language_id'");
			if (!$language_id->num_rows){
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "url_alias` ADD `language_id` INT NOT NULL DEFAULT 1 AFTER `query`");
			}
		}
		
		if ((version_compare(VERSION,'2.0.0.0','>=' )) and (version_compare(VERSION,'2.2.0.0','<' ))) {
			$ocmod_filename = 'ocmod_hb_seourl_2000_21xx.txt';
			$ocmod_name = 'SEO - URL BASIC [2000 - 21xx]';
		}else if ((version_compare(VERSION,'2.2.0.0','>=' )) and (version_compare(VERSION,'3.0.0.0','<' ))) {
			$ocmod_filename = 'ocmod_hb_seourl_2200_23xx.txt';
			$ocmod_name = 'SEO - URL BASIC [2200 - 23xx]';
		}else if (version_compare(VERSION,'3.0.0.0','>=' )) {
			$ocmod_filename = 'ocmod_hb_seourl_3xxx.txt';
			$ocmod_name = 'SEO - URL BASIC [3xxx]';
		}
		
		$ocmod_version = EXTN_VERSION;
		$ocmod_code = 'huntbee_seo_friendly_url_ocmod';	
		$ocmod_author = 'HuntBee OpenCart Services';
		$ocmod_link = 'https://www.huntbee.com';
		
		$file = DIR_APPLICATION . 'view/template/extension/hbseo/ocmod/'.$ocmod_filename;
		if (file_exists($file)) {
			$ocmod_xml = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
			$this->db->query("INSERT INTO " . DB_PREFIX . "modification SET code = '" . $this->db->escape($ocmod_code) . "', name = '" . $this->db->escape($ocmod_name) . "', author = '" . $this->db->escape($ocmod_author) . "', version = '" . $this->db->escape($ocmod_version) . "', link = '" . $this->db->escape($ocmod_link) . "', xml = '" . $this->db->escape($ocmod_xml) . "', status = '1', date_added = NOW()");
		}
		
		$languages_count = $this->db->query("SELECT count(*) as total FROM `" . DB_PREFIX . "language` WHERE status = 1");
		if ($languages_count->row['total'] > 1) {
			if ((version_compare(VERSION,'2.0.0.0','>=' )) and (version_compare(VERSION,'2.2.0.0','<' ))) {
				$ocmod_filename = 'ocmod_hb_seourl_ML_2000_21xx.txt';
				$ocmod_name = 'SEO - URL Multi-Language [2000 - 21xx]';
			}else if ((version_compare(VERSION,'2.1.0.2','>=' )) and (version_compare(VERSION,'2.3.0.0','<' ))) {
				$ocmod_filename = 'ocmod_hb_seourl_ML_22xx.txt';
				$ocmod_name = 'SEO - URL Multi-Language [22xx]';
			}else if ((version_compare(VERSION,'2.3.0.0','>=' )) and (version_compare(VERSION,'3.0.0.0','<' ))) {
				$ocmod_filename = 'ocmod_hb_seourl_ML_23xx.txt';
				$ocmod_name = 'SEO - URL Multi-Language [23xx]';
			}else if (version_compare(VERSION,'3.0.0.0','>=' )) {
				$ocmod_filename = 'ocmod_hb_seourl_ML_3xxx.txt';
				$ocmod_name = 'SEO - URL Multi-Language [3xxx]';
			}
			
			$file = DIR_APPLICATION . 'view/template/extension/hbseo/ocmod/'.$ocmod_filename;
			if (file_exists($file)) {
				$ocmod_xml = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
				$this->db->query("INSERT INTO " . DB_PREFIX . "modification SET code = 'huntbee_seo_multi_language_url_ocmod', name = '" . $this->db->escape($ocmod_name) . "', author = 'HuntBee OpenCart Services', version = '" . $this->db->escape(EXTN_VERSION) . "', link = 'https://www.huntbee.com', xml = '" . $this->db->escape($ocmod_xml) . "', status = '1', date_added = NOW()");
			}
		}
	}
	
	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "hb_url`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "hb_url_preserve`");
		if (version_compare(VERSION,'3.0.0.0','<')){	
			$language_id = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "url_alias` LIKE 'language_id'");
			if ($language_id->num_rows){
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "url_alias` DROP COLUMN `language_id`");
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "modification WHERE `code` = 'huntbee_seo_friendly_url_ocmod'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "modification WHERE `code` = 'huntbee_seo_multi_language_url_ocmod'");
	}
	
	public function getRoutes($store_id = 0, $language_id = 1){
		$results = $this->db->query("SELECT * FROM `".DB_PREFIX."hb_url` WHERE store_id = '".(int)$store_id."' AND language_id = '".(int)$language_id."' ORDER BY date_added DESC");
		if ($results->rows) {
			return $results->rows;
		}else{
			return false;
		}
	}
	
	public function getKeywordCountbyType($query,$language_id = 1,$store_id = 0) {
		if (version_compare(VERSION,'3.0.0.0','<')){
			$result = $this->db->query("SELECT count(*) as total FROM `".DB_PREFIX."url_alias` WHERE `query` LIKE '".$this->db->escape($query)."%' AND `language_id` = '".(int)$language_id."'");
		}else{
			$result = $this->db->query("SELECT count(*) as total FROM `".DB_PREFIX."seo_url` WHERE `query` LIKE '".$this->db->escape($query)."%' AND `language_id` = '".(int)$language_id."' AND `store_id` = '".(int)$store_id."'");
		}
		return $result->row['total'];
	}
	
	public function getProductCount($language_id = 1,$store_id = 0){
		$records = $this->db->query("SELECT count(*) as total FROM " . DB_PREFIX . "product_description pd LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (pd.product_id = p2s.product_id) WHERE pd.language_id = '".(int)$language_id."' AND p2s.store_id = '" . (int)$store_id . "'");
		return $records->row['total'];
	}
	
	public function getCategoryCount($language_id = 1,$store_id = 0){
		$records = $this->db->query("SELECT  count(*) as total FROM " . DB_PREFIX . "category_description cd LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (cd.category_id = c2s.category_id) WHERE cd.language_id = '".(int)$language_id."' AND c2s.store_id = '" . (int)$store_id . "'");
		return $records->row['total'];
	}
	
	public function getBrandCount($language_id = 1,$store_id = 0){
		$records = $this->db->query("SELECT  count(*) as total FROM " . DB_PREFIX . "manufacturer a LEFT JOIN " . DB_PREFIX . "manufacturer_to_store b ON (a.manufacturer_id = b.manufacturer_id) WHERE b.store_id = '" . (int)$store_id . "'");
		return $records->row['total'];
	}
	
	public function getInformationCount($language_id = 1,$store_id = 0){
		$records = $this->db->query("SELECT  count(*) as total FROM " . DB_PREFIX . "information_description a LEFT JOIN " . DB_PREFIX . "information_to_store b ON (a.information_id = b.information_id) WHERE a.language_id = '".(int)$language_id."' AND b.store_id = '" . (int)$store_id . "'");
		return $records->row['total'];
	}
	
	public function clearKeywordType($query, $store_id = 0) {
		if (version_compare(VERSION,'3.0.0.0','<')){
			$this->db->query("DELETE FROM `".DB_PREFIX."url_alias` WHERE `query` LIKE '".$this->db->escape($query)."%'");
		}else{
			$this->db->query("DELETE FROM `".DB_PREFIX."seo_url` WHERE `query` LIKE '".$this->db->escape($query)."%' AND `store_id` = '".(int)$store_id."'");
		}
	}
	
	public function getPreserveDate($store_id) {
		$result = $this->db->query("SELECT date_added FROM `".DB_PREFIX."hb_url_preserve` WHERE `store_id` = '".(int)$store_id."' ORDER BY `date_added` DESC LIMIT 1");
		if (isset($result->row['date_added'])) {
			return $result->row['date_added'];
		} else {
			return false;
		}
	}
	
	public function preserveKeyword($store_id) {
		$sql = "INSERT INTO `".DB_PREFIX."hb_url_preserve` (`store_id`, `language_id`, `query`, `old_keyword`)";
		
		if (version_compare(VERSION,'3.0.0.0','<')){
			$sql .= " SELECT '".(int)$store_id."', `language_id`, `query`, `keyword` FROM `" . DB_PREFIX . "url_alias`";
		} else {
			$sql .= " SELECT `store_id`, `language_id`, `query`, `keyword` FROM `" . DB_PREFIX . "seo_url` WHERE `store_id` = '".(int)$store_id."'";
		}
		$this->db->query($sql);
	}
	
	public function clearPreserve($store_id) {
		$this->db->query("DELETE FROM `".DB_PREFIX."hb_url_preserve` WHERE `store_id` = '".(int)$store_id."'");
	}
	
	public function isRedirectManagerInstalled() {
		$result = $this->db->query("SELECT * FROM `".DB_PREFIX."extension` WHERE `code` = 'hb_brokenlinks'");
		if ($result->rows){
			return true;
		} else {
			return false;
		}
	}
	
	public function getPreserveData($store_id) {
		$result = $this->db->query("SELECT * FROM `".DB_PREFIX."hb_url_preserve` WHERE `store_id` = '".(int)$store_id."' AND new_keyword <> '' AND old_keyword <> new_keyword");
		if (isset($result->rows)) {
			return $result->rows;
		} else {
			return false;
		}
	}
	
	public function checkURLKeyword($keyword, $store_id = 0){
		if (version_compare(VERSION,'3.0.0.0','<')){
    		$results = $this->db->query("SELECT count(*) as count FROM `" . DB_PREFIX . "url_alias` WHERE `keyword` = '".$this->db->escape($keyword)."'");
		}else{
    		$results = $this->db->query("SELECT count(*) as count FROM `" . DB_PREFIX . "seo_url` WHERE `keyword` = '".$this->db->escape($keyword)."' AND `store_id` = '".(int)$store_id."'");
		}
		return $results->row['count'];
	}
	
	public function clearEmptyKeyword(){
		if (version_compare(VERSION,'3.0.0.0','<')){
			$this->db->query("DELETE FROM `".DB_PREFIX."url_alias` WHERE `keyword` = ''");
		}else{
			$this->db->query("DELETE FROM `".DB_PREFIX."seo_url` WHERE `keyword` = ''");
		}
	}
	
	public function defaultLanguage(){
		$query = $this->db->query("SELECT language_id FROM `" . DB_PREFIX . "language` WHERE `code` = '".$this->config->get('config_language')."'");
		return $query->row['language_id'];
	}
	
	
	public function getProducts($store_id){
		$sql = "SELECT a.product_id, a.name, a.language_id FROM `".DB_PREFIX."product_description` a LEFT JOIN `".DB_PREFIX."product_to_store` b ON (a.product_id = b.product_id) WHERE b.store_id = '".(int)$store_id."'";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	public function getBatchProducts($store_id, $start, $limit){
		$sql = "SELECT a.product_id, a.name, a.language_id FROM `".DB_PREFIX."product_description` a LEFT JOIN `".DB_PREFIX."product_to_store` b ON (a.product_id = b.product_id) WHERE b.store_id = '".(int)$store_id."' LIMIT " . (int)$start . "," . (int)$limit;
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	public function getTotalProductsEntries($store_id){
		$sql = "SELECT count(*) as total FROM `".DB_PREFIX."product_description` a LEFT JOIN `".DB_PREFIX."product_to_store` b ON (a.product_id = b.product_id) WHERE b.store_id = '".(int)$store_id."'";
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	public function getProductInfo($product_id, $language_id){
		$query = $this->db->query("SELECT p.*, pd.name as product_name, m.name as brand FROM `".DB_PREFIX."product_description` pd LEFT JOIN ".DB_PREFIX."product p ON pd.product_id = p.product_id LEFT JOIN ".DB_PREFIX."manufacturer m ON p.manufacturer_id = m.manufacturer_id WHERE pd.product_id = '".(int)$product_id."' AND pd.language_id = '".(int)$language_id."' LIMIT 1");
		return $query->row;
	}
	
	
	public function getCategories($store_id){
		$sql = "SELECT a.category_id, a.name, a.language_id FROM `".DB_PREFIX."category_description` a LEFT JOIN `".DB_PREFIX."category_to_store` b ON (a.category_id = b.category_id) WHERE b.store_id = '".(int)$store_id."'  ";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	public function getBrands($store_id){
		$sql = "SELECT a.manufacturer_id, a.name FROM `".DB_PREFIX."manufacturer` a LEFT JOIN `".DB_PREFIX."manufacturer_to_store` b ON (a.manufacturer_id = b.manufacturer_id) WHERE b.store_id = '".(int)$store_id."'  ";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	public function getInformations($store_id){
		$sql = "SELECT a.information_id, a.title as name, a.language_id FROM `".DB_PREFIX."information_description` a LEFT JOIN `".DB_PREFIX."information_to_store` b ON (a.information_id = b.information_id) WHERE b.store_id = '".(int)$store_id."'  ";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	public function isKeywordNotAvailable($query, $language_id, $store_id){
		if (version_compare(VERSION,'3.0.0.0','<')){
			$result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "url_alias` WHERE `query` = '".$this->db->escape($query)."' AND `language_id` = '".(int)$language_id."'");
		}else{
			$result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "seo_url` WHERE `query` = '".$this->db->escape($query)."' AND `language_id` = '".(int)$language_id."' AND `store_id` = '".(int)$store_id."'");
		}
		if ($result->num_rows == 0){
			return true;
		}else{
			return false;
		}
	}
	
	public function addurlentry($query, $keyword, $language_id, $store_id, $preserve = false){
		if (version_compare(VERSION,'3.0.0.0','<')){
			$sql = "INSERT INTO `" . DB_PREFIX . "url_alias` (`query`,`keyword`,`language_id`) VALUES ('".$this->db->escape($query)."','".$this->db->escape($keyword)."','".(int)$language_id."')";
		} else {
			$sql = "INSERT INTO `" . DB_PREFIX . "seo_url` (`query`,`keyword`,`language_id`,`store_id`) VALUES ('".$this->db->escape($query)."','".$this->db->escape($keyword)."','".(int)$language_id."','".(int)$store_id."')";
		}
		
		$this->db->query($sql);
		if ($preserve) {
			$this->db->query("UPDATE `".DB_PREFIX."hb_url_preserve` SET `new_keyword` = '".$this->db->escape($keyword)."' WHERE `query` = '".$this->db->escape($query)."' AND `language_id` = '".(int)$language_id."' AND `store_id` = '".(int)$store_id."'");
		}
	}

	
}
?>