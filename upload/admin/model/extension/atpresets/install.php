<?php 

class ModelExtensionAtpresetsInstall extends Model {  

	public function install($data=null) {
		
		$sql="CREATE TABLE IF NOT EXISTS `". DB_PREFIX . "attribute_presets` (";
		$sql.="  `preset_id` int(11) NOT NULL AUTO_INCREMENT,";
		$sql.="  `attribute_id` int(11) NOT NULL,";				
		$sql.="  PRIMARY KEY (`preset_id`),";
		$sql.="  KEY `attribute_id` (`attribute_id`)";		
		$sql.=") ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$query = $this->db->query($sql);		

		$sql="CREATE TABLE IF NOT EXISTS `". DB_PREFIX . "attribute_presets_description` (";
		$sql.="  `preset_id` int(11) NOT NULL,";
		$sql.="  `language_id` int(11) NOT NULL,";			
		$sql.="  `text` text NOT NULL,";
		$sql.="  PRIMARY KEY (`preset_id`,`language_id`)";		
		$sql.=") ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$query = $this->db->query($sql);	

		$sql="CREATE TABLE IF NOT EXISTS `". DB_PREFIX . "attemplate` (";
		$sql.="  `attemplate_id` int(11) NOT NULL AUTO_INCREMENT,";
		$sql.="  `name` text NOT NULL,";		
		$sql.="  `status` tinyint NOT NULL DEFAULT '1',";		
		$sql.="  PRIMARY KEY (`attemplate_id`)";		
		$sql.=") ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$query = $this->db->query($sql);		

		$sql="CREATE TABLE IF NOT EXISTS `". DB_PREFIX . "attemplate_attribute` (";
		$sql.="  `attemplate_id` int(11) NOT NULL,";
		$sql.="  `attribute_id` int(11) NOT NULL,";			
		$sql.="  `preset_id` int(11) NOT NULL DEFAULT '0',";	
		$sql.="  PRIMARY KEY (`attemplate_id`,`attribute_id`,`preset_id`)";		
		$sql.=") ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
		$query = $this->db->query($sql);

		$exists = $this->db->query("SHOW COLUMNS from `" . DB_PREFIX . "product_attribute` LIKE 'preset_id'");
		if (!$exists->num_rows){
			$sql="ALTER TABLE `" . DB_PREFIX . "product_attribute` ";
			$sql.="  add `preset_id` int(11) NOT NULL DEFAULT '0'";
			$query = $this->db->query($sql);
		}		
		
		$this->db->query("ALTER TABLE " . DB_PREFIX . "product_attribute ADD KEY(product_id, attribute_id, language_id)");
		$this->db->query("ALTER TABLE " . DB_PREFIX . "product_attribute DROP PRIMARY KEY");
		$this->db->query("ALTER TABLE " . DB_PREFIX . "product_attribute ADD PRIMARY KEY(product_id, attribute_id, language_id, preset_id)");		
		$this->db->query("ALTER TABLE " . DB_PREFIX . "product_attribute DROP INDEX product_id");

		$this->load->model('setting/setting');
		$this->load->model('user/user_group');
		
		$settings = array(		
			'module_atpresets_status'					=> '1',
			'module_atpresets_delete'					=> '0',
			'module_atpresets_allow_multiple'			=> '1',
			'module_atpresets_autoupdate'				=> '1',			
			'module_atpresets_installed'				=> '1',
			'module_atpresets_limit'					=> '15',
			'module_atpresets_use_newline'				=> '0',
			'module_atpresets_other_character'			=> ', ',			
			'module_atpresets_selecttype'				=> '1'
		);
		
		$this->model_setting_setting->editSetting('module_atpresets', $settings );

		$this->model_user_user_group->addPermission($this->user->getId(), 'access', 'extension/module/atpresets');		
		$this->model_user_user_group->addPermission($this->user->getId(), 'modify', 'extension/module/atpresets');

	}

	public function uninstall() {
		if ($this->config->get('module_atpresets_delete')==1) {	
		
			$min_preset_id = $this->db->query("SELECT max(preset_id)as max_preset, product_id, attribute_id FROM " . DB_PREFIX . "product_attribute WHERE preset_id <>0 GROUP BY product_id, attribute_id");
			foreach ($min_preset_id->rows as $preset) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '".$preset['product_id']."' AND attribute_id = '".$preset['attribute_id']."' AND preset_id <>0 AND preset_id <> '".$preset['max_preset']."'");
			}

			$this->db->query("ALTER TABLE " . DB_PREFIX . "product_attribute ADD KEY(product_id, attribute_id, language_id)");
			$this->db->query("ALTER TABLE " . DB_PREFIX . "product_attribute DROP PRIMARY KEY");
			$this->db->query("ALTER TABLE " . DB_PREFIX . "product_attribute ADD PRIMARY KEY(product_id, attribute_id, language_id)");
			$this->db->query("ALTER TABLE " . DB_PREFIX . "product_attribute DROP INDEX product_id");	
			
			
			$sql="drop TABLE IF EXISTS `" . DB_PREFIX . "attribute_presets`, `" . DB_PREFIX . "attribute_presets_description`, `" . DB_PREFIX . "attemplate`, `" . DB_PREFIX . "attemplate_attribute`";
			$query = $this->db->query($sql);

			$exists = $this->db->query("SHOW COLUMNS from `" . DB_PREFIX . "product_attribute` LIKE 'preset_id'");
			if ($exists->num_rows){
				$sql="ALTER TABLE `" . DB_PREFIX . "product_attribute` ";
				$sql.="  DROP COLUMN `preset_id`";
				$query = $this->db->query($sql);
			}	
			
			$this->load->model('setting/setting');
			$this->model_setting_setting->deleteSetting('module_atpresets');
		}
	}	
}

?>