<?php
class ModelExtensionHbseoHbOnpage extends Model {
	public function install(){
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hb_onpage_templates` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `template` VARCHAR(200) NOT NULL,
			  `page_type` VARCHAR(100) NOT NULL,
			  `element_type` VARCHAR(100) NOT NULL,
			  `language_id` int(11) NOT NULL,
			  `store_id` int(11) NOT NULL,
			  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  PRIMARY KEY (`id`)
			)");
		
		//product
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "product_description` LIKE 'h1'");
		if (!$check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "product_description` ADD `h1` VARCHAR(300) AFTER `meta_keyword`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "product_description` LIKE 'h2'");
		if (!$check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "product_description` ADD `h2` VARCHAR(300) AFTER `h1`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "product_description` LIKE 'image_alt'");
		if (!$check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "product_description` ADD `image_alt` VARCHAR(300) AFTER `h2`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "product_description` LIKE 'image_title'");
		if (!$check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "product_description` ADD `image_title` VARCHAR(300) AFTER `image_alt`");
		}
		//category
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "category_description` LIKE 'h1'");
		if (!$check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "category_description` ADD `h1` VARCHAR(300) AFTER `meta_keyword`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "category_description` LIKE 'h2'");
		if (!$check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "category_description` ADD `h2` VARCHAR(300) AFTER `h1`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "category_description` LIKE 'image_alt'");
		if (!$check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "category_description` ADD `image_alt` VARCHAR(300) AFTER `h2`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "category_description` LIKE 'image_title'");
		if (!$check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "category_description` ADD `image_title` VARCHAR(300) AFTER `image_alt`");
		}
		
		//brand
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "manufacturer` LIKE 'language_id'");
		if (!$check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer` ADD `language_id` INT NOT NULL DEFAULT '".$this->defaultLanguage()."' AFTER `name`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "manufacturer` LIKE 'brand_description'");
		if (!$check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer` ADD `brand_description` TEXT AFTER `language_id`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "manufacturer` LIKE 'meta_title'");
		if (!$check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer` ADD `meta_title` VARCHAR(300) AFTER `brand_description`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "manufacturer` LIKE 'meta_description'");
		if (!$check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer` ADD `meta_description` VARCHAR(300) AFTER `meta_title`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "manufacturer` LIKE 'meta_keyword'");
		if (!$check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer` ADD `meta_keyword` VARCHAR(300) AFTER `meta_description`");
		}		
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "manufacturer` LIKE 'h1'");
		if (!$check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer` ADD `h1` VARCHAR(300) AFTER `meta_keyword`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "manufacturer` LIKE 'h2'");
		if (!$check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer` ADD `h2` VARCHAR(300) AFTER `h1`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "manufacturer` LIKE 'image_alt'");
		if (!$check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer` ADD `image_alt` VARCHAR(300) AFTER `h2`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "manufacturer` LIKE 'image_title'");
		if (!$check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer` ADD `image_title` VARCHAR(300) AFTER `image_alt`");
		}	
		
		
		if (version_compare(VERSION,'2.2.0.0','<' )) {
			$theme = $this->config->get('config_template');
		}else if (version_compare(VERSION,'2.3.0.0','>' )) {
			$theme = str_replace('theme_','',$this->config->get('config_theme'));
		} else {
			$theme = $this->config->get('theme_default_directory');
		}
		
		if ($theme == 'journal3') {
			$template_name = 'journal3';
		}elseif ($theme == 'journal2') {
			$template_name = 'journal2';
		}else{
			$template_name = 'default';
		}
		
		if ((version_compare(VERSION,'2.0.0.0','>=' )) and (version_compare(VERSION,'2.3.0.2','<=' ))) {
			$ocmod_filename = 'ocmod_seo_onpage_2xxx_'.$template_name.'.txt';
			$ocmod_name = 'SEO - On-Page Tags Generator ['.$template_name.'][2xxx]';
		}else if (version_compare(VERSION,'3.0.0.0','>=' )) {
			$ocmod_filename = 'ocmod_seo_onpage_3xxx_'.$template_name.'.txt';
			$ocmod_name = 'SEO - On-Page Tags Generator ['.$template_name.'][3xxx]';
		}
		
		$ocmod_version = EXTN_VERSION;
		$ocmod_code = 'huntbee_onpage_tags_ocmod';	
		$ocmod_author = 'HuntBee OpenCart Services';
		$ocmod_link = 'https://www.huntbee.com/';
		
		$file = DIR_APPLICATION . 'view/template/extension/hbseo/ocmod/'.$ocmod_filename;
		if (file_exists($file)) {
			$ocmod_xml = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
			$ocmod_xml = str_replace('{huntbee_vesion}',$ocmod_version,$ocmod_xml);
			$this->db->query("INSERT INTO " . DB_PREFIX . "modification SET code = '" . $this->db->escape($ocmod_code) . "', name = '" . $this->db->escape($ocmod_name) . "', author = '" . $this->db->escape($ocmod_author) . "', version = '" . $this->db->escape($ocmod_version) . "', link = '" . $this->db->escape($ocmod_link) . "', xml = '" . $this->db->escape($ocmod_xml) . "', status = '1', date_added = NOW()");
		}
	}
	
	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "hb_onpage_templates`");
		//product
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "product_description` LIKE 'h1'");
		if ($check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "product_description`  DROP COLUMN `h1`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "product_description` LIKE 'h2'");
		if ($check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "product_description` DROP COLUMN `h2`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "product_description` LIKE 'image_alt'");
		if ($check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "product_description` DROP COLUMN `image_alt`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "product_description` LIKE 'image_title'");
		if ($check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "product_description` DROP COLUMN `image_title`");
		}
		//category
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "category_description` LIKE 'h1'");
		if ($check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "category_description` DROP COLUMN `h1`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "category_description` LIKE 'h2'");
		if ($check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "category_description` DROP COLUMN `h2`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "category_description` LIKE 'image_alt'");
		if ($check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "category_description` DROP COLUMN `image_alt`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "category_description` LIKE 'image_title'");
		if ($check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "category_description` DROP COLUMN `image_title`");
		}
		
		//brand
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "manufacturer` LIKE 'language_id'");
		if ($check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer` DROP COLUMN `language_id`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "manufacturer` LIKE 'brand_description'");
		if ($check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer` DROP COLUMN `brand_description`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "manufacturer` LIKE 'meta_title'");
		if ($check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer` DROP COLUMN `meta_title`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "manufacturer` LIKE 'meta_description'");
		if ($check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer` DROP COLUMN `meta_description`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "manufacturer` LIKE 'meta_keyword'");
		if ($check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer` DROP COLUMN `meta_keyword`");
		}		
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "manufacturer` LIKE 'h1'");
		if ($check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer` DROP COLUMN `h1`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "manufacturer` LIKE 'h2'");
		if ($check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer` DROP COLUMN `h2`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "manufacturer` LIKE 'image_alt'");
		if ($check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer`  DROP COLUMN `image_alt`");
		}
		$check = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "manufacturer` LIKE 'image_title'");
		if ($check->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "manufacturer`  DROP COLUMN `image_title`");
		}
		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `code` = 'huntbee_onpage_tags_ocmod'");
	}
	
	public function sampletemplates($language_id = 1, $store_id = 0, $store_name = 'Your Store'){
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{name}','product','meta_title','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{name} | ".$store_name."','product','meta_title','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{name} | {brand} | ".$store_name."','product','meta_title','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{name} - {description}','product','meta_description','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{description}','product','meta_description','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('Buy {name}, {brand} from ".$store_name.". Fast & Free Home Delivery. High Quality Service','product','meta_description','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{name} {category} {model} {brand} {tag}','product','meta_keyword','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('buy {xname}, buy {xname} online, online shopping {xname}, {xcategory}, {xcategory} {xname}, {xb} {xname} {xm}, quality {xname} {xcategory}, best price {xname}, less price {xname}','product','meta_keyword','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{name} {brand} {model} {upc}','product','h1','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{name}','product','h1','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{name}','product','h2','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{name} | {brand} | {category}','product','h2','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{name} image','product','image_alt','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{name} {category} image','product','image_alt','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('Showing image for {name}','product','image_title','".(int)$language_id."','".(int)$store_id."')");
		
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{name} - ".$store_name."','category','meta_title','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('Buy Best {name} Products from ".$store_name."','category','meta_title','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{description}','category','meta_description','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{category} - {description}','category','meta_description','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('Buy best and quality {name} products at less price only from ".$store_name.". Fast and free home delivery','category','meta_description','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('buy {xname}, buy {xname} products, best {xname} products, low price {xname}, high quality {xname} products, online {xname} products, buy {xname} online','category','meta_keyword','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{name}','category','h1','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('Best {name} products','category','h1','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('Buy best and quality {name} products','category','h2','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('Quality {name} products from ".$store_name."','category','h2','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{name} image','category','image_alt','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('Showing image for {name}','category','image_title','".(int)$language_id."','".(int)$store_id."')");

		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{name} - ".$store_name."','brand','meta_title','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{name} Products from ".$store_name."','brand','meta_title','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('Buy Best {name} Products from ".$store_name."','brand','meta_title','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{brand} - {description}','brand','meta_description','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{description}','brand','meta_description','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('Buy best and quality {name} products at less price only from ".$store_name.". Fast and free home delivery','brand','meta_description','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('buy {xname}, buy {xname} products, best {xname} products, low price {xname}, high quality {xname} products, online {xname} products, buy {xname} online','brand','meta_keyword','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('Best {name} products','brand','h1','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('Buy best and quality {name} products','brand','h2','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{name} image','brand','image_alt','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('Showing image for {name}','brand','image_title','".(int)$language_id."','".(int)$store_id."')");
		
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{name} | ".$store_name."','information','meta_title','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{name} {description}','information','meta_description','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{description}','information','meta_description','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{name} - ".$store_name." . Best Products, Best Price, Best Quality, Free Home Delivery','information','meta_description','".(int)$language_id."','".(int)$store_id."')");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) VALUES ('{xname}, {xname} information, {xname} ".$store_name.", best products, best quality products','information','meta_keyword','".(int)$language_id."','".(int)$store_id."')");
	}
	
	public function defaultLanguage(){
		$query = $this->db->query("SELECT language_id FROM `" . DB_PREFIX . "language` WHERE `code` = '".$this->config->get('config_language')."'");
		return $query->row['language_id'];
	}
	
	public function addTemplate($page_type,$element_type,$language_id,$template,$store_id) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "hb_onpage_templates` (`template`,`page_type`,`element_type`,`language_id`,`store_id`) 
		VALUES ('".$this->db->escape($template)."','".$this->db->escape($page_type)."','".$this->db->escape($element_type)."','".(int)$language_id."','".(int)$store_id."')");
	}
	
	public function getTemplates($page_type,$element_type,$language_id,$store_id){
		$results = $this->db->query("SELECT * FROM `" . DB_PREFIX . "hb_onpage_templates` WHERE `page_type` = '".$this->db->escape($page_type)."' AND `element_type` = '".$this->db->escape($element_type)."' AND `language_id` = '".(int)$language_id."' AND `store_id` = '".(int)$store_id."'");
		if ($results->rows){
			return $results->rows;
		}else{
			return false;
		}
	}
	
	public function deleteTemplate($id){
		$this->db->query("DELETE FROM `" . DB_PREFIX . "hb_onpage_templates` WHERE `id` = '".(int)$id."'");
	}
	
	public function getTotalItems($page_type,$store_id){
		if ($page_type == 'product'){
			$sql = "SELECT count(*) as total FROM ".DB_PREFIX."product a, ".DB_PREFIX."product_to_store b WHERE a.product_id = b.product_id and b.store_id = '".(int)$store_id."'";		
		}
		if ($page_type == 'category'){
			$sql = "SELECT count(*) as total FROM ".DB_PREFIX."category a, ".DB_PREFIX."category_to_store b WHERE a.category_id = b.category_id and b.store_id = '".(int)$store_id."'";		
		}
		if ($page_type == 'brand'){
			$sql = "SELECT count(*) as total FROM ".DB_PREFIX."manufacturer a, ".DB_PREFIX."manufacturer_to_store b WHERE a.manufacturer_id = b.manufacturer_id and b.store_id = '".(int)$store_id."'";		
		}
		if ($page_type == 'information'){
			$sql = "SELECT count(*) as total FROM ".DB_PREFIX."information a, ".DB_PREFIX."information_to_store b WHERE a.information_id = b.information_id and b.store_id = '".(int)$store_id."'";		
		}
		$results = $this->db->query($sql);
		return $results->row['total'];
	}
	
	public function getCount($page_type,$element_type,$language_id,$store_id){
		if ($page_type == 'product'){
			$sql = "SELECT count(*) as total FROM ".DB_PREFIX."product_description a, ".DB_PREFIX."product_to_store b WHERE a.product_id = b.product_id and b.store_id = '".(int)$store_id."' AND a.language_id = '".(int)$language_id."' AND a.".$element_type." <> ''";		
		}
		if ($page_type == 'category'){
			$sql = "SELECT count(*) as total FROM ".DB_PREFIX."category_description a, ".DB_PREFIX."category_to_store b WHERE a.category_id = b.category_id and b.store_id = '".(int)$store_id."' AND a.language_id = '".(int)$language_id."' AND a.".$element_type." <> ''";		
		}
		if ($page_type == 'brand'){
			$language = $this->db->query("SELECT * FROM `".DB_PREFIX."manufacturer` WHERE language_id = '".(int)$language_id."'");
			if ($language->num_rows > 0) {
				$sql = "SELECT count(*) as total FROM ".DB_PREFIX."manufacturer a, ".DB_PREFIX."manufacturer_to_store b WHERE a.manufacturer_id = b.manufacturer_id and b.store_id = '".(int)$store_id."' AND a.language_id = '".(int)$language_id."' AND a.".$element_type." <> ''";		
			}else{
				$sql = "SELECT 'NA' as total";
			}
		}
		if ($page_type == 'information'){
			$sql = "SELECT count(*) as total FROM ".DB_PREFIX."information_description a, ".DB_PREFIX."information_to_store b WHERE a.information_id = b.information_id and b.store_id = '".(int)$store_id."' AND a.language_id = '".(int)$language_id."' AND a.".$element_type." <> ''";		
		}
		$results = $this->db->query($sql);
		return $results->row['total'];
	}

	
	//clear tags
	public function clearTags($page_type, $element_type, $store_id){
		if ($page_type == 'product') {
			$this->db->query("UPDATE ".DB_PREFIX."product_description a, ".DB_PREFIX."product_to_store b  SET a.".$element_type." = '' WHERE a.product_id = b.product_id and b.store_id = '".(int)$store_id."'");
		}
		if ($page_type == 'category') {
			$this->db->query("UPDATE ".DB_PREFIX."category_description a, ".DB_PREFIX."category_to_store b  SET a.".$element_type." = '' WHERE a.category_id = b.category_id and b.store_id = '".(int)$store_id."'");
		}
		if ($page_type == 'brand') {
			$this->db->query("UPDATE ".DB_PREFIX."manufacturer a, ".DB_PREFIX."manufacturer_to_store b  SET a.".$element_type." = '' WHERE a.manufacturer_id = b.manufacturer_id and b.store_id = '".(int)$store_id."'");
		}
		if ($page_type == 'information') {
			$this->db->query("UPDATE ".DB_PREFIX."information_description a, ".DB_PREFIX."information_to_store b  SET a.".$element_type." = '' WHERE a.information_id = b.information_id and b.store_id = '".(int)$store_id."'");
		}
	}
	
	public function invalidLanguageEntries($page_type){
		if ($page_type == 'product'){
			$sql = "SELECT count(*) as total FROM ".DB_PREFIX."product_description WHERE language_id NOT IN (SELECT language_id FROM ".DB_PREFIX."language)";
		}
		if ($page_type == 'category'){
			$sql = "SELECT count(*) as total FROM ".DB_PREFIX."category_description WHERE language_id NOT IN (SELECT language_id FROM ".DB_PREFIX."language)";
		}
		if ($page_type == 'brand'){
			$sql = "SELECT count(*) as total FROM ".DB_PREFIX."manufacturer WHERE language_id NOT IN (SELECT language_id FROM ".DB_PREFIX."language)";
		}
		if ($page_type == 'information'){
			$sql = "SELECT count(*) as total FROM ".DB_PREFIX."information_description WHERE language_id NOT IN (SELECT language_id FROM ".DB_PREFIX."language)";
		}
		
		$result = $this->db->query($sql);
		
		if ($result->row['total'] > 0) {
			return $result->row['total'];
		}else{
			return false;
		}
	}
	
	public function fixLanguageEntries(){
		$this->db->query("DELETE FROM ".DB_PREFIX."product_description WHERE language_id NOT IN (SELECT language_id FROM ".DB_PREFIX."language)");
		$this->db->query("DELETE FROM ".DB_PREFIX."category_description WHERE language_id NOT IN (SELECT language_id FROM ".DB_PREFIX."language)");
		$this->db->query("DELETE FROM ".DB_PREFIX."manufacturer WHERE language_id NOT IN (SELECT language_id FROM ".DB_PREFIX."language)");
		$this->db->query("DELETE FROM ".DB_PREFIX."information_description WHERE language_id NOT IN (SELECT language_id FROM ".DB_PREFIX."language)");
	}
	
	public function titleLengthIssues($page_type){
		if ($page_type == 'product'){
			$sql = "SELECT count(*) as total FROM ".DB_PREFIX."product_description WHERE (CHAR_LENGTH(meta_title) < 50 OR CHAR_LENGTH(meta_title) > 60) AND meta_title <> ''";
		}
		if ($page_type == 'category'){
			$sql = "SELECT count(*) as total FROM ".DB_PREFIX."category_description WHERE (CHAR_LENGTH(meta_title) < 50 OR CHAR_LENGTH(meta_title) > 60) AND meta_title <> ''";
		}
		if ($page_type == 'brand'){
			$sql = "SELECT count(*) as total FROM ".DB_PREFIX."manufacturer WHERE (CHAR_LENGTH(meta_title) < 50 OR CHAR_LENGTH(meta_title) > 60) AND meta_title <> ''";
		}
		if ($page_type == 'information'){
			$sql = "SELECT count(*) as total FROM ".DB_PREFIX."information_description WHERE (CHAR_LENGTH(meta_title) < 50 OR CHAR_LENGTH(meta_title) > 60) AND meta_title <> ''";
		}
		
		$result = $this->db->query($sql);
		
		return $result->row['total'];
	}
	
	public function deleteLengthIssues($page_type){
		if ($page_type == 'product'){
			$sql = "UPDATE ".DB_PREFIX."product_description SET meta_title = '' WHERE CHAR_LENGTH(meta_title) < 50 OR CHAR_LENGTH(meta_title) > 60";
		}
		if ($page_type == 'category'){
			$sql = "UPDATE ".DB_PREFIX."category_description SET meta_title = '' WHERE CHAR_LENGTH(meta_title) < 50 OR CHAR_LENGTH(meta_title) > 60";
		}
		if ($page_type == 'brand'){
			$sql = "UPDATE ".DB_PREFIX."manufacturer SET meta_title = '' WHERE CHAR_LENGTH(meta_title) < 50 OR CHAR_LENGTH(meta_title) > 60";
		}
		if ($page_type == 'information'){
			$sql = "UPDATE ".DB_PREFIX."information_description SET meta_title = '' WHERE CHAR_LENGTH(meta_title) < 50 OR CHAR_LENGTH(meta_title) > 60";
		}
		
		$this->db->query($sql);
	}
	
	public function mdLengthIssues($page_type){
		if ($page_type == 'product'){
			$sql = "SELECT count(*) as total FROM ".DB_PREFIX."product_description WHERE CHAR_LENGTH(meta_description) < 100 AND meta_description <> ''";
		}
		if ($page_type == 'category'){
			$sql = "SELECT count(*) as total FROM ".DB_PREFIX."category_description WHERE CHAR_LENGTH(meta_description) < 100 AND meta_description <> ''";
		}
		if ($page_type == 'brand'){
			$sql = "SELECT count(*) as total FROM ".DB_PREFIX."manufacturer WHERE CHAR_LENGTH(meta_description) < 100 AND meta_description <> ''";
		}
		if ($page_type == 'information'){
			$sql = "SELECT count(*) as total FROM ".DB_PREFIX."information_description WHERE CHAR_LENGTH(meta_description) < 100 AND meta_description <> ''";
		}
		
		$result = $this->db->query($sql);
		
		return $result->row['total'];
	}
	
	public function deletemdLengthIssues($page_type){
		if ($page_type == 'product'){
			$sql = "UPDATE ".DB_PREFIX."product_description SET meta_description = '' WHERE CHAR_LENGTH(meta_description) < 100";
		}
		if ($page_type == 'category'){
			$sql = "UPDATE ".DB_PREFIX."category_description SET meta_description = '' WHERE CHAR_LENGTH(meta_description) < 100";
		}
		if ($page_type == 'brand'){
			$sql = "UPDATE ".DB_PREFIX."manufacturer SET meta_description = '' WHERE CHAR_LENGTH(meta_description) < 100";
		}
		if ($page_type == 'information'){
			$sql = "UPDATE ".DB_PREFIX."information_description SET meta_description = '' WHERE CHAR_LENGTH(meta_description) < 100";
		}
		
		$this->db->query($sql);
	}
	
}
?>