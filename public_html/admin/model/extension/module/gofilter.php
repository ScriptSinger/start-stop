<?php
class ModelExtensionModuleGofilter extends Model {

	public function createGofilter() {
			
		$res0 = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "gofilter'");
		if($res0->num_rows == 0){
			$this->db->query("
				CREATE TABLE IF NOT EXISTS `". DB_PREFIX. "gofilter` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `code` varchar(255) NOT NULL,
				  `key` varchar(255) NOT NULL,
				  `value` longtext NOT NULL,
				  `serialized` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
			");
		}
	
	}
	
	public function getGofilter($code) {
		$setting_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "gofilter WHERE `code` = '" . $this->db->escape($code) . "'");

		foreach ($query->rows as $result) {
			$setting_data[$result['key']] = $result['value'];
		}

		return $setting_data;
	}

	public function editGofilter($code, $data) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "gofilter` WHERE `code` = '" . $this->db->escape($code) . "'");

		foreach ($data as $key => $value) {
			if (substr($key, 0, strlen($code)) == $code) {
				if (!is_array($value)) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "gofilter SET `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "gofilter SET `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(json_encode($value)) . "', serialized = '1'");
				}
			}
		}
	}
	
	public function getRouting($route_id) {
		$query = $this->db->query("SELECT route FROM " . DB_PREFIX . "layout_route WHERE layout_id = '" . (int)$route_id . "'");
		if($query->num_rows){
			$route = $query->row['route'];
		} else {
			$route = false;
		}
		return $route;
	}
	
	public function getStore($route_id) {
		$query = $this->db->query("SELECT store_id FROM " . DB_PREFIX . "layout_route WHERE layout_id = '" . (int)$route_id . "'");
		if($query->num_rows){
			$store_id = $query->row['store_id'];
		} else {
			$store_id = false;
		}
		return $store_id;
	}
	
	public function getCategoryname($category_id) {
		if ($category_id) {
			$query = $this->db->query("SELECT name FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
			if (isset($query->row['name'])) {$category_name = $query->row['name'];} else {$category_name = '';}
		
			return $category_name;
		} else {
			return $category_name = false;
		}
	}
	
	public function getmodelAttributes($filter_name) {
			$results = array();
			
			$sql_attributes = "SELECT DISTINCT ad.attribute_id,ad.name as name_attribute,agd.name as name_group FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_attribute pa ON (p.product_id = pa.product_id) INNER JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) INNER JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) INNER JOIN " . DB_PREFIX . "attribute_group_description agd ON (a.attribute_group_id = agd.attribute_group_id) INNER JOIN " . DB_PREFIX . "product_to_store p2s ON (pa.product_id = p2s.product_id) WHERE p.status = '1' AND ad.name LIKE '%" . $this->db->escape($filter_name) . "%' AND pa.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
			
			$query_attributes = $this->db->query($sql_attributes);
		
			if ($query_attributes->num_rows) {
				
				foreach ($query_attributes->rows as $value) {
					if ($value['name_attribute']) {
						$results[] = array(
							'attribute_id' 		=> (int)$value['attribute_id'],
							'name_attribute' 	=> $value['name_attribute'],
							'name_group' 		=> $value['name_group']
						);
					}
				}
			}
		
		$results = array_map("unserialize", array_unique(array_map("serialize", $results)));
		
		return $results;
	}
	
	public function getNameAttributes($attribute_id) {
		$sql_name_attributes = $this->db->query("SELECT name FROM " . DB_PREFIX . "attribute_description WHERE attribute_id = " . (int)$attribute_id . " AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
		if (isset($sql_name_attributes->row['name'])) {$name = $sql_name_attributes->row['name'];} else {$name = false;}
		
		return $name;
	}
	
	public function getNameOptions($option_id) {
		$sql_name_options = $this->db->query("SELECT name FROM " . DB_PREFIX . "option_description WHERE option_id = " . (int)$option_id . " AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
		if (isset($sql_name_options->row['name'])) {$name_option = $sql_name_options->row['name'];} else {$name_option = false;}
		
		return $name_option;
	}
	public function getNameOptionsValue($option_value_id) {
		$sql_name_options_value = $this->db->query("SELECT name FROM " . DB_PREFIX . "option_value_description WHERE option_value_id = " . (int)$option_value_id . " AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
		if (isset($sql_name_options_value->row['name'])) {$name_option_value = $sql_name_options_value->row['name'];} else {$name_option_value = false;}
		
		return $name_option_value;
	}
	
	public function getmodelOptions($filter_name) {
			$results = array();
			
			$sql_options = "SELECT pov.option_id,od.name as name_option,pov.option_value_id,ovd.name as name_option_value FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_option_value pov ON (p.product_id = pov.product_id) LEFT JOIN " . DB_PREFIX . "option o ON (pov.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (pov.product_id = p2s.product_id) WHERE p.status = '1' AND ovd.name LIKE '%" . $this->db->escape($filter_name) . "%' AND pov.option_id IS NOT NULL AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' GROUP BY pov.option_id";
			
			$query_options = $this->db->query($sql_options);
		
			if ($query_options->num_rows) {
				foreach ($query_options->rows as $value) {
					if ($value['name_option']) {
						$results[] = array(
							'option_id' 			=> (int)$value['option_id'],
							'name_option' 			=> $value['name_option']
						);
					}
				}
			}
		
		$results = array_map("unserialize", array_unique(array_map("serialize", $results)));
		
		return $results;
	}
	
	public function getClearGarbage($clear_garbage, $massiv_table) {
		
		$result = array();
		if ($massiv_table) {
			foreach ($massiv_table as $keys => $values) {
				foreach ($clear_garbage as $key => $value) {
					$num_rows = $this->db->query("SELECT " . $values . ",COUNT(DISTINCT " . $values . ") as total FROM " . $keys . " WHERE " . $values . " LIKE '%" . $key . "%'");
					if ($num_rows->num_rows) {
						$res = $this->db->query("UPDATE " . $keys . " SET " . $values . " = REPLACE(" . $values . ", '" . $key . "', '" . $value . "')");
						
						foreach ($num_rows->rows as $val) {
							$result[$keys][] = $val['total'];
						}
						
					}
				}
			}
		}

		return $result;
	}
}
