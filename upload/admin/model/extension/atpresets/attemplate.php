<?php
class ModelExtensionAtpresetsAttemplate extends Model {

	public function addAttemplate($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "attemplate SET name = '" . $this->db->escape($data['name']) . "', status = '" . (int)$data['status'] . "'");
		$attemplate_id = $this->db->getLastId();	
		
		foreach ($data['attributes'] as $attribute){
			foreach ($attribute['preset_id'] as $preset_id)
				if ($preset_id>0)
					$this->db->query("INSERT INTO " . DB_PREFIX . "attemplate_attribute SET attemplate_id = '" . (int)$attemplate_id . "', preset_id = '" . (int)$preset_id . "', attribute_id = '" .(int)$attribute['attribute_id'] . "'");
				else if (count($attribute['preset_id'])==1)
					$this->db->query("INSERT INTO " . DB_PREFIX . "attemplate_attribute SET attemplate_id = '" . (int)$attemplate_id . "', preset_id = '0', attribute_id = '" .(int)$attribute['attribute_id'] . "'");
		}
		return $attemplate_id;
	}
	
	public function editAttemplate($attemplate_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "attemplate SET name = '" . $this->db->escape($data['name']) . "', status = '" . (int)$data['status'] . "' WHERE attemplate_id = '" . (int)$attemplate_id . "'");
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "attemplate_attribute WHERE attemplate_id = '" . (int)$attemplate_id . "'");
		foreach ($data['attributes'] as $attribute){
			foreach ($attribute['preset_id'] as $preset_id)
				if ($preset_id>0)
					$this->db->query("INSERT INTO " . DB_PREFIX . "attemplate_attribute SET attemplate_id = '" . (int)$attemplate_id . "', preset_id = '" . (int)$preset_id . "', attribute_id = '" .(int)$attribute['attribute_id'] . "'");
				else if (count($attribute['preset_id'])==1)
					$this->db->query("INSERT INTO " . DB_PREFIX . "attemplate_attribute SET attemplate_id = '" . (int)$attemplate_id . "', preset_id = '0', attribute_id = '" .(int)$attribute['attribute_id'] . "'");		}	
	}
	
	public function deleteAttemplate($attemplate_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "attemplate WHERE attemplate_id = '" . (int)$attemplate_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "attemplate_attribute WHERE attemplate_id = '" . (int)$attemplate_id . "'");	
	}
		
	public function getAttemplate($attemplate_id) {

		$attemplate_data = array();
		
		if ((int)$attemplate_id!=0){
			$attemplate_query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "attemplate  WHERE attemplate_id = '" . (int)$attemplate_id . "'");
			
			$attemplate_data = array(
				'attemplate_id'  				=> $attemplate_query->row['attemplate_id'],		
				'name'          				=> $attemplate_query->row['name'],
				'status' 						=> $attemplate_query->row['status']
			);
		} 
	
		return $attemplate_data;	
	}

	public function getAttemplateAttributes($attemplate_id) {

		$attemplate_attributes = array();

		$attemplate_attribute_query = $this->db->query("SELECT aa.attribute_id FROM " . DB_PREFIX . "attemplate_attribute aa LEFT JOIN " . DB_PREFIX . "attribute a ON (aa.attribute_id = a.attribute_id) WHERE aa.attemplate_id = '" . (int)$attemplate_id . "' GROUP BY aa.attribute_id ORDER BY a.sort_order ASC");

		foreach ($attemplate_attribute_query->rows as $attribute) {

			$this->load->model('extension/atpresets/atpresets');

			$preset_id = array();
			$sql = "SELECT preset_id FROM " . DB_PREFIX . "attemplate_attribute WHERE attribute_id = '". (int)$attribute['attribute_id'] ."' and attemplate_id='".(int)$attemplate_id."'";			
			$query = $this->db->query($sql);
		
			foreach ($query->rows as $preset_ids) {
				$preset_id[] = $preset_ids['preset_id'];
			}	

			$attemplate_attributes[] = array(
				'attribute_id'               => $attribute['attribute_id'],
				'preset_id'                  => $preset_id
			);
		}

		return $attemplate_attributes;		
	}
	
	public function getAttemplates($data = array()) {
	
		$sql = "SELECT * FROM " . DB_PREFIX . "attemplate";	
	
		$sort_data = array(
			'name',			
			'status'
		);	

		if (isset($data['status'])) {
			$sql .= " WHERE status = '".$data['status']."'";	
		} 
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY name";	
		}
			
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
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
	
	public function getTotalAttemplates($data = array()) {

		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "attemplate";
	
		$query = $this->db->query($sql);		
		return $query->row['total'];
	}
}
?>