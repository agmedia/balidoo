<?php 
class ModelExtensionAtpresetsAtpresets extends Model {
	public function addPreset($data) {
		$preset_id = $this->existsPreset($data);
		if ($preset_id == 0) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "attribute_presets SET attribute_id = '" . (int)$data['attribute_id'] . "'");
			$preset_id = $this->db->getLastId();
			foreach ($data['descriptions'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "attribute_presets_description SET preset_id = '" . (int)$preset_id . "', language_id = '" . (int)$language_id . "', text = '" . $this->db->escape($value) . "'");
			}
			return array('exists'=>0, 'preset_id'=>$preset_id);
		} else
			return array('exists'=>1, 'preset_id'=>$preset_id);
	}

	public function existsPreset($data) {
		if (isset($data['descriptions'][$this->config->get('config_language_id')])) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute_presets ap inner join " . DB_PREFIX . "attribute_presets_description apd on (ap.preset_id = apd.preset_id) WHERE ap.attribute_id = '" . (int)$data['attribute_id'] . "' AND apd.text = '" . $this->db->escape($data['descriptions'][$this->config->get('config_language_id')]) . "' AND apd.language_id = '".(int)$this->config->get('config_language_id')."'");
			if ($query->num_rows) {
				foreach ($query->rows as $att) {
					$preset_id = $att['preset_id'];
					$query_all = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute_presets_description WHERE preset_id = '" . (int)$preset_id . "'");			
					$exists = true;
					foreach ($query_all->rows as $value) {
						if ((!isset($data['descriptions'][$value['language_id']]) && $value['text'] != '') 
							|| (isset($data['descriptions'][$value['language_id']]) && $value['text'] != $data['descriptions'][$value['language_id']]))
							$exists = false;
					}
					if ($exists) return $preset_id;
				}				
				return 0;
			} else return 0;
		} else return 0;
	}
	
	public function editPreset($preset_id, $data) {

		$this->db->query("UPDATE " . DB_PREFIX . "attribute_presets SET attribute_id = '" . (int)$data['attribute_id'] . "' WHERE preset_id = '".$preset_id."'");
		$old_texts_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute_presets_description WHERE preset_id = '" . (int)$preset_id . "'");
		$old_texts = array();
		foreach ($old_texts_query->rows as $old) {
			$old_texts[$old['language_id']] = $old['text'];
		}
		$this->db->query("DELETE FROM " . DB_PREFIX . "attribute_presets_description WHERE preset_id = '" . (int)$preset_id . "'");

		foreach ($data['descriptions'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "attribute_presets_description SET preset_id = '" . (int)$preset_id . "', language_id = '" . (int)$language_id . "', text = '" . $this->db->escape($value) . "'");
		}	
		
		if ($this->config->get('module_atpresets_autoupdate')==1) {
			$related_products = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product_attribute WHERE preset_id = '".$preset_id."' AND attribute_id = '" . (int)$data['attribute_id'] . "'");
			foreach ($related_products->rows as $related_product) {
				foreach ($data['descriptions'] as $language_id => $value) {
					if ($value != $old_texts[$language_id])
						$this->db->query("UPDATE " . DB_PREFIX . "product_attribute pa SET text = '".$this->db->escape($value)."' WHERE pa.product_id = '".$related_product['product_id']."' AND pa.language_id = '" . (int)$language_id . "' AND pa.attribute_id = '" . (int)$data['attribute_id'] . "' AND preset_id = '".$preset_id."'");	
				}
			}
			
		} else {
			$this->db->query("UPDATE " . DB_PREFIX . "product_attribute SET preset_id=0 WHERE preset_id = '".$preset_id."' AND attribute_id = '" . (int)$data['attribute_id'] . "'");			
		}
		
	}
	
	public function deletePreset($preset_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "attribute_presets WHERE preset_id = '" . (int)$preset_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "attribute_presets_description WHERE preset_id = '" . (int)$preset_id . "'");
		$this->db->query("UPDATE " . DB_PREFIX . "product_attribute SET preset_id = 0 WHERE preset_id = '" . (int)$preset_id . "'");
	}
		
	public function getPreset($preset_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute_presets WHERE preset_id = '" . (int)$preset_id . "'");
		$query_desc = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute_presets_description WHERE preset_id = '" . (int)$preset_id . "'");

		$result = $query->row;
		$result['descriptions'] = array();
		foreach ($query_desc->rows as $desc) {
			$result['descriptions'][$desc['language_id']] = $desc['text'];
		}
		$result['text'] = $result['descriptions'][$this->config->get('config_language_id')];
		return $result;
	}
	
	public function getAttributeName($attribute_id) {
		$query = $this->db->query("SELECT ad.name FROM " . DB_PREFIX . "attribute a LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE a.attribute_id = '" . (int)$attribute_id . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row['name'];
	}
	
	public function getPresets($data = array()) {
	
		$sql = "SELECT * FROM " . DB_PREFIX . "attribute_presets ap left join " . DB_PREFIX . "attribute_presets_description apd on(ap.preset_id=apd.preset_id)";
      	$sql .= " WHERE apd.language_id = '". (int)$this->config->get('config_language_id') ."'";
		
		if (isset($data['attribute_id'])) {
			$sql .= "  AND ap.attribute_id = '". (int)$data['attribute_id'] ."'";	
		}
		
		$sql .= " ORDER BY apd.text ASC";	
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}					

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
		
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}			
		
		$query = $this->db->query($sql);
	
		return $query->rows;
	}

	
	public function getPresetTitle($preset_id) {
		$query = $this->db->query("SELECT text FROM " . DB_PREFIX . "attribute_presets_description WHERE preset_id='".$preset_id."' AND language_id = '". (int)$this->config->get('config_language_id') ."'");
      	
		if ($query->num_rows)
			return (utf8_strlen($query->row['text'])>50)?substr($query->row['text'], 0, 50).'...':$query->row['text'];
		else 
			return '';
	}
	
	public function getPresetTexts($preset_id) {
		$query_desc = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute_presets_description WHERE preset_id = '" . (int)$preset_id . "'");

		$result = array();
		foreach ($query_desc->rows as $desc) {
			$result[$desc['language_id']] = html_entity_decode($desc['text'],ENT_QUOTES, 'UTF-8');
		}
		
		return $result;
	}

	public function getManyPresetsTexts($presets) {
		foreach ($presets as $key => $preset_id) if (!($preset_id>0)) unset($presets[$key]);
		
		if (count($presets)>0) {
			$presets_string = implode(',',$presets);
			$query_desc = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute_presets_description WHERE preset_id IN (" . $presets_string . ") AND language_id = '". (int)$this->config->get('config_language_id') ."' order by text ASC");
			
			$result = array();
			foreach ($query_desc->rows as $desc) {
				$result[$desc['preset_id']][$desc['language_id']] = html_entity_decode($desc['text'],ENT_QUOTES, 'UTF-8');
			}
			
			$query_desc = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute_presets_description WHERE preset_id IN (" . $presets_string . ")");
			foreach ($query_desc->rows as $desc) {
				$result[$desc['preset_id']][$desc['language_id']] = html_entity_decode($desc['text'],ENT_QUOTES, 'UTF-8');
			}	
			return $result;			
		} else {
			return array();
		}
		
	}
	
	public function getPresetsAuto($data = array()) {
	
		$sql = "SELECT * FROM " . DB_PREFIX . "attribute_presets ap left join " . DB_PREFIX . "attribute_presets_description apd on(ap.preset_id=apd.preset_id)";
      		
		if ($data['attribute_id']=='') {
			$sql .= " left join " . DB_PREFIX . "attribute_description ad on(ad.attribute_id=ap.attribute_id)";
		}
		
		$sql .= " WHERE apd.language_id = '". (int)$this->config->get('config_language_id') ."'";
		
		if (isset($data['filter_name'])) {
			$sql .= " AND apd.text LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}		
		
		if ($data['attribute_id'] != '') {
			$sql .= " AND ap.attribute_id='".$data['attribute_id']."'";
			$sql .= " ORDER BY apd.text ASC";				
		} else {
			$sql .= " AND ad.language_id = '". (int)$this->config->get('config_language_id') ."'";					
			$sql .= " ORDER BY ad.name ASC, ap.attribute_id, apd.text ASC";
		}
				
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}					

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
		
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}			
		
		$query = $this->db->query($sql);
	
		return $query->rows;
	}
	
	public function getTotalPresets() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "attribute_presets ");
		
		return $query->row['total'];
	}	
}
?>