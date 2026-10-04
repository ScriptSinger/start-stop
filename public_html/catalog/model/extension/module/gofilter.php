<?php
class ModelExtensionModuleGofilter extends Model {
	
private $tax_rates = array();
	
public function getGofilter($code) {
		$setting_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "gofilter WHERE `code` = '" . $this->db->escape($code) . "'");
		
		if ($query->num_rows) {
			foreach ($query->rows as $result) {
				$setting_data[$result['key']] = $result['value'];
			}
		}

		return $setting_data;
}
	
public function getRoute($route) {
		$data_route = array();
		
		$query_route = $this->db->query("SELECT layout_id FROM " . DB_PREFIX . "layout_route WHERE '" . $this->db->escape($route) . "' LIKE route AND store_id = '" . (int)$this->config->get('config_store_id') . "'");
		
		if ($query_route->rows) {$data_route = $query_route->row['layout_id'];}
		
		return $data_route;
}

public function getParentcategory($category_id) {

		$data_parent = false;
		
		if ($category_id) {
			$query_parent = $this->db->query("SELECT path_id FROM " . DB_PREFIX . "category_path WHERE category_id = '" . (int)$category_id . "'");
		
			if ($query_parent->rows) {
				if ($query_parent->row['path_id'] != $category_id) {
					if ($query_parent->rows) {$data_parent = $query_parent->row['path_id'];}
				}
			}
		}

		return $data_parent;
}

public function getChildcategory($category_massiv_id) {
	
	$category_array = array();
	
	if ($category_massiv_id) {
		foreach ($category_massiv_id as $key => $category_id) {
			$query_child = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category_path WHERE path_id = '" . (int)$category_id . "'");
			if ($query_child->num_rows) {
				foreach ($query_child->rows as $value) {
					$category_array[] = (int)$value['category_id'];
				}
			}
		}
	}

	return $category_array;
}
	
public function testSeparator($sperarator, $value) {
	$name = "";
	if (strpos($value, $sperarator) !== false) {
		$d = 0; foreach (explode($sperarator, (string)$value) as $value_delimitier) {
			if ($d > 0) {$name .= " ";}
			$name .= $value_delimitier;
			$d = $d + 1;
		}
	} else {
		$name = $value;
	}
	$name = str_replace('--', ',', rawurldecode($name));
	return $name;
}

public function resultSeparator($sperarator, $sper, $value) {
	$name = "";
	if (strpos($value, $sperarator) !== false) {
		$d = 0; foreach (explode($sperarator, (string)$value) as $value_delimitier) {
			if ($d > 0) {$name .= $sper;}
			$name .= $value_delimitier;
			$d = $d + 1;
		}
	} else {
		$name = $value;
	}
	$name = str_replace('--', ',', rawurldecode($name));
	return $name;
}

public function getCategories($parent_id = 0, $getCache) {
	$sql_categories = "SELECT c.category_id,cd.name FROM " . DB_PREFIX . "category c INNER JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) INNER JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name)";

	if (!$getCache or ($getCache and !$this->cache->get('options.gofilter.categories.name.' . (int)$parent_id))) {
		$query_categories = $this->db->query($sql_categories);
		if ($query_categories->num_rows) {$query_categories_rows = $query_categories->rows;} else {$query_categories_rows = false;}

		if ($getCache and isset($query_categories_rows)) {$this->cache->set('options.gofilter.categories.name.' . (int)$parent_id, $query_categories_rows);}
	} else {
		$query_categories_rows = $this->cache->get('options.gofilter.categories.name.' . (int)$parent_id);
	}
	
	return $query_categories_rows;
}

public function getCategory($category_id) {
	$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c.category_id = '" . (int)$category_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'");

	return $query->row;
}

public function getGeneraionSql($common_value_id, $stockStock, $job_view) {
	unset($common_value_id['old_category_id']);	unset($common_value_id['path']); unset($common_value_id['route_layout']); unset($common_value_id['select']); unset($common_value_id['class']); unset($common_value_id['op']); unset($common_value_id['prices_max_value']); unset($common_value_id['prices_min_value']); unset($common_value_id['route']); unset($common_value_id['gofilter']); unset($common_value_id['nofilter']); unset($common_value_id['nofilter']);
	
	if (isset($common_value_id['manufacturer_id'])) {$common_value_id['manufacturers_filter'][] = $common_value_id['manufacturer_id'];}
	unset($common_value_id['manufacturer_id']);
	if (isset($common_value_id['search']) and $common_value_id['search'] != "") {$common_value_id['keywords_filter'] = $common_value_id['search'];}
	unset($common_value_id['search']); unset($common_value_id['search_tablet']); unset($common_value_id['search_top']); unset($common_value_id['special']);
	
	if(isset($common_value_id['categ_id'])) {$common_value_id['category_id'] = $common_value_id['categ_id'];}

		$sql = "SELECT DISTINCT p.product_id,p.manufacturer_id,p.price,p.tax_class_id,p.quantity,p.stock_status_id FROM " . DB_PREFIX . "product p";
		
		$sql .= " INNER JOIN " . DB_PREFIX . "product_to_store AS p2sq ON p.product_id = p2sq.product_id";
		
		if (isset($common_value_id['option_filter'])) {
			$sql .= " INNER JOIN " . DB_PREFIX . "product_option_value x ON (p.product_id = x.product_id)";
		}
		
		if (isset($common_value_id['filter_filter'])) {
			$sql .= " INNER JOIN " . DB_PREFIX . "product_filter f ON (p.product_id = f.product_id)";
		}
			
		if (isset($common_value_id['attributes_filter'])) {
			$sql .= " INNER JOIN " . DB_PREFIX . "product_attribute xc ON (p.product_id = xc.product_id)";
		}
		
		if (isset($common_value_id['rating_filter'])) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "review r ON (p.product_id = r.product_id)";
		}
		
		if (isset($this->request->post['route_layout'])) {
			if ($this->request->post['route_layout'] == 'product/special') {
				$sql .= " INNER JOIN " . DB_PREFIX . "product_special AS ps ON p.product_id = ps.product_id";
			}
		}
		if (isset($this->request->get['special'])) {
			if ($this->request->get['special'] == 'product/special') {
				$sql .= " INNER JOIN " . DB_PREFIX . "product_special AS ps ON p.product_id = ps.product_id";
			}
		}
		
		if (isset($common_value_id['keywords_filter'])) {
			if ($common_value_id['keywords_filter'] != "") {
				$keywords = strtolower($common_value_id['keywords_filter']);
				if(strlen($keywords) >= 1) {
					$sql .= " INNER JOIN " . DB_PREFIX . "product_description AS pdq ON p.product_id = pdq.product_id";
					$sql .= " LEFT JOIN " . DB_PREFIX . "manufacturer AS pmq ON p.manufacturer_id = pmq.manufacturer_id";
				}
				
			}
		}
		
		if (isset($common_value_id['category_id'])) {
			$sql .= " INNER JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) LEFT JOIN " . DB_PREFIX . "category_path cp ON (cp.category_id = p2c.category_id)";
		}
		
		$sql .= " WHERE";
		
		$sql .= " p2sq.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p.status = '1'";

		if (isset($common_value_id)) {$temp_common_value_id = $common_value_id; unset($temp_common_value_id['rating_filter']); unset($temp_common_value_id['sort']); unset($temp_common_value_id['order']); unset($temp_common_value_id['limit']); unset($temp_common_value_id['page']);unset($temp_common_value_id['go_mobile']);unset($temp_common_value_id['_route_']);} else {$temp_common_value_id = false;}
		
		if ($temp_common_value_id) {
			$sql .= " AND";
		}

		if (isset($common_value_id['option_filter'])) {
			
			$count_options_select_group = $this->test_Count_options_group_select($common_value_id['option_filter']);
			
			if ($count_options_select_group == 1) {
				$sql_compare = " AND";
			} else {
				$sql_compare = " AND";
			}
			
			if ($job_view == 1) {$sql_compare = " OR";}
			
			$kolvo_option = 0;
			
			$sql .=  " (";
			foreach ($common_value_id['option_filter'] as $product_option_id => $value) {
				if ($kolvo_option == 0) {
					$sql .= " x.option_value_id = '" . (int)$value . "' AND x.quantity <> 0";
				} else {
					$sql .= $sql_compare;
					$sql .= " x.product_id IN (SELECT x2.product_id  FROM " . DB_PREFIX . "product_option_value x2 WHERE x2.option_value_id = '" . (int)$value . "')";
				}
				
				$kolvo_option = $kolvo_option + 1;
			}
			$sql .=  ") ";
			
			if (isset($common_value_id['filter_filter']) or isset($common_value_id['attributes_filter']) or isset($common_value_id['manufacturers_filter']) or isset($common_value_id['stock_status_filter']) or (isset($common_value_id['keywords_filter']) and ($common_value_id['keywords_filter'] != "")) or isset($common_value_id['category_id'])) {
				$sql .= " AND";
			}
		
		}
		
		if (isset($common_value_id['filter_filter'])) {

			$sql_compare = " AND";
			if ($job_view == 1) {$sql_compare = " OR";}
			
			$kolvo_option = 0;
			
			$sql .=  " (";
			foreach ($common_value_id['filter_filter'] as $product_filter_id => $value) {
				
				if ($kolvo_option == 0) {
					$sql .= " f.product_id IN (SELECT f2.product_id  FROM " . DB_PREFIX . "product_filter f2 WHERE f2.filter_id = '" . (int)$value . "')";
				} else {
					$sql .= $sql_compare;
					$sql .= " f.product_id IN (SELECT f2.product_id  FROM " . DB_PREFIX . "product_filter f2 WHERE f2.filter_id = '" . (int)$value . "')";
				}
				
				$kolvo_option = $kolvo_option + 1;
			}
			$sql .=  ") ";
			
			if (isset($common_value_id['attributes_filter']) or isset($common_value_id['manufacturers_filter']) or isset($common_value_id['stock_status_filter']) or (isset($common_value_id['keywords_filter']) and ($common_value_id['keywords_filter'] != "")) or isset($common_value_id['category_id'])) {
				$sql .= " AND";
			}
		}
		
		if (isset($common_value_id['attributes_filter'])) {
			$sql_compare = " AND";
			if ($job_view == 1) {$sql_compare = " OR";}
			$kolvo_attribute = 0;
			
			$sql .=  " (";
			foreach ($common_value_id['attributes_filter'] as $product_attribute => $value) {
			     foreach ($value as $key_1 => $value_1) {
    				if (isset($value_1)) {
    					if ($kolvo_attribute == 0) {
    						$sql .= " xc.text = '" . $this->db->escape($value_1) . "' AND xc.attribute_id = '" . (int)$product_attribute . "'";
    					} else {
							$sql .= $sql_compare;
    						$sql .= " xc.product_id IN (SELECT xc2.product_id  FROM " . DB_PREFIX . "product_attribute xc2 WHERE xc2.text = '" . $this->db->escape($value_1) . "' AND xc2.attribute_id = '" . (int)$product_attribute . "')";
    					}
                        $kolvo_attribute = $kolvo_attribute + 1;
    				}
                }
			}
			$sql .=  ") ";
			
			if (isset($common_value_id['manufacturers_filter']) or isset($common_value_id['stock_status_filter']) or (isset($common_value_id['keywords_filter']) and ($common_value_id['keywords_filter'] != "")) or isset($common_value_id['category_id'])) {
				$sql .= " AND";
			}
		}
	
		if (isset($common_value_id['manufacturers_filter']) and !in_array("", $common_value_id['manufacturers_filter'])) {

		if (is_array($common_value_id['manufacturers_filter'])) {
				foreach ($common_value_id['manufacturers_filter'] as $key_manufacturer => $value_manufacturer) {
					$manufacturer_values_data[] = $value_manufacturer;
				}
			} else {
				$manufacturer_values_data[] = $common_value_id['manufacturers_filter'];
			}
			
			$manufacturer_values_data = array_map("unserialize", array_unique(array_map("serialize", $manufacturer_values_data)));
			
			$h = 0;
			$sql .=  " (";
			foreach ($manufacturer_values_data as $stock_key => $manufacturer_value) {
				$h = $h + 1;
				if ($h == 1) {
					$sql .=  "p.manufacturer_id = '" . (int)$manufacturer_value . "'";
				} else {
					$sql .=  " OR p.manufacturer_id = '" . (int)$manufacturer_value . "'";
				}
			}
			$sql .=  ")";
			
			if (isset($common_value_id['stock_status_filter']) or (isset($common_value_id['keywords_filter']) and ($common_value_id['keywords_filter'] != "")) or isset($common_value_id['category_id'])) {
				$sql .= " AND";
			}
		}
			
		if (isset($common_value_id['stock_status_filter'])) {
				$temp_array_stock_status_values_data = array();
				foreach ($common_value_id['stock_status_filter'] as $key_status => $value_status) {
					$temp_array = explode('-', (string)$value_status);
					$temp_array_stock_status_values_data[] = $temp_array;
				}
				
				$stock_status_values_data = array();
				if (is_array($common_value_id['stock_status_filter'])) {
					foreach ($common_value_id['stock_status_filter'] as $key => $value) {
							$stock_status_values_data[] = $value;
					}
				} else {
					$stock_status_values_data[] = $common_value_id['stock_status_filter'];
				}
				
				$stock_status_values_data = array_map("unserialize", array_unique(array_map("serialize", $stock_status_values_data)));
				$this->load->language('extension/module/gofilter');
				$sum = 0; $d = 0;
				$sql .=  " (";
				foreach ($stock_status_values_data as $stock_key => $stock_value) {
					
					if ($sum > 0) {$sql .=  " OR ";}
					
					if ($stock_value == "stock") {
						$sql .=  "p.quantity > 0 OR p.stock_status_id = (SELECT stock_status_id FROM " . DB_PREFIX . "stock_status WHERE name = '" . $this->language->get('text_stock') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "')";
					} elseif ($stock_value == 0 and $d == 0) {
						$sql .=  "p.quantity <= 0";
					} else {
						$sql .=  "p.stock_status_id = '" . (int)$stock_value . "' AND p.quantity <= 0";
						$d = $d + 1;
					}
					
					$sum = $sum + 1;
				
				}
				$sql .=  ")";
				
				if ((isset($common_value_id['keywords_filter']) and ($common_value_id['keywords_filter'] != "")) or isset($common_value_id['category_id'])) {
					$sql .= " AND";
				}
		}
			
		if (isset($common_value_id['keywords_filter']) and ($common_value_id['keywords_filter'] != "")) {
			if ($common_value_id['keywords_filter'] != null) {
				$keywords = $common_value_id['keywords_filter'];
				if(strlen($keywords) >= 1) {
					
				if ($this->getDelimitier()) {$delimitier = $this->getDelimitier();} else {$delimitier = " ";}

				$parts = explode($delimitier, $keywords);
					$sql .= ' (';
					
					$u = 0;
					foreach($parts as $part) {
						$u = $u + 1;
						if ($u == 1) {
							$sql .= '(LOWER(p.model) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pdq.name) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pdq.meta_title) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pdq.tag) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pdq.meta_description) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pdq.meta_keyword) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pmq.name) LIKE "%' . $this->db->escape($part) . '%")';
						} else {
							$sql .= ' OR ';
							$sql .= '(LOWER(p.model) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pdq.name) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pdq.meta_title) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pdq.tag) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pdq.meta_description) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pdq.meta_keyword) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pmq.name) LIKE "%' . $this->db->escape($part) . '%")';
						}							
					}
					
					$sql .= ')';
					
					$sql .= ' AND pdq.language_id = ' . (int)$this->config->get('config_language_id');

				}
				
			}
			if (isset($common_value_id['category_id'])) {
				$sql .= " AND";
			}
		}
		
		if (isset($common_value_id['category_id'])) {
			$sql .= " IF(cp.path_id IS NOT NULL, cp.path_id, p2c.category_id) = '" . (int)$common_value_id['category_id'] . "'";
		}
		
		if (isset($this->request->post['route_layout'])) {
			if ($this->request->post['route_layout'] == 'product/special') {
				$sql .= " AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()) AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "')";
			}
		}
		
		if (isset($this->request->get['special'])) {
			if ($this->request->get['special'] == 'product/special') {
				$sql .= " AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()) AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "')";
			}
		}

		if ($stockStock) {
			$sql .= " AND p.quantity > 0";
		}
		if (isset($common_value_id['rating_filter'])) {
			$sql .= " GROUP BY p.product_id";
		}
		
		if (isset($common_value_id['rating_filter'])) {
			$sql .= " HAVING";
			$or = 0;
			foreach ($common_value_id['rating_filter'] as $key => $value) {
				if ($or > 0) {$sql .= " OR ";}
				if ($value != 0) {
					$sql .= " ROUND(AVG(r.rating), 0) = '" . (int)$value . "'";
				} else {
					$sql .= " COUNT(r.product_id) = 0";
				}
				$or++;
			}
		}
		
		return $sql;
}

public function exec_time($phase = 'run', $float_round = 6){
	static $prev_time, $collect;

	list( $usec, $sec ) = explode(' ', microtime() );
	$microtime = bcadd( $usec, $sec, 8 );

	if( $prev_time ){
		$exectime = bcsub( $microtime, $prev_time, 8 );
		$collect  = bcadd( $collect, $exectime, 8 );
	}

	$prev_time = ( $phase === 'save' ) ? 0 : $microtime;

	if( $phase === 'end' ){
		$out = round( $collect, $float_round );
		$collect = $prev_time = 0; // clear
		return $out;
	}
}

public function getParameterProducts($common_value_id, $quantity_view, $stockStock, $status_massivs, $job_view) {

		$sql = $this->getGeneraionSql($common_value_id, $stockStock, $job_view);
		
		$this->db->query("CREATE TEMPORARY TABLE IF NOT EXISTS temp_result_table (INDEX indextempres (product_id)) AS (" . $sql . ")");
		
		$select = false;
		if (isset($this->request->get['select'])) {$select = $this->request->get['select'];}
		if (isset($this->request->post['select'])) {$select = $this->request->post['select'];}
		unset($this->request->post['select']); unset($this->request->get['select']);
		
		$attribute_id_data = array();
		$attribute_sort_order = array();
		
		$products_id = array();
		
		$this->load->model('tool/image');

		$common_value_data = array();

		if (isset($status_massivs['status_attributes'])) {
			$attribute_value_id_query = $this->db->query("SELECT pa.attribute_id,pa.text" . ($quantity_view ? ',COUNT(DISTINCT pa.product_id) AS total' : ',-1 as total') . " FROM temp_result_table p INNER JOIN " . DB_PREFIX . "product_attribute pa ON (p.product_id = pa.product_id) WHERE pa.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY pa.attribute_id,pa.text");
			
			$attribute_values = array();
			if ($attribute_value_id_query->num_rows) {
				foreach ($attribute_value_id_query->rows as $attribute_value_id) {
					$attribute_values[$attribute_value_id['attribute_id']][] = array(
						'text'				=> $attribute_value_id['text'],
						'total'				=> $attribute_value_id['total'],
					);
					$common_value_data['attributes'] =  $attribute_values;
				}
			}
		}
		
		if (isset($status_massivs['status_options'])) {
			$option_value_id_query = $this->db->query("SELECT pov.option_id,pov.option_value_id" . ($quantity_view ? ',COUNT(DISTINCT pov.product_id) as total' : ',-1 as total') . " FROM temp_result_table p INNER JOIN " . DB_PREFIX . "product_option_value pov ON (p.product_id = pov.product_id) GROUP BY pov.option_value_id");
			
			$option_id_data = array();
			$temp_option_id_data = array();
			if ($option_value_id_query->num_rows) {
				$option_value_id_query_rows = array_map("unserialize", array_unique(array_map("serialize", $option_value_id_query->rows)));
				foreach ($option_value_id_query_rows as $option_val_id) {
					
					$total_quantity_option_value_sql = $this->db->query("SELECT COUNT(DISTINCT p.product_id) as total,pov.option_value_id FROM temp_result_table as p INNER JOIN " . DB_PREFIX . "product_option_value pov ON (p.product_id = pov.product_id) WHERE pov.option_value_id = '" . (int)$option_val_id['option_value_id'] . "' AND pov.quantity <> 0");
					
					if ($total_quantity_option_value_sql->num_rows) {$total_quantity_option_value = $total_quantity_option_value_sql->row['total'];} else {$total_quantity_option_value = 0;}
					
					$temp_option_id_data[(int)$option_val_id['option_id']][] = array(
						'option_value_id' 		=> (int)$option_val_id['option_value_id'],
						'option_value_total' 	=> $total_quantity_option_value,
					);
				}
				foreach ($temp_option_id_data as $temp_key => $temp_value) {
					$option_values = array();
					$option_name = "";
					$option_type = "";
					foreach ($temp_value as $key => $value) {
						$option_values[] = array(
							'option_value_id'          => $value['option_value_id'],
							'option_value_total' 	   => $value['option_value_total'],
						);
					}
					$option_id_data[] = array(
						'option_value'      	   => $option_values
					);
				}
				$option_id_data = array_map("unserialize", array_unique(array_map("serialize", $option_id_data)));
				foreach ($option_id_data as $option_id) {
					$option_values = array();
					if ($option_id['option_value']) {
						foreach ($option_id['option_value'] as $option_value) {
							$option_values[] = array(
								'option_value_id'          => $option_value['option_value_id'],
								'option_value_total' 	   => $option_value['option_value_total'],
							);
						}
					}
					$common_value_data['options'][] = array(
						'option_value'      	   => $option_values
					);
				}
			}
		}

		if (isset($status_massivs['status_filter'])) {
			$query_filters = $this->db->query("SELECT f.filter_id" . ($quantity_view ? ',COUNT(DISTINCT f.product_id) as total' : ',-1 as total') . " FROM temp_result_table p INNER JOIN " . DB_PREFIX . "product_filter f ON (p.product_id = f.product_id) GROUP BY f.filter_id");
			
			if ($query_filters->num_rows) {
				foreach ($query_filters->rows as $key => $value) {
					$common_value_data['filters'][] = array(
						'filter_id' 			=> 	(int)$value['filter_id'],
						'filter_value_total' 	=> 	$value['total'],
					);
				}
			}
		}
		
		$query_categories_child = $this->db->query("SELECT DISTINCT c.category_id" . ($quantity_view ? ',COUNT(DISTINCT ptc.product_id) as total' : ',-1 as total') . " FROM temp_result_table p INNER JOIN " . DB_PREFIX . "product_to_category ptc ON (p.product_id = ptc.product_id) INNER JOIN " . DB_PREFIX . "category_to_store cts ON (cts.category_id = ptc.category_id) INNER JOIN " . DB_PREFIX . "category c ON (c.category_id = cts.category_id) WHERE cts.store_id='0' AND c.status = 1 GROUP BY c.category_id");

		if ($query_categories_child->num_rows) {
			foreach ($query_categories_child->rows as $key_child => $value_child) {
				$common_value_data['categories'][] = array(
					'category_id' 	=> 	(int)$value_child['category_id'],
					'count' 		=> 	$value_child['total'],
				);
			}
		}
		
		if (isset($this->request->get['path'])) {
			$category_id = $this->request->get['path'];
			$pars = explode('_', (string)$this->request->get['path']);
			$category_id = (int)array_pop($pars);
		} else {
			$category_id = false;
		}
		
		if (isset($this->request->post['category_id'])) {
			$category_id = $this->request->post['category_id'];
		}
		
		if (isset($status_massivs['status_manufacturers'])) {
			$query_manufacturers = $this->db->query("SELECT p.manufacturer_id,m.name,m.image" . ($quantity_view ? ',COUNT(DISTINCT p.product_id) as total' : ',-1 as total') . " FROM temp_result_table p INNER JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) GROUP BY p.manufacturer_id");
			
			if ($query_manufacturers->num_rows) {
				
				foreach ($query_manufacturers->rows as $query_manufacturer) {

					if ($query_manufacturer['total']) {$total_manufacturers = $query_manufacturer['total'];} else {$total_manufacturers = "";}

					$common_value_data['manufacturers'][] = array(
						'manufacturer_id'    				=> $query_manufacturer['manufacturer_id'],
						'manufacturer_value_total' 	   		=> $total_manufacturers,
					);
				}
			}
		}
		
		if (isset($common_value_data['manufacturers'])) {$common_value_data['manufacturers'] = array_map("unserialize", array_unique(array_map("serialize", $common_value_data['manufacturers'])));}
		
		if (isset($this->request->post['attributes_filter']) or isset($this->request->post['rating_filter']) or isset($this->request->post['keywords_filter']) or isset($this->request->post['option_filter']) or isset($this->request->post['manufacturers_filter']) or isset($this->request->post['prices_max_value'])) {
			$test_click_no_stock_status = true;
		}
		
		$this->load->language('extension/module/gofilter');
		
		if (isset($status_massivs['status_stock'])) {
			$query_stock_statuses_stock = $this->db->query("SELECT IF(p.quantity > 0 OR ps.name = '" . $this->db->escape(mb_strtolower($this->language->get('text_stock'), 'UTF-8')) . "', 'stock', ps.stock_status_id) as stock_status_id,IF(p.quantity > 0, '" . $this->db->escape(mb_strtolower($this->language->get('text_stock'), 'UTF-8')) . "', ps.name) as name" . ($quantity_view ? ',COUNT(DISTINCT p.product_id) as total' : ',-1 as total') . " FROM temp_result_table p INNER JOIN " . DB_PREFIX . "stock_status ps ON (p.stock_status_id = ps.stock_status_id) WHERE ps.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY stock_status_id");
			
			if ($query_stock_statuses_stock->num_rows) {
				foreach ($query_stock_statuses_stock->rows as $query_stock_status_stock) {
					$common_value_data['stock_status'][] = array(
						'stock_status_id'	=>	$query_stock_status_stock['stock_status_id'],
						'total'				=>	$query_stock_status_stock['total'],
					);
				}
			}
		}
	
		if (isset($common_value_data['stock_status'])) {$common_value_data['stock_status'] = array_map("unserialize", array_unique(array_map("serialize", $common_value_data['stock_status'])));}
			$ratings = array();
		
		if (isset($status_massivs['status_ratings'])) {
			$this->db->query("CREATE TEMPORARY TABLE IF NOT EXISTS temp_rating_table (INDEX indextempres (rating_total)) AS (SELECT IF(r.rating IS NOT NULL, ROUND(AVG(r.rating), 0), 0) AS rating_total,IF(r.rating IS NOT NULL,COUNT(DISTINCT r.product_id), COUNT(DISTINCT p.product_id)) as total FROM temp_result_table p LEFT JOIN " . DB_PREFIX . "review r ON (p.product_id = r.product_id) GROUP BY r.product_id)");

			$query_ratings = $this->db->query("SELECT rating_total,IF(rating_total = 0, total, COUNT(rating_total)) as total_count FROM temp_rating_table GROUP BY rating_total;");

			if ($query_ratings->num_rows) {
				foreach ($query_ratings->rows as $rating) {
					$common_value_data['ratings'][] = array(
						'rating' 	=> $rating['rating_total'],
						'total' 	=> $rating['total_count'],
					);
				}
			}
		}
		
		if (isset($status_massivs['status_price'])) {
			$query_prices = $this->db->query("SELECT DISTINCT p.price,p.tax_class_id,(SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount FROM temp_result_table p LEFT JOIN " . DB_PREFIX . "product_special pd ON (p.product_id = pd.product_id)");

			$prices_values = array();
			if ($query_prices->num_rows) {
				foreach ($query_prices->rows as $query_price) {
					if ((float)$query_price['special']) {
						$special = $this->tax->calculate($query_price['special'], $query_price['tax_class_id'], $this->config->get('config_tax'));
					} else {
						if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
							$price = $this->tax->calculate(($query_price['discount'] ? $query_price['discount'] : $query_price['price']), $query_price['tax_class_id'], $this->config->get('config_tax'));
						} else {
							$price = false;
						}
						$special = false;
					}
					$prices_values[] = 	(int)($special ? $special : $price);
				}
			}
			if ($prices_values) {
				$common_value_data['prices'][] = array(
					'max'      => ceil($this->currency->format(max($prices_values), $this->session->data['currency'], '', false)),
					'min'      => floor($this->currency->format(min($prices_values), $this->session->data['currency'], '', false))
				);
			}
		}
			
			$query_counts = $this->db->query("SELECT DISTINCT product_id FROM temp_result_table");
			
			if ($query_counts->num_rows) {
				$temp_arr = array();
				foreach ($query_counts->rows as $key => $value) {
					$temp_arr[] = $value['product_id'];
				}
				if (isset($common_value_id['prices_min_value']) and isset($common_value_id['prices_max_value'])) {
					$common_value_data['count'] = count($this->geProductsPrice($common_value_id, $temp_arr, $stockStock));
				} else {
					$common_value_data['count'] = count($temp_arr);
				}
			} else {
				$common_value_data['count'] = 0;
			}

		return $common_value_data;

}

public function getTotalProducts($categories_enum, $getCache, $stockStock) {
	
	$sql = "SELECT DISTINCT IF(cp.path_id IS NOT NULL, cp.path_id, ptc.category_id) as categ,COUNT(DISTINCT p.product_id) AS total";

	$sql .= " FROM " . DB_PREFIX . "product_to_category ptc INNER JOIN " . DB_PREFIX . "product p ON (ptc.product_id = p.product_id) INNER JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "category_path cp ON (cp.category_id = ptc.category_id)";

	$sql .= " INNER JOIN " . DB_PREFIX . "category_to_store cts ON (cts.category_id = ptc.category_id)";
	
	if (isset($this->request->post['search'])) {
		if ($this->request->post['search'] != "") {
			$keywords = strtolower($this->request->post['search']);
			if(strlen($keywords) >= 1) {
				$sql .= " INNER JOIN " . DB_PREFIX . "product_description AS pdq ON p.product_id = pdq.product_id";
				$sql .= " LEFT JOIN " . DB_PREFIX . "manufacturer AS pmq ON p.manufacturer_id = pmq.manufacturer_id";
			}
			
		}
	}
	if (isset($this->request->get['search'])) {
		if ($this->request->get['search'] != "") {
			$keywords = strtolower($this->request->get['search']);
			if(strlen($keywords) >= 1) {
				$sql .= " INNER JOIN " . DB_PREFIX . "product_description AS pdq ON p.product_id = pdq.product_id";
				$sql .= " LEFT JOIN " . DB_PREFIX . "manufacturer AS pmq ON p.manufacturer_id = pmq.manufacturer_id";
			}
			
		}
	}
	
	if (isset($this->request->get['route_layout'])) {
		if ($this->request->get['route_layout'] == 'product/special') {
			$sql .= " INNER JOIN " . DB_PREFIX . "product_special AS ps ON p.product_id = ps.product_id";
		}
	}
	if (isset($this->request->get['route'])) {
		if ($this->request->get['route'] == 'product/special') {
			$sql .= " INNER JOIN " . DB_PREFIX . "product_special AS ps ON p.product_id = ps.product_id";
		}
	}

	$sql .= " WHERE cts.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
	
	if ($stockStock) {
		$sql .= " AND p.quantity > 0";
	}
	
	if (isset($this->request->get['manufacturer_id'])) {
		$sql .= " AND p.manufacturer_id = '" . (int)$this->request->get['manufacturer_id'] . "'";
	}
	
	if (isset($this->request->get['route_layout'])) {
		if ($this->request->get['route_layout'] == 'product/special') {
			$sql .= " AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()) AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "')";
		}
	}
	if (isset($this->request->get['route'])) {
		if ($this->request->get['route'] == 'product/special') {
			$sql .= " AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()) AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "')";
		}
	}
	
	if (isset($this->request->post['search'])) {
		$sql .= $this->getLikeSearch($this->request->post['search']);
	}
	if (isset($this->request->get['search'])) {
		$sql .= $this->getLikeSearch($this->request->get['search']);
	}

	$sql .= " AND p.product_id IN (SELECT p2c2.product_id FROM " . DB_PREFIX . "product_to_category p2c2 LEFT JOIN " . DB_PREFIX . "category_path cp2 ON (p2c2.category_id = cp2.category_id) WHERE IF(cp2.path_id IS NOT NULL, cp2.path_id, ptc.category_id) IN (" . $categories_enum . ")) GROUP BY IF(cp.path_id IS NOT NULL, cp.path_id, ptc.category_id)";
	
	$categories_key = substr(md5($categories_enum), 10, 0);

	if (!$getCache or ($getCache and !$this->cache->get('categories.gofilter.total.' . $categories_key))) {
		$query_categories = $this->db->query($sql);
		if ($query_categories->num_rows) {$query_categories_rows = $query_categories->rows;} else {$query_categories_rows = false;}

		if ($getCache and isset($query_categories_rows)) {$this->cache->set('categories.gofilter.total.' . $categories_key, $query_categories_rows);}
	} else {
		$query_categories_rows = $this->cache->get('categories.gofilter.total.' . $categories_key);
	}
	
	return $query_categories_rows;
}

public function getSeoElements($filter_array) {
	$options_massiv = array();
	
	$seo_keywords = $this->generationResultsSeo();

	foreach ($filter_array as $key => $value) {
		if (strpos($key, "-price-") !== false) {
			$val = explode("-", $value[0]);
			$this->request->get['prices_min_value'] = $val[0];
			$this->request->get['prices_max_value'] = $val[1];
			$this->request->get['op'] = explode("-", $key)[0];
			if (isset(explode("-", $key)[2])) {$this->session->data['currency'] = strtoupper(explode("-", $key)[2]);}
		}
		if ($key == "manufacturers") {
			foreach ($value as $key_2 => $value_2) {
				$name = $this->testSeparator("_", $value_2);
				$name = $this->resultSeparator("?", "/", $name);
				
				if ($seo_keywords) {
					foreach ($seo_keywords as $key_test_seo => $value_test_seo) {
						if (trim($value_2) == trim($value_test_seo)) {
							$name = $key_test_seo;
						}
					}
				}
				
				$sql_manufacturers = $this->db->query("SELECT DISTINCT manufacturer_id FROM " . DB_PREFIX . "manufacturer WHERE name = '" . $this->db->escape($name) . "'");
				
				if ($sql_manufacturers->num_rows) {
					foreach ($sql_manufacturers->rows as $key_3 => $value_3) {
						$this->request->get['manufacturers_filter'][] = $value_3['manufacturer_id'];
					}
				}
			}
		}
		if ($key == "keywords") {
			$this->request->get['keywords_filter'] = "";
			foreach ($value as $key_2 => $value_2) {
				$name = $this->testSeparator("_", $value_2);
				$name = $this->resultSeparator("?", "/", $name);
				
				if ($key_2 > 0) {
					$this->request->get['keywords_filter'] .= ",";
				}
				$this->request->get['keywords_filter'] .= $name;
			}
		}
		if ($key == "stock") {
			$this->load->language('extension/module/gofilter');
			foreach ($value as $key_2 => $value_2) {
				$name = $this->testSeparator("_", $value_2);
				$name = $this->resultSeparator("?", "/", $name);
				
				if ($seo_keywords) {
					foreach ($seo_keywords as $key_test_seo => $value_test_seo) {
						if (trim($value_2) == trim($value_test_seo)) {
							$name = $key_test_seo;
						}
					}
				}
				
				if ($name == $this->language->get('text_stock')) {
					$this->request->get['stock_status_filter'][] = "stock";
				} else {
					$sql_manufacturers = $this->db->query("SELECT DISTINCT stock_status_id FROM " . DB_PREFIX . "stock_status WHERE name = '" . $this->db->escape($name) . "'");
					if ($sql_manufacturers->num_rows) {
						foreach ($sql_manufacturers->rows as $key_3 => $value_3) {
							$this->request->get['stock_status_filter'][] = $value_3['stock_status_id'];
						}
					}
				}
			}
		}
		if ($key == "rating") {
			foreach ($value as $key_2 => $value_2) {
				$this->request->get['rating_filter'][] = $value_2;
			}
		}
		if ($key == "category") {
			foreach ($value as $key_2 => $value_2) {
				$name = $this->testSeparator("_", $value_2);
				$name = $this->resultSeparator("?", "/", $name);
				
				if ($seo_keywords) {
					foreach ($seo_keywords as $key_test_seo => $value_test_seo) {
						if ($value_2 == $value_test_seo) {
							$name = $key_test_seo;
						}
					}
				}
				
				$condition_sql = ""; $condition_where = "";
				if ($key_2 == 0) {$condition_where = " AND c.parent_id = 0";}
				if ($key_2 > 0 and isset($category_parent_seo)) {
					$condition_sql = " INNER JOIN " . DB_PREFIX . "category_path AS cp ON (c.category_id = cp.category_id)";
					$condition_where = " AND cp.path_id = " . $category_parent_seo;
				}
				
				$sql_parent_id_category = "SELECT DISTINCT cd.category_id FROM " . DB_PREFIX . "category_description cd INNER JOIN " . DB_PREFIX . "category AS c ON (cd.category_id = c.category_id) INNER JOIN " . DB_PREFIX . "category_to_store AS cts ON (c.category_id = cts.category_id)" . $condition_sql . " WHERE cd.name = '" . $this->db->escape($name) . "' AND c.status = 1 AND cts.store_id = '" . (int)$this->config->get('config_store_id') . "'" . $condition_where;
				
				$query_parent_id_category = $this->db->query($sql_parent_id_category);
				
				if ($query_parent_id_category->num_rows) {
					$category_parent_seo = $query_parent_id_category->row['category_id'];
					$this->request->get['category_id'] = $category_parent_seo;
				}
			}
		}
		if ($key == "options") {
			$key_schet = 0;
			foreach ($value as $key_3 => $value_3) {
				if (isset($value[$key_schet]) and isset($value[$key_schet + 1])) {
					$name_option = $this->testSeparator("_", $value[$key_schet]);
					$name_option = $this->resultSeparator("?", "/", $name_option);
					
					if ($seo_keywords) {
						foreach ($seo_keywords as $key_test_seo => $value_test_seo) {
							if (trim($value[$key_schet]) == trim($value_test_seo)) {
								$name_option = $key_test_seo;
							}
						}
					}
					
					$key_schet = $key_schet + 1;
					$name_option_value = $this->testSeparator("_", $value[$key_schet]);
					$name_option_value = $this->resultSeparator("?", "/", $name_option_value);
					
					if ($seo_keywords) {
						foreach ($seo_keywords as $key_test_seo => $value_test_seo) {
							if (trim($value[$key_schet]) == trim($value_test_seo)) {
								$name_option_value = $key_test_seo;
							}
						}
					}
					
					$sql_options = $this->db->query("SELECT DISTINCT ovd.option_value_id FROM " . DB_PREFIX . "option_value_description ovd INNER JOIN " . DB_PREFIX . "option_description AS od ON (ovd.option_id = od.option_id) WHERE ovd.name = '" . $this->db->escape($name_option_value) . "' AND od.name = '" . $this->db->escape($name_option) . "'");
					if ($sql_options->num_rows) {
						foreach ($sql_options->rows as $key_options => $value_options) {
							$this->request->get['option_filter'][] = $value_options['option_value_id'];
						}
					}
				
					$key_schet = $key_schet + 1;
				}
			}
		}
		if ($key == "filter_filter") {
			$key_schet = 0;
			foreach ($value as $key_3 => $value_3) {
				if (isset($value[$key_schet]) and isset($value[$key_schet + 1])) {
					$name_filter = $this->testSeparator("_", $value[$key_schet]);
					$name_filter = $this->resultSeparator("?", "/", $name_filter);
					
					if ($seo_keywords) {
						foreach ($seo_keywords as $key_test_seo => $value_test_seo) {
							if (trim($value[$key_schet]) == trim($value_test_seo)) {
								$name_filter = $key_test_seo;
							}
						}
					}
					
					$key_schet = $key_schet + 1;
					$name_filter_value = $this->testSeparator("_", $value[$key_schet]);
					$name_filter_value = $this->resultSeparator("?", "/", $name_filter_value);
					
					if ($seo_keywords) {
						foreach ($seo_keywords as $key_test_seo => $value_test_seo) {
							if (trim($value[$key_schet]) == trim($value_test_seo)) {
								$name_filter_value = $key_test_seo;
							}
						}
					}

					$sql_filter = $this->db->query("SELECT DISTINCT fd.filter_id FROM " . DB_PREFIX . "filter_description fd INNER JOIN " . DB_PREFIX . "filter_group_description AS fgd ON (fgd.filter_group_id = fd.filter_group_id) WHERE fd.name = '" . $this->db->escape($name_filter_value) . "' AND fgd.name = '" . $this->db->escape($name_filter) . "' AND fd.language_id = '" . (int)$this->config->get('config_language_id') . "'  AND fd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

					if ($sql_filter->num_rows) {
						foreach ($sql_filter->rows as $key_filter => $value_filter) {
							$this->request->get['filter_filter'][] = $value_filter['filter_id'];
						}
					}
				
					$key_schet = $key_schet + 1;
				}
			}
		}
		if ($key == "attributes") {
			$key_schet = 0;
			foreach ($value as $key_3 => $value_3) {
				if (isset($value[$key_schet]) and isset($value[$key_schet + 1])) {
					$name_attributes = $this->testSeparator("_", $value[$key_schet]);
					$name_attributes = $this->resultSeparator("?", "/", $name_attributes);
					
					if ($seo_keywords) {
						foreach ($seo_keywords as $key_test_seo => $value_test_seo) {
							if (trim($value[$key_schet]) == trim($value_test_seo)) {
								$name_attributes = $key_test_seo;
							}
						}
					}
					
					$key_schet = $key_schet + 1;
					$name_attributes_value = $this->testSeparator("_", $value[$key_schet]);
					$name_attributes_value = $this->resultSeparator("?", "/", $name_attributes_value);
					
					
					if ($seo_keywords) {
						foreach ($seo_keywords as $key_test_seo => $value_test_seo) {
							if (trim($value[$key_schet]) == trim($value_test_seo)) {
								$name_attributes_value = $key_test_seo;
							}
						}
					}

					$sql_attributes = $this->db->query("SELECT DISTINCT pa.attribute_id FROM " . DB_PREFIX . "product_attribute pa INNER JOIN " . DB_PREFIX . "attribute_description AS ad ON (pa.attribute_id = ad.attribute_id) WHERE pa.text = '" . $this->db->escape($name_attributes_value) . "' AND ad.name = '" . $this->db->escape($name_attributes) . "'");

					if ($sql_attributes->num_rows) {
						foreach ($sql_attributes->rows as $key_attributes => $value_attributes) {
							$this->request->get['attributes_filter'][$value_attributes['attribute_id']][] = $name_attributes_value;
						}
						
					}
					
					$key_schet = $key_schet + 1;
				}
			}
		}
		if ($key == "page") {
			foreach ($value as $key_2 => $value_2) {
				$this->request->get['page'] = $value_2;
			}
		}
		if ($key == "limit") {
			foreach ($value as $key_2 => $value_2) {
				$this->request->get['limit'] = $value_2;
			}
		}
		if ($key == "sort") {
			foreach ($value as $key_2 => $value_2) {
				$this->request->get['sort'] = $value_2;
			}
		}
		if ($key == "order") {
			foreach ($value as $key_2 => $value_2) {
				$this->request->get['order'] = $value_2;
			}
		}
	}
	return $this->request->get;
}
	public function test_Count_options_group_select($select_options) {
			
			$test_options_id = array();
				
			foreach ($select_options as $key => $value) {
				$test_options_id[] = (int)$value;
			}
			if ($test_options_id) {
				$test_options_id_enum = $this->getGenerationEnumeration($test_options_id);
			}
			$options_id_query = $this->db->query("SELECT option_id FROM " . DB_PREFIX . "option_value WHERE option_value_id IN (" . $this->db->escape($test_options_id_enum) . ")");

			$options_id_array = array();
			if ($options_id_query->num_rows) {
				foreach ($options_id_query->rows as $key => $value) {
					$options_id_array[] = (int)$value['option_id'];
				}
			}
			$options_id_array = array_unique($options_id_array);
			
			return count($options_id_array);
			
	}
	public function stockStock() {
		$module_info = $this->getGofilter('gofilter');
		
		if (isset($module_info['gofilter_data'])) {
			foreach (json_decode($module_info['gofilter_data'], true) as $module => $value) {
				if (isset($value['stock_quantity'])) {$stock_quantity = $value['stock_quantity'];}
			}
		}
				
		if (isset($stock_quantity)) {
			return true;
		} else {
			return false;
		}
	}

	
	public function getidProducts($common_value_id, $stockStock, $job_view) {
		
		unset($common_value_id['old_category_id']);	unset($common_value_id['path']); unset($common_value_id['route_layout']); unset($common_value_id['select']); unset($common_value_id['class']); unset($common_value_id['op']); unset($common_value_id['prices_max_value']); unset($common_value_id['prices_min_value']); unset($common_value_id['route']); unset($common_value_id['gofilter']); unset($common_value_id['nofilter']); unset($common_value_id['nofilter']);
		
		$sql = $this->getGeneraionSql($common_value_id, $stockStock, $job_view);
		
		$query_products = $this->db->query($sql);
		
		if ($query_products->num_rows) {
			return $query_products;
		} else {
			return $query_products = false;
		}

}
	
public function getDelimitier() {

	$route = 'common/home';
	
	if (isset($this->request->get['route'])) {
		$route = $this->request->get['route'];
	}
	
	if (isset($this->request->post['route_layout'])) {
		$route = $this->request->post['route_layout'];
	}
	
	$layoute_id = $this->getRoute($route);
		
	$module_info = $this->getGofilter('gofilter');
	
	$delimitier = " ";
	
	if (isset($module_info['gofilter_data'])) {
		foreach (json_decode($module_info['gofilter_data'], true) as $module => $value) {
			if (isset($value['layout'])) {
				if (in_array($layoute_id, $value['layout'])) {
					$delimitier = $value['keywords_type'];
				}
			}
		}
	}
	
	return $delimitier;
}
	
public function getGenerationEnumeration($perem_id_data = array()) {
		$perem_id = "";
		$k = 0;
		if ($perem_id_data) {
			foreach ($perem_id_data as $key => $value) {
				$k = $k + 1;
				if ($k == 1) {
					$perem_id = (int)$value;
				} else {
					$perem_id .= ", " . (int)$value;
				}
			}
		}
		
		return $perem_id;
		
}

	public function getGenerationSeparator() {
		$seo_separator = "=";
		
		$module_info = $this->getGofilter('gofilter');
		
		if (isset($module_info['gofilter_data'])) {
			foreach (json_decode($module_info['gofilter_data'], true) as $module => $value) {
				if (isset($value['seo_separator'])) {
					$seo_separator = $value['seo_separator'];
				}
			}
		}
		return $seo_separator;
	}

	public function getGenerationString($perem_id_data = array()) {

		$seo_separator = $this->getGenerationSeparator();

		$perem_id = "";
		$k = 0;
		if ($perem_id_data) {
			foreach ($perem_id_data as $key => $value) {
				$k = $k + 1;
				if ($k == 1) {
					$perem_id = $value;
				} else {
					$perem_id .= $seo_separator . $value;
				}
			}
		}
		
		return $perem_id;
	}

public function calculate($value, $tax_class_id, $calculate = true) {
		if ($tax_class_id && $calculate) {
			$amount = 0;

			$tax_rates = $this->getRates($value, $tax_class_id);

			foreach ($tax_rates as $tax_rate) {
				if ($calculate != 'P' && $calculate != 'F') {
					$amount += $tax_rate['amount'];
				} elseif ($tax_rate['type'] == $calculate) {
					$amount += $tax_rate['amount'];
				}
			}
			
			$common = round(($value - $amount), 0);
			
			if ($common < 0) {$common = 0;}
			
			return $common = $common + 1;
		} else {
			return $value;
		}
}
	
public function getRates($value, $tax_class_id) {

		$tax_rate_data = array();
	
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "tax_rate tr INNER JOIN " . DB_PREFIX . "tax_rule trl ON (tr.tax_rate_id = trl.tax_rate_id) WHERE trl.tax_class_id IN (" . $this->db->escape($tax_class_id) . ") ORDER BY tr.type ASC");
		
	
		if ($query->num_rows) {

			$amount = 0;
			foreach ($query->rows as $tax_rate) {
				

				if ($tax_rate['type'] == 'F') {
					$amount += $tax_rate['rate'];
				} elseif ($tax_rate['type'] == 'P') {
					$amount += ($value - $amount) / (100 + $tax_rate['rate'])*$tax_rate['rate'] - $amount;
				}

				$tax_rate_data[$tax_rate['tax_rate_id']] = array(
					'type'        => $tax_rate['type'],
					'amount'      => $amount
				);
			}
			
		}
		
		return $tax_rate_data;
	
}
	
public function geProductsPrice($common_value_id, $option_products_id_data, $stockStock) {
	
	if ($option_products_id_data) {
		if (isset($common_value_id['prices_min_value']) and isset($common_value_id['prices_max_value'])) {
			$common_value_id['prices_max_value'] = ceil($this->currency->convert($common_value_id['prices_max_value'], $this->session->data['currency'], $this->currency->convert(1, 1, $this->config->get('config_currency')) > 1 ? 1 : $this->config->get('config_currency')));
			$common_value_id['prices_min_value'] = ceil($this->currency->convert($common_value_id['prices_min_value'], $this->session->data['currency'], $this->currency->convert(1, 1, $this->config->get('config_currency')) > 1 ? 1 : $this->config->get('config_currency')));
			
			$sql = "";
			$array_products = array();
			foreach ($option_products_id_data as $product_id) {
				$sql = "SELECT DISTINCT p.product_id FROM " . DB_PREFIX . "product p";
				$query_tax_class_id = $this->db->query("SELECT DISTINCT t.tax_class_id FROM " . DB_PREFIX . "product t WHERE t.product_id = '" . (int)$product_id . "'");
				if ($this->config->get('config_tax')) {
					if (isset($common_value_id['prices_min_value'])) {$prices_min_value = $this->calculate($common_value_id['prices_min_value'], $query_tax_class_id->row['tax_class_id'], $this->config->get('config_tax')) - 1;}
					if (isset($common_value_id['prices_max_value'])) {$prices_max_value = $this->calculate($common_value_id['prices_max_value'], $query_tax_class_id->row['tax_class_id'], $this->config->get('config_tax')) + 1;}
				} else {
					if (isset($common_value_id['prices_min_value'])) {$prices_min_value = $this->calculate($common_value_id['prices_min_value'], $query_tax_class_id->row['tax_class_id'], $this->config->get('config_tax')) - 1;}
					if (isset($common_value_id['prices_max_value'])) {$prices_max_value = $this->calculate($common_value_id['prices_max_value'], $query_tax_class_id->row['tax_class_id'], $this->config->get('config_tax')) + 1;}
				}
				
				if ($prices_min_value < 0) {$prices_min_value = 0;}
				
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_special ps ON (p.product_id = ps.product_id) LEFT JOIN " . DB_PREFIX . "product_discount pd2 ON (pd2.product_id = p.product_id)";
				
				$sql .=  " WHERE";

				$sql .= " (CASE WHEN ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()) AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "') THEN (((select ROUND(MAX(ps5.price), 0) from " . DB_PREFIX . "product_special ps5 WHERE ps5.product_id = '" . (int)$product_id . "' AND ps5.priority = (select MIN(ps7.priority) from " . DB_PREFIX . "product_special ps7 WHERE ps7.product_id = '" . (int)$product_id . "')) >= " . (int)$prices_min_value . ") AND ((select ROUND(MIN(ps6.price), 0) from " . DB_PREFIX . "product_special ps6 WHERE ps6.product_id = '" . (int)$product_id . "' AND ps6.priority = (select MIN(ps7.priority) from " . DB_PREFIX . "product_special ps7 WHERE ps7.product_id = '" . (int)$product_id . "')) <= " . (int)$prices_max_value . ")) ELSE ROUND(p.price, 0) >= " . (int)$prices_min_value . " AND ROUND(p.price, 0) <= " . (int)$prices_max_value . " END)";

				$sql .=  " AND p.product_id = '" . $product_id . "'";
				
				if ($stockStock) {
					$sql .= " AND p.quantity > 0";
				}
				
				$query_products_id = $this->db->query($sql);
				if ($query_products_id->num_rows) {
					$array_products[] = $query_products_id->row['product_id'];
				}
			}
			$array_products = array_unique($array_products);
		} else {
			$array_products = $option_products_id_data;
		}
		return $array_products;
	}
}
	
public function getOptionsProducts($common_value_id) {
	
	if(isset($common_value_id['categ_id'])) {$common_value_id['category_id'] = $common_value_id['categ_id'];}
	
	if ($this->stockStock()) {
		$stockStock = true;
	} else {
		$stockStock = false;
	}
	
	$module_info = $this->getGofilter('gofilter');
	if (isset($module_info['gofilter_data'])) {
		$module_info_decode = json_decode($module_info['gofilter_data'], true);
	} else {
		$module_info_decode = false;
	}
	if (!isset($module_info_decode['settings']['job_view'])) {
		$job_view = 1;
	} else {
		$job_view = 0;
	}
	
	$query_products = $this->getidProducts($common_value_id, $stockStock, $job_view);

	$option_products_id_data = array();
	if ($query_products) {
		foreach ($query_products->rows as $option_value_product_id) {
			$option_products_id_data[] = (int)$option_value_product_id['product_id'];
		}
	}
	
	$option_products_id = array_map("unserialize", array_unique(array_map("serialize", $option_products_id_data)));
	
	$option_products_id = $this->geProductsPrice($common_value_id, $option_products_id, $stockStock);

	return $option_products_id;
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



	public function getHierarchyCategoryname($category_id) {

		$query = $this->db->query("SELECT cd.name,cd.category_id,c.parent_id FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE cd.category_id = '" . (int)$category_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		
		$category_name = array();
		if ($query->num_rows) {
				if ($query->row['parent_id'] != 0) {
					$query_1 = $this->db->query("SELECT cd.name,cd.category_id,c.parent_id FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE c.category_id = '" . (int)$query->row['parent_id'] . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
					if ($query_1->num_rows) {
						if ($query_1->row['parent_id'] != 0) {
							$query_2 = $this->db->query("SELECT cd.name,cd.category_id,c.parent_id FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE c.category_id = '" . (int)$query_1->row['parent_id'] . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
							if ($query_2->num_rows) {
								if ($query_2->row['parent_id'] != 0) {
									$query_3 = $this->db->query("SELECT cd.name,cd.category_id,c.parent_id FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE c.category_id = '" . (int)$query_2->row['parent_id'] . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
									if ($query_3->num_rows) {
										if ($query_3->row['parent_id'] != 0) {
											$query_4 = $this->db->query("SELECT cd.name,cd.category_id,c.parent_id FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE c.category_id = '" . (int)$query_3->row['parent_id'] . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
											if ($query_4->num_rows) {
												if ($query_4->row['parent_id'] != 0) {
													$query_5 = $this->db->query("SELECT cd.name,cd.category_id,c.parent_id FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE c.category_id = '" . (int)$query_4->row['parent_id'] . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
													if ($query_5->num_rows) {
														if ($query_5->row['parent_id'] != 0) {
															$query_6 = $this->db->query("SELECT cd.name,cd.category_id,c.parent_id FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE c.category_id = '" . (int)$query_5->row['parent_id'] . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
															if ($query_6->num_rows) {
																$category_name[] = $query_6->row['name'];
															}
														}
														$category_name[] = $query_5->row['name'];
													}
												}
												$category_name[] = $query_4->row['name'];
											}
										}
										$category_name[] = $query_3->row['name'];
									}
								}
								$category_name[] = $query_2->row['name'];
							}
						}
						$category_name[] = $query_1->row['name'];
					}
				}
				$category_name[] = $query->row['name'];
		}
		
		$category_name = $this->getGenerationString($category_name);
		
		$category_name = str_replace(' ', '_', str_replace('  ', ' ', trim($category_name)));
		
		$massiv = array();
		if ($query->num_rows) {
			foreach ($query->rows as $key => $value) {
				$massiv = array(
					'category_id' 		=> $query->row['category_id'],
					'name' 				=> $query->row['name'],
					'name_hierarchy' 	=> $category_name,
				);
			}
		}
		
		
		return $massiv;
	}

	public function getOptionsProductsCategory($category_id) {
		
		$categories_value_query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category_path WHERE path_id = '" . (int)$category_id . "'");
		
		$categories_id_data = array();
		if ($categories_value_query->num_rows) {
			foreach ($categories_value_query->rows as $categories_value) {
				$categories_id_data[] = (int)$categories_value['category_id'];
			}
		}
		
		$categories_ids = $this->getGenerationEnumeration($categories_id_data);
		
		if($categories_ids == "") {
			$categories_ids_value_query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category WHERE status = 1");
			$categories_ids_data = array();
			if ($categories_ids_value_query->num_rows) {
				foreach ($categories_ids_value_query->rows as $categories_value) {
					$categories_ids_data[] = (int)$categories_value['category_id'];
				}
			}
			$categories_ids = $this->getGenerationEnumeration($categories_ids_data);
		}
		
		$products_value_query = $this->db->query("SELECT ptc.product_id FROM " . DB_PREFIX . "product_to_category ptc INNER JOIN " . DB_PREFIX . "product p ON (ptc.product_id = p.product_id) WHERE category_id IN (" . $this->db->escape($categories_ids) . ") AND p.status = '1'");
		
		if ($products_value_query->num_rows) {
			
			$products_id_data = array();
			
			foreach ($products_value_query->rows as $product_value) {
				
				$products_id_data[] = (int)$product_value['product_id'];
				
			}
			
			$products_id_data = array_map("unserialize", array_unique(array_map("serialize", $products_id_data)));
			
			$products_id = array();
			$k = 0;
			foreach ($products_id_data as $products => $value) {
				$k = $k + 1;
				if ($k == 1) {
					$products_id = (int)$value;
				} else {
					$products_id .= ", " . (int)$value;
				}
			}
		} else {
			$products_id = false;
		}
		
		return $products_id;
	}
	
	public function getProductsCommon() {

		$products_value_query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE status = '1'");

		$products_array = array();
		if ($products_value_query->num_rows) {
			foreach ($products_value_query->rows as $product_value) {
				$products_array[] = (int)$product_value['product_id'];
			}
		}

		return $products_array;
	}
	
public function getProductsstockCategory($category_id) {
		$products_value_query = $this->db->query("SELECT ptc.product_id FROM " . DB_PREFIX . "product_to_category ptc INNER JOIN " . DB_PREFIX . "product p ON (ptc.product_id = p.product_id) WHERE category_id = '" . (int)$category_id . "' AND p.status = '1'");
		$products_array = array();
		if ($products_value_query->num_rows) {
			foreach ($products_value_query->rows as $product_value) {
				$products_array[] = (int)$product_value['product_id'];
			}
		}

		return $products_array;
}
	
public function getProductsPrices($products_array) {
	$prices_values_array = array();
	$prices_values = false;
	
	if ($products_array) {
		$products_array_enum = $this->getGenerationEnumeration($products_array);
		$query_prices = $this->db->query("SELECT DISTINCT p.price,p.tax_class_id,(SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount FROM " . DB_PREFIX . "product p INNER JOIN " . DB_PREFIX . "product_special pd ON (p.product_id = pd.product_id) WHERE p.product_id IN (" . $products_array_enum . ")");
		if ($query_prices->num_rows) {
			foreach ($query_prices->rows as $query_price) {
				if ((float)$query_price['special']) {
					$special = $this->tax->calculate($query_price['special'], $query_price['tax_class_id'], $this->config->get('config_tax'));
				} else {
					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->tax->calculate(($query_price['discount'] ? $query_price['discount'] : $query_price['price']), $query_price['tax_class_id'], $this->config->get('config_tax'));
					} else {
						$price = false;
					}
					$special = false;
				}
				$prices_values[] = 	(int)($special ? $special : $price);
			}
		}
	}
	if ($prices_values) {
		$prices_values_array[] = array(
			'max'      => max($prices_values),
			'min'      => min($prices_values)
		);
	}
	
	return $prices_values_array;
}
	
public function getCache() {
	$module_info = $this->getGofilter('gofilter');
	if (isset($module_info['gofilter_data'])) {
		$module_info_decode = json_decode($module_info['gofilter_data'], true);
	} else {
		$module_info_decode = false;
	}
	if (isset($module_info_decode['settings']['status_cache'])) {
		$oncache = true;
	} else {
		$oncache = false;
	}
	
	return $oncache;
}

public function get_array_column($input, $columnKey, $indexKey = null) {
        $array = array();
        foreach ($input as $value) {
            if (!isset($value[$columnKey])) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }
    
            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            } else {
                if (!isset($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if (!is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;

}

public function sqlEmptyParameters($data) {
	$itog = array();
	$itog[0] = "";
	$itog[1] = "";
	
	/* if ($data['attribute_empty']) {
		$itog[0] .= " INNER JOIN " . DB_PREFIX . "product_attribute pa ON (p.product_id = pa.product_id)";
	}
	if ($data['options_empty']) {
		$itog[0] .= " INNER JOIN " . DB_PREFIX . "product_option po ON (p.product_id = po.product_id)";
	}
	
	if ($data['attribute_empty']) {
		$attribute_empty = $this->getGenerationEnumeration($data['attribute_empty']);
		$itog[1] .= " AND pa.attribute_id";
		if (!$data['attrib_empty']) {
			$itog[1] .= " NOT IN";
		} else {
			$itog[1] .= " IN";
		}
		$itog[1] .= " (" . $attribute_empty . ")";
	}
	
	if ($data['options_empty']) {
		$options_empty = $this->getGenerationEnumeration($data['options_empty']);
		$itog[1] .= " AND po.option_id";
		if (!$data['option_empty']) {
			$itog[1] .= " NOT IN";
		} else {
			$itog[1] .= " IN";
		}
		$itog[1] .= " (" . $options_empty . ")";
	} */
	return $itog;
}

public function getLikeSearch($data) {
	$sql_store = '';
	if ($data != "") {
		if(strlen($data) >= 1) {
		$sql_store .= ' AND';
		if ($this->getDelimitier()) {$delimitier = $this->getDelimitier();} else {$delimitier = " ";}

		$parts = explode($delimitier, $data);
			$sql_store .= ' (';
			
			$u = 0;
			foreach($parts as $part) {
				$u = $u + 1;
				if ($u == 1) {
					$sql_store .= '(LOWER(p.model) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pdq.name) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pdq.meta_title) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pdq.tag) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pdq.meta_description) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pdq.meta_keyword) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pmq.name) LIKE "%' . $this->db->escape($part) . '%")';
				} else {
					$sql_store .= ' OR ';
					$sql_store .= '(LOWER(p.model) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pdq.name) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pdq.meta_title) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pdq.tag) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pdq.meta_description) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pdq.meta_keyword) LIKE "%' . $this->db->escape($part) . '%") OR (LOWER(pmq.name) LIKE "%' . $this->db->escape($part) . '%")';
				}							
			}
			
			$sql_store .= ')';
			
			$sql_store .= ' AND pdq.language_id = ' . (int)$this->config->get('config_language_id');

		}
	}
	return $sql_store;
}
	
public function getCommonProducts($category, $quantity_view, $getCache, $stockStock, $status_massivs, $data) {
		
		$itog = $this->sqlEmptyParameters($data);
		
		$common_value_data = array();
		
		$options = array();
		
		if ($stockStock) {
			$sql_quantity = " AND p.quantity > 0";
		} else {
			$sql_quantity = "";
		}

		if ($category) {
			$sql_store = "CREATE TEMPORARY TABLE IF NOT EXISTS store_temp_table (INDEX indextemp (product_id)) AS ";
			
			$sql_store .= "(";
			
				$sql_store .= "SELECT p.product_id,p.manufacturer_id,p.price,p.tax_class_id,p.quantity,p.stock_status_id FROM " . DB_PREFIX . "product_to_category ptc LEFT JOIN " . DB_PREFIX . "category_path cp ON (ptc.category_id = cp.category_id) INNER JOIN " . DB_PREFIX . "category_to_store AS c2s ON (ptc.category_id = c2s.category_id) INNER JOIN " . DB_PREFIX . "product_to_store AS p2sq ON (ptc.product_id = p2sq.product_id) INNER JOIN " . DB_PREFIX . "product p ON (p.product_id = p2sq.product_id)";
				
				if ($itog) {
					$sql_store .= $itog[0];
				}
				
				if (isset($data['search'])) {
					if ($data['search'] != "") {
						$sql_store .= " INNER JOIN " . DB_PREFIX . "product_description AS pdq ON p.product_id = pdq.product_id";
						$sql_store .= " LEFT JOIN " . DB_PREFIX . "manufacturer AS pmq ON p.manufacturer_id = pmq.manufacturer_id";
					}
				}
				
				$sql_store .= " WHERE p2sq.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p.status = '1' AND IF(cp.path_id IS NOT NULL, cp.path_id, ptc.category_id) = '" . (int)$category . "'" . $sql_quantity;
				
				if ($itog) {
					$sql_store .= $itog[1];
				}
				
				if (isset($data['search'])) {
					$sql_store .= $this->getLikeSearch($data['search']);
				}
				
			$sql_store .= ")";

			$this->db->query($sql_store);
           
			$category_id_cache = ".gofilter.category" . $category . "." . (int)$this->config->get('config_customer_group_id') . "." . (int)$this->config->get('config_store_id') . "." . (int)$this->config->get('config_language_id');
		} else {
			
			$sql_store = "CREATE TEMPORARY TABLE IF NOT EXISTS store_temp_table (INDEX indextemp (product_id)) AS ";
			
			$sql_store .= "(";
			
				$sql_store .= "SELECT p.product_id,p.manufacturer_id,p.price,p.tax_class_id,p.quantity,p.stock_status_id FROM " . DB_PREFIX . "product p INNER JOIN " . DB_PREFIX . "product_to_store AS p2sq ON (p.product_id = p2sq.product_id)";
				
				if ($itog) {
					$sql_store .= $itog[0];
				}
				
				if (isset($this->request->get['route'])) {
					if ($this->request->get['route'] == 'product/special') {
						$sql_store .= " INNER JOIN " . DB_PREFIX . "product_special AS ps ON p.product_id = ps.product_id";
					}
				}
				if (isset($this->request->post['route_layout'])) {
					if ($this->request->post['route_layout'] == 'product/special') {
						$sql_store .= " INNER JOIN " . DB_PREFIX . "product_special AS ps ON p.product_id = ps.product_id";
					}
				}
				
				if (isset($data['search'])) {
					if ($data['search'] != "") {
						$sql_store .= " INNER JOIN " . DB_PREFIX . "product_description AS pdq ON p.product_id = pdq.product_id";
						$sql_store .= " LEFT JOIN " . DB_PREFIX . "manufacturer AS pmq ON p.manufacturer_id = pmq.manufacturer_id";
					}
				}

				$sql_store .= " WHERE p2sq.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p.status = '1'" . $sql_quantity;
				
				if ($itog) {
					$sql_store .= $itog[1];
				}
				
				if (isset($data['manufacturer_id'])) {
					$sql_store .= " AND p.manufacturer_id = '" . (int)$data['manufacturer_id'] . "'";
				}
				
				if (isset($this->request->get['route'])) {
					if ($this->request->get['route'] == 'product/special') {
						$sql_store .= " AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()) AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "')";
					}
				}
				if (isset($this->request->post['route_layout'])) {
					if ($this->request->post['route_layout'] == 'product/special') {
						$sql_store .= " AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()) AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "')";
					}
				}
				
				if (isset($data['search'])) {
					$sql_store .= $this->getLikeSearch($data['search']);
				}

			$sql_store .= ")";
			
			$this->db->query($sql_store);
			
			$category_id_cache = ".gofilter.common." . (int)$this->config->get('config_customer_group_id') . "." . (int)$this->config->get('config_store_id') . "." . (int)$this->config->get('config_language_id');
		}
		
		$text_sql_category_left_join = " store_temp_table";
		
		if (isset($status_massivs['status_options'])) {
			$sql_options = "SELECT pov.option_id,od.name as name_option,o.type,pov.option_value_id,ovd.name as name_option_value,ov.image" . ($quantity_view ? ',COUNT(DISTINCT pov.product_id) as total' : ',-1 as total') . " FROM " . $text_sql_category_left_join . " p INNER JOIN " . DB_PREFIX . "product_option_value pov ON (p.product_id = pov.product_id) INNER JOIN `" . DB_PREFIX . "option` o ON (pov.option_id = o.option_id) INNER JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) INNER JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) INNER JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.option_id IS NOT NULL AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY pov.option_value_id ORDER BY o.sort_order,ov.sort_order ASC";
		
			if (!$getCache or ($getCache and !$this->cache->get('options' . $category_id_cache))) {
				$query_options = $this->db->query($sql_options);
				if ($query_options->num_rows) {$query_options_rows = $query_options->rows;} else {$query_options_rows = false;}
				
				$temp_option_id_data = array();
				if ($query_options_rows) {
					$option_value_id_query_rows = array_map("unserialize", array_unique(array_map("serialize", $query_options_rows)));
					foreach ($option_value_id_query_rows as $option_val_id) {
						$total_quantity_option_value_sql = $this->db->query("SELECT COUNT(DISTINCT p.product_id) as total,pov.option_value_id FROM " . $text_sql_category_left_join . " as p INNER JOIN " . DB_PREFIX . "product_option_value pov ON (p.product_id = pov.product_id) WHERE pov.option_value_id = '" . (int)$option_val_id['option_value_id'] . "' AND pov.quantity <> 0");
						
						if ($total_quantity_option_value_sql->num_rows) {$total_quantity_option_value = $total_quantity_option_value_sql->row['total'];} else {$total_quantity_option_value = 0;}
						
						$temp_option_id_data[(int)$option_val_id['option_id']][] = array(
							'option_name' 			=> $option_val_id['name_option'],
							'option_type' 			=> $option_val_id['type'],
							'option_value_id' 		=> (int)$option_val_id['option_value_id'],
							'option_value_image' 	=> $option_val_id['image'],
							'option_value_name' 	=> $option_val_id['name_option_value'],
							'option_value_total' 	=> $total_quantity_option_value,
						);
					}
					foreach ($temp_option_id_data as $temp_key => $temp_value) {
						$option_values = array();
						$option_name = "";
						$option_type = "";
						foreach ($temp_value as $key => $value) {
							if ($value['option_value_total']) {
								$option_values[] = array(
									'option_value_id'          => $value['option_value_id'],
									'option_value_name' 	   => $value['option_value_name'],
									'option_value_total' 	   => $value['option_value_total'],
									'option_value_image'       => $value['option_value_image'],
								);
								$option_name = $value['option_name'];
								$option_type = $value['option_type'];
							}
						}
						
						$common_value_data['options'][] = array(
							'option_id'          	   => $temp_key,
							'name'          	       => $option_name,
							'type'          		   => $option_type,
							'option_value'      	   => $option_values
						);
						
					}

				}
				if ($getCache and isset($common_value_data['options'])) {$this->cache->set('options' . $category_id_cache, $common_value_data['options']);}
			} else {
				$common_value_data['options'] = $this->cache->get('options' . $category_id_cache);
			}
		}
		
		if (isset($status_massivs['status_attributes'])) {
			$test_index = $this->db->query("SHOW INDEXES FROM " . DB_PREFIX . "product_attribute WHERE Key_name = 'indexpa'");
			if (!$test_index->num_rows) {$this->db->query("create index indexpa on " . DB_PREFIX . "product_attribute(attribute_id,product_id);");}

			$sql_attributes = "SELECT pa.attribute_id,pa.text as rtext" . ($quantity_view ? ',COUNT(DISTINCT p.product_id) AS total' : ',-1 as total') . " FROM " . $text_sql_category_left_join . " p INNER JOIN " . DB_PREFIX . "product_attribute pa ON (pa.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) WHERE pa.text <> '' AND pa.text <> ' ' AND pa.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY pa.attribute_id,rtext ORDER BY a.sort_order ASC";
			
			$sql_attributes_name = "SELECT DISTINCT attribute_id,name FROM " . DB_PREFIX . "attribute_description WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'";

			if (!$getCache or ($getCache and !$this->cache->get('attributes' . $category_id_cache))) {
				$query_attributes = $this->db->query($sql_attributes);
				if ($query_attributes->num_rows) {$query_attributes_rows = $query_attributes->rows;} else {$query_attributes_rows = false;}
				
				$query_attributes_name = $this->db->query($sql_attributes_name);
				
				$result_attributes_name = array();
				if ($query_attributes_name->num_rows) {
					foreach ($query_attributes_name->rows as $value) {
						$result_attributes_name[$value['attribute_id']] = $value['name'];
					}
					
				}
				
				$temp_attribute_id_data = array();
				if ($query_attributes_rows) {
					foreach ($query_attributes_rows as $attribute_value_id) {
						if ($attribute_value_id['attribute_id']) {
							$temp_attribute_id_data[$attribute_value_id['attribute_id']][] = array(
								'text'				=> $attribute_value_id['rtext'],
								'total'				=> $attribute_value_id['total'],
							);
						}
					}
				}
				
				if ($temp_attribute_id_data) {
					$temp_massiv_attributes_1 = array();
					foreach ($temp_attribute_id_data as $temp_key_1 => $temp_value_1) {
						
						if (isset($result_attributes_name[$temp_key_1])) {
							$name = $result_attributes_name[$temp_key_1];
							$attribute_values = array();
							foreach ($temp_value_1 as $temp_key_2 => $temp_value_2) {
								if ($temp_value_2['text'] and $temp_value_2['text'] != "" and $temp_value_2['text'] != " " and $temp_value_2['text'] != "  ") {
									$attribute_values[] = array(
										'attribute_id'          	=> $temp_key_1,
										'text' 	   					=> $temp_value_2['text'],
										'attribute_text_total' 	  	=> $temp_value_2['total'],
									);
								}
							}
							$common_value_data['attributes'][] = array(
								'attribute_id'      => $temp_key_1,
								'name'          	=> $name,
								'attribute'      	=> $attribute_values
							);
						}

					}
				}
				if ($getCache and isset($common_value_data['attributes'])) {$this->cache->set('attributes' . $category_id_cache, $common_value_data['attributes']);}
			} else {
				$common_value_data['attributes'] = $this->cache->get('attributes' . $category_id_cache);
			}
		}
		
		/* filter */
		if (isset($status_massivs['status_filter'])) {
			if (!$getCache or ($getCache and !$this->cache->get('filters' . $category_id_cache))) {
				$implode_filters  = array();

				$query_filters = $this->db->query("SELECT filter_id FROM " . DB_PREFIX . "category_filter WHERE category_id = '" . (int)$category . "'");

				foreach ($query_filters->rows as $result) {
					$implode_filters[] = (int)$result['filter_id'];
				}

				$filter_group_data = array();

				if ($implode_filters) {
					$filter_group_query = $this->db->query("SELECT DISTINCT f.filter_group_id, fgd.name, fg.sort_order FROM " . DB_PREFIX . "filter f LEFT JOIN " . DB_PREFIX . "filter_group fg ON (f.filter_group_id = fg.filter_group_id) LEFT JOIN " . DB_PREFIX . "filter_group_description fgd ON (fg.filter_group_id = fgd.filter_group_id) WHERE f.filter_id IN (" . implode(',', $implode_filters) . ") AND fgd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY f.filter_group_id ORDER BY fg.sort_order, LCASE(fgd.name)");

					foreach ($filter_group_query->rows as $filter_group) {
						$filter_data = array();

						$filter_query = $this->db->query("SELECT DISTINCT f.filter_id, fd.name FROM " . DB_PREFIX . "filter f LEFT JOIN " . DB_PREFIX . "filter_description fd ON (f.filter_id = fd.filter_id) WHERE f.filter_id IN (" . implode(',', $implode_filters) . ") AND f.filter_group_id = '" . (int)$filter_group['filter_group_id'] . "' AND fd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY f.sort_order, LCASE(fd.name)");

						foreach ($filter_query->rows as $filter) {
							$query_total = $this->db->query("SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product_to_category p2c LEFT JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) LEFT JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p2c.category_id = '" . (int)$category . "' AND pf.filter_id = '" . (int)$filter['filter_id'] . "'");
							if ($query_total->row['total']) {$filter_total = $query_total->row['total'];} else {$filter_total = 0;}
							$filter_data[] = array(
								'filter_id' 			=> $filter['filter_id'],
								'name'      			=> $filter['name'],
								'filter_value_total'	=> $filter_total
							);
						}

						if ($filter_data) {
							$filter_group_data[] = array(
								'filter_group_id' => $filter_group['filter_group_id'],
								'name'            => $filter_group['name'],
								'filter'          => $filter_data
							);
						}
					}
				}
				
				$common_value_data['filters'] = $filter_group_data;
			if ($getCache and isset($common_value_data['filters'])) {$this->cache->set('filters' . $category_id_cache, $common_value_data['filters']);}
			} else {
				$common_value_data['filters'] = $this->cache->get('filters' . $category_id_cache);
			}
		}
		
		/* filter */
		
		if (isset($status_massivs['status_manufacturers'])) {
			$sql_manufacturers = "SELECT p.manufacturer_id,m.name,m.image" . ($quantity_view ? ',COUNT(DISTINCT p.product_id) as total' : ',-1 as total') . " FROM " . DB_PREFIX . "manufacturer m INNER JOIN" . $text_sql_category_left_join . " p ON (p.manufacturer_id = m.manufacturer_id) GROUP BY p.manufacturer_id ORDER BY m.sort_order ASC";

			if (!$getCache or ($getCache and !$this->cache->get('manufacturers' . $category_id_cache))) {
				$query_manufacturers = $this->db->query($sql_manufacturers);
				if ($query_manufacturers->num_rows) {$query_manufacturers_rows = $query_manufacturers->rows;} else {$query_manufacturers_rows = false;}
				
				$this->load->model('tool/image');
				
				if ($query_manufacturers_rows) {
					foreach ($query_manufacturers_rows as $query_manufacturer) {
							if ($query_manufacturer['manufacturer_id']) {
								$common_value_data['manufacturers'][] = array(
								'manufacturer_id'    				=> $query_manufacturer['manufacturer_id'],
								'name'          	 				=> $query_manufacturer['name'],
								'image'                    	    	=> $query_manufacturer['image'] ? $this->model_tool_image->resize($query_manufacturer['image'], 20, 20) : '',
								'manufacturer_value_total' 	   		=> $query_manufacturer['total'],
							);
						}
					}
				}
				
				if ($getCache and isset($common_value_data['manufacturers'])) {$this->cache->set('manufacturers' . $category_id_cache, $common_value_data['manufacturers']);}
			} else {
				$common_value_data['manufacturers'] = $this->cache->get('manufacturers' . $category_id_cache);
			}
		}
			

			$post_category_id = 0;
			if (isset($this->request->post['category_id'])) {$post_category_id = $this->request->post['category_id'];}
			if (isset($this->request->get['category_id'])) {$post_category_id = $this->request->get['category_id'];}
			
			
			if ($post_category_id != 0) {
				if (isset($this->request->get['category_id'])) {
					$category_id_enum = $this->request->get['category_id'];
				}
				if (isset($this->request->post['category_id'])) {
					$category_id_enum = $this->request->post['category_id'];
				}
				$sql_categories = "SELECT DISTINCT c.category_id FROM " . DB_PREFIX . "category c INNER JOIN " . DB_PREFIX . "category_to_store cts ON (c.category_id = cts.category_id) INNER JOIN " . DB_PREFIX . "category_path cp ON (c.category_id = cp.category_id) WHERE cp.path_id = '" . (int)$category_id_enum . "' AND c.status = '1' AND cts.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY c.sort_order";
			} else {
				$sql_categories = "SELECT DISTINCT c.category_id FROM " . DB_PREFIX . "category c INNER JOIN " . DB_PREFIX . "category_to_store cts ON (c.category_id = cts.category_id) WHERE c.status = '1' AND c.parent_id = '0' AND cts.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY c.sort_order";
			}
			
			if (!$getCache or ($getCache and !$this->cache->get('categories' . $category_id_cache))) {
				$query_categories = $this->db->query($sql_categories);
				if ($query_categories->num_rows) {$query_categories_rows = $query_categories->rows;} else {$query_categories_rows = false;}
				$categories_count = array();
				$sort_category = array();
				if ($query_categories_rows) {
					foreach ($query_categories_rows as $key => $category_id) {
						$common_value_data['categories'][] = array(
							'category_id' 	=> $category_id['category_id'],
						);
					}

				}
				if ($getCache and isset($common_value_data['categories'])) {$this->cache->set('categories' . $category_id_cache, $common_value_data['categories']);}
			} else {
				$common_value_data['categories'] = $this->cache->get('categories' . $category_id_cache);
			}
		
		
		if (isset($status_massivs['status_price'])) {
			$sql_prices = "SELECT DISTINCT p.price,p.tax_class_id,(SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount FROM" . $text_sql_category_left_join . " p LEFT JOIN " . DB_PREFIX . "product_special pd ON (p.product_id = pd.product_id)";
			
			if (!$getCache or ($getCache and !$this->cache->get('prices' . $category_id_cache))) {
				$query_prices = $this->db->query($sql_prices);
				if ($query_prices->num_rows) {$query_prices_rows = $query_prices->rows;} else {$query_prices_rows = false;}
				
				$prices_values = array();
				if ($query_prices_rows) {
					foreach ($query_prices_rows as $query_price) {
						if ((float)$query_price['special']) {
							$special = $this->tax->calculate($query_price['special'], $query_price['tax_class_id'], $this->config->get('config_tax'));
						} else {
							if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
								$price = $this->tax->calculate(($query_price['discount'] ? $query_price['discount'] : $query_price['price']), $query_price['tax_class_id'], $this->config->get('config_tax'));
							} else {
								$price = false;
							}
							$special = false;
						}
						$prices_values[] = 	(int)($special ? $special : $price);
					}
				}
				
				if ($prices_values) {
					$common_value_data['prices'][] = array(
						'max'      => ceil($this->currency->format(max($prices_values), $this->session->data['currency'], '', false)),
						'min'      => floor($this->currency->format(min($prices_values), $this->session->data['currency'], '', false))
					);
					if ($getCache and isset($common_value_data['prices'])) {$this->cache->set('prices' . $category_id_cache, $common_value_data['prices']);}
				}
			} else {
				$common_value_data['prices'] = $this->cache->get('prices' . $category_id_cache);
			}
		}
		
		if (isset($status_massivs['status_stock'])) {
			$sql_stock_statuses_stock = "SELECT DISTINCT p.product_id,IF(p.quantity <= '0', ps.stock_status_id, 'stock') as status_id,IF(p.quantity <= '0', ps.name, 'stock') as stock_name FROM " . $text_sql_category_left_join . " p INNER JOIN " . DB_PREFIX . "stock_status ps ON (p.stock_status_id = ps.stock_status_id) WHERE ps.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			
			if (!$getCache or ($getCache and !$this->cache->get('stock' . $category_id_cache))) {
				$query_stock_statuses_stock = $this->db->query($sql_stock_statuses_stock);
				if ($query_stock_statuses_stock->num_rows) {$query_stock_statuses_stock_rows = $query_stock_statuses_stock->rows;} else {$query_stock_statuses_stock_rows = false;}
				
				$this->load->language('extension/module/gofilter');
				$temp_stock_status = array();
				if ($query_stock_statuses_stock_rows) {
					foreach ($query_stock_statuses_stock_rows as $query_stock_status_stock) {
						$name_text = $query_stock_status_stock['stock_name'];
						if ($query_stock_status_stock['stock_name'] == "stock") {
							$name_text = $this->language->get('text_stock');
						}
						$temp_stock_status[] = array(
							'status_id'			=> $query_stock_status_stock['status_id'],
							'stock_name'		=> $name_text,
						);
					}
				}
				if ($quantity_view) {
					if(!function_exists('array_column')) {
						$count = array_count_values($this->get_array_column($temp_stock_status, 'stock_name'));
					} else {
						$count = array_count_values(array_column($temp_stock_status, 'stock_name'));
					}
				}
				foreach ($temp_stock_status as $key => $value) {
					if ($quantity_view) {$itog_count = $count[$value['stock_name']];} else {$itog_count = 0;}
					$common_value_data['stock_status'][$value['stock_name']] = array(
						'status_id'  => $value['status_id'],
						'name'  	 => $value['stock_name'],
						'count'  	 => $itog_count,
					);
				}
				
				if ($getCache and isset($common_value_data['stock_status'])) {$this->cache->set('stock' . $category_id_cache, $common_value_data['stock_status']);}
			} else {
				$common_value_data['stock_status'] = $this->cache->get('stock' . $category_id_cache);
			}
		}
		
		if (isset($status_massivs['status_ratings'])) {
			$sql_ratins = "SELECT IF(r.product_id IS NOT NULL, ROUND(AVG(r.rating), 0), 0) as ratings" . ($quantity_view ? ', IF(r.product_id IS NOT NULL, COUNT(DISTINCT r.product_id), COUNT(DISTINCT p.product_id)) as total' : ',-1 as total') . " FROM " . $text_sql_category_left_join . " p LEFT JOIN " . DB_PREFIX . "review r ON (p.product_id = r.product_id) WHERE IF(r.product_id IS NOT NULL, r.status = '1', p.product_id <> '0') GROUP BY r.product_id";
			
			if (!$getCache or ($getCache and !$this->cache->get('ratings' . $category_id_cache))) {
				$temp_ratings = array();
				$query_ratins = $this->db->query($sql_ratins);
				if ($query_ratins->num_rows) {$query_ratins_rows = $query_ratins->rows;} else {$query_ratins_rows = false;}
				
				if ($query_ratins_rows) {
					foreach ($query_ratins_rows as $query_stock_status_stock) {
						$temp_ratings[$query_stock_status_stock['ratings']][] = array(
							'rating_value_total'  	 => $query_stock_status_stock['total'],
						);
					}
				}
				
				if ($temp_ratings) {
					foreach ($temp_ratings as $key => $value) {
						$con = 0;
						foreach ($value as $key_2 => $value_2) {
							$con += $value_2['rating_value_total'];
							
						}
						$common_value_data['ratings'][] = array(
							'rating'				=> $key,
							'rating_value_total'  	=> $con
						);
					}
					
				}
				if ($getCache and isset($common_value_data['ratings'])) {$this->cache->set('ratings' . $category_id_cache, $common_value_data['ratings']);}
			} else {
				$common_value_data['ratings'] = $this->cache->get('ratings' . $category_id_cache);
			}
		}
		
		return $common_value_data;
}
	
public function getProductsCategory($products_array, $quantity_view) {
		$select = false;
		if (isset($this->request->get['select'])) {$select = $this->request->get['select'];}
		if (isset($this->request->post['select'])) {$select = $this->request->post['select'];}
		unset($this->request->post['select']); unset($this->request->get['select']);
		
		$attribute_id_data = array();
		$attribute_sort_order = array();
		
		$products_id = array();
		
		$this->load->model('tool/image');
		
		if ($products_array) {
			$enum_products_array = $this->getGenerationEnumeration($products_array);
			$common_value_data = array();

			if ($enum_products_array != "") {
				$attribute_value_id_query = $this->db->query("SELECT pa.attribute_id,pa.text" . ($quantity_view ? ',COUNT(DISTINCT pa.product_id) AS total' : ',-1 as total') . ",ad.name FROM " . DB_PREFIX . "product_attribute pa INNER JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) INNER JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id IN (" . $enum_products_array . ") AND pa.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY pa.attribute_id,pa.text");
				
				$temp_attribute_id_data = array();
				if ($attribute_value_id_query->num_rows) {
					foreach ($attribute_value_id_query->rows as $attribute_value_id) {
						if ($attribute_value_id['name']) {
							$temp_attribute_id_data[$attribute_value_id['name']][] = array(
								'attribute_id' 	   	=> $attribute_value_id['attribute_id'],
								'text'				=> $attribute_value_id['text'],
								'total'				=> $attribute_value_id['total'],
							);
						}
					}
				}
				foreach ($temp_attribute_id_data as $temp_key => $temp_value) {
					$attribute_values = array();
					foreach ($temp_value as $key => $value) {
						$attribute_values[] = array(
							'attribute_id' 	   		=> $value['attribute_id'],
							'text' 	   				=> $value['text'],
							'total' 	  			=> $value['total'],
						);
					}
					$common_value_data['attributes'][] = array(
						'attribute'      	=> $attribute_values
					);
				}

				$option_value_id_query = $this->db->query("SELECT option_id,option_value_id" . ($quantity_view ? ',COUNT(DISTINCT product_id) as total' : ',-1 as total') . " FROM " . DB_PREFIX . "product_option_value WHERE product_id IN (" . $enum_products_array . ") GROUP BY option_value_id");
				
				$option_id_data = array();
				$temp_option_id_data = array();
				if ($option_value_id_query->num_rows) {
					$option_value_id_query_rows = array_map("unserialize", array_unique(array_map("serialize", $option_value_id_query->rows)));
					foreach ($option_value_id_query_rows as $option_val_id) {
						$temp_option_id_data[(int)$option_val_id['option_id']][] = array(
							'option_value_id' 		=> (int)$option_val_id['option_value_id'],
							'option_value_total' 	=> $option_val_id['total'],
						);
					}
					foreach ($temp_option_id_data as $temp_key => $temp_value) {
						$option_values = array();
						$option_name = "";
						$option_type = "";
						foreach ($temp_value as $key => $value) {
							$option_values[] = array(
								'option_value_id'          => $value['option_value_id'],
								'option_value_total' 	   => $value['option_value_total'],
							);
						}
						$option_id_data[] = array(
							'option_value'      	   => $option_values
						);
					}
					$option_id_data = array_map("unserialize", array_unique(array_map("serialize", $option_id_data)));
					foreach ($option_id_data as $option_id) {
						$option_values = array();
						if ($option_id['option_value']) {
							foreach ($option_id['option_value'] as $option_value) {
								$option_values[] = array(
									'option_value_id'          => $option_value['option_value_id'],
									'option_value_total' 	   => $option_value['option_value_total'],
								);
							}
						}
						$common_value_data['options'][] = array(
							'option_value'      	   => $option_values
						);
					}
				}
			}
			foreach ($products_array as $product_value => $value) {
				$products_id[] = (int)$value;
				$products_id = array_map("unserialize", array_unique(array_map("serialize", $products_id)));
			}
		}
		$post_category_id = 0;
		if (isset($this->request->post['category_id'])) {$post_category_id = $this->request->post['category_id'];}
		if (isset($this->request->get['category_id'])) {$post_category_id = $this->request->get['category_id'];}
		if ($post_category_id != 0) {
			if (isset($this->request->get['category_id'])) {
				if (is_array($this->request->get['category_id'])) {$category_id_enum = $this->getGenerationEnumeration($this->request->get['category_id']);} else {$category_id_enum = $this->request->get['category_id'];}
			}
			if (isset($this->request->post['category_id'])) {
				if (is_array($this->request->post['category_id'])) {$category_id_enum = $this->getGenerationEnumeration($this->request->post['category_id']);} else {$category_id_enum = $this->request->post['category_id'];}
			}
			$query_categories_parent = $this->db->query("SELECT DISTINCT cp.category_id FROM " . DB_PREFIX . "category_path cp INNER JOIN " . DB_PREFIX . "category c ON (cp.category_id = c.category_id) WHERE cp.path_id = '" . (int)$category_id_enum . "' AND c.status = 1");
		} else {
			$query_categories_parent = $this->db->query("SELECT DISTINCT category_id FROM " . DB_PREFIX . "category WHERE status = 1 AND parent_id = 0");
		}
		$categories_parent_array = array();
		if ($query_categories_parent->num_rows) {
			foreach ($query_categories_parent->rows as $key => $value) {
				$categories_parent_array[] = $value['category_id'];
			}
		}
		$categories_parent_child_array = array();

		foreach ($categories_parent_array as $key => $value) {
			
			$query_categories_child = $this->db->query("SELECT DISTINCT c.category_id FROM " . DB_PREFIX . "category c INNER JOIN " . DB_PREFIX . "category_path cp ON (c.category_id = cp.category_id) INNER JOIN " . DB_PREFIX . "category_to_store cts ON (cp.category_id = cts.category_id) WHERE cp.path_id = '" . (int)$value . "' AND c.status = 1 AND cts.store_id = '" . (int)$this->config->get('config_store_id') . "'");
			
			$temp_categories = array();
			if ($query_categories_child->num_rows) {
				foreach ($query_categories_child->rows as $key_child => $value_child) {
					$temp_categories[] = (int)$value_child['category_id'];
				}
				$temp_categories = array_unique($temp_categories);
				$temp_categories_enum = $this->getGenerationEnumeration($temp_categories);
				$query_categories_child_products = $this->db->query("SELECT DISTINCT ptc.product_id FROM " . DB_PREFIX . "product_to_category ptc INNER JOIN " . DB_PREFIX . "product_to_store pts ON (ptc.product_id = pts.product_id) INNER JOIN " . DB_PREFIX . "product p ON (pts.product_id = p.product_id) WHERE ptc.category_id IN (" . $temp_categories_enum . ") AND p.status = '1' AND pts.store_id = '" . (int)$this->config->get('config_store_id') . "'");
				if ($query_categories_child_products->num_rows) {
					foreach ($query_categories_child_products->rows as $key_child_products => $value_child_products) {
						$categories_parent_child_array[$value][] = $value_child_products['product_id'];
					}
				}

			}
		}

		$categories = array();
		$categories_common_count = array();
		$categories_path = array();
		
		foreach ($products_id as $product_id) {
			foreach ($categories_parent_child_array as $key => $value) {
				foreach ($value as $key_child => $value_child) {
					if ($value_child == $product_id) {
						$categories[$key][] = (int)$product_id;
					}
				}
			}
		}

		$categories_count = array();
		foreach ($categories as $key => $category_id) {
			
			$categories_count[] = array(
				'category_id' 	=> $key,
				'count' 		=> count($category_id),
			);
		}
		
		if($categories_count) {
			$common_value_data['categories'] = $categories_count;
		}
		
		if (isset($this->request->get['path'])) {
			$category_id = $this->request->get['path'];
			$pars = explode('_', (string)$this->request->get['path']);
			$category_id = (int)array_pop($pars);
		} else {
			$category_id = false;
		}
		
		if (isset($this->request->post['category_id'])) {
			$category_id = $this->request->post['category_id'];
			
		}
		if ($products_array) {
			$products_ids = $this->getGenerationEnumeration($products_array);
		} else {
			$products_ids = false;
		}

		if ($products_ids) {
			$query_manufacturers = $this->db->query("SELECT p.manufacturer_id,m.name,m.image" . ($quantity_view ? ',COUNT(DISTINCT p.product_id) as total' : ',-1 as total') . " FROM " . DB_PREFIX . "product p INNER JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id IN (" . $this->db->escape($products_ids) . ") GROUP BY p.manufacturer_id");
			
			if ($query_manufacturers->num_rows) {
				
				foreach ($query_manufacturers->rows as $query_manufacturer) {

					if ($query_manufacturer['total']) {$total_manufacturers = $query_manufacturer['total'];} else {$total_manufacturers = "";}

					$common_value_data['manufacturers'][] = array(
						'manufacturer_id'    				=> $query_manufacturer['manufacturer_id'],
						'manufacturer_value_total' 	   		=> $total_manufacturers,
					);
				}
			}
		}
		
		if (isset($common_value_data['manufacturers'])) {$common_value_data['manufacturers'] = array_map("unserialize", array_unique(array_map("serialize", $common_value_data['manufacturers'])));}
		
		if ($products_ids) {
			if (isset($this->request->post['attributes_filter']) or isset($this->request->post['rating_filter']) or isset($this->request->post['keywords_filter']) or isset($this->request->post['option_filter']) or isset($this->request->post['manufacturers_filter']) or isset($this->request->post['prices_max_value'])) {
				$test_click_no_stock_status = true;
			}
			
			$this->load->language('extension/module/gofilter');
			
			$query_stock_statuses_stock = $this->db->query("SELECT IF(p.quantity > 0 OR ps.name = '" . $this->db->escape(mb_strtolower($this->language->get('text_stock'), 'UTF-8')) . "', 'stock', ps.stock_status_id) as stock_status_id,IF(p.quantity > 0, '" . $this->db->escape(mb_strtolower($this->language->get('text_stock'), 'UTF-8')) . "', ps.name) as name" . ($quantity_view ? ',COUNT(DISTINCT p.product_id) as total' : ',-1 as total') . " FROM " . DB_PREFIX . "product p INNER JOIN " . DB_PREFIX . "stock_status ps ON (p.stock_status_id = ps.stock_status_id) WHERE p.product_id IN (" . $this->db->escape($products_ids) . ") AND ps.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY stock_status_id");
			
			if ($query_stock_statuses_stock->num_rows) {
				foreach ($query_stock_statuses_stock->rows as $query_stock_status_stock) {
					$common_value_data['stock_status'][] = array(
						'stock_status_id'	=>	$query_stock_status_stock['stock_status_id'],
						'total'				=>	$query_stock_status_stock['total'],
					);
				}
			}
	
		}
		if (isset($common_value_data['stock_status'])) {$common_value_data['stock_status'] = array_map("unserialize", array_unique(array_map("serialize", $common_value_data['stock_status'])));}
		
		if ($products_id) {
			$ratings = array();
			$products_id_enum = $this->getGenerationEnumeration($products_id);
			
			$query_ratings = $this->db->query("SELECT ROUND(AVG(rating), 0) AS rating_total FROM " . DB_PREFIX . "review WHERE product_id IN (" . $products_id_enum . ") AND status = '1' GROUP BY product_id");
			
			if ($query_ratings->num_rows) {
				foreach ($query_ratings->rows as $rating) {
					if ($rating['rating_total']) {
							$ratings[] = array(
							'rating'	=>	(int)$rating['rating_total']
						);
					}
				}
			}
			foreach ($products_id as $key => $product_id) {
				$query_ratings = $this->db->query("SELECT ROUND(AVG(rating), 0) AS rating_total FROM " . DB_PREFIX . "review WHERE product_id = '" . (int)$product_id . "' AND status = '1' GROUP BY product_id ORDER BY product_id ASC");
				if ($query_ratings->num_rows) {
					foreach ($query_ratings->rows as $rating) {
						$ratings[] = array(
							'rating'	=>	(int)$rating['rating_total']
						);
					}
				} else {
					$ratings[] = array(
						'rating'	=>	'0'
					);
				}
			}
		}
		
		if (isset($ratings)) {
			$rating_values = array();
			foreach ($ratings as $key => $values) {
				foreach ($values as $key => $value) {
					$rating_values[] = (int)$value;
				}
			}
			
			array_multisort($rating_values, SORT_DESC, $rating_values);
		
			$rating_sort = array();
			foreach ($rating_values as $key => $value) {
				$rating_sort[] = $key;
			}
			
			$common_value_data['ratings'] = array_count_values($rating_values);

		}

		if ($products_id) {
			$query_prices = $this->db->query("SELECT DISTINCT p.price,p.tax_class_id,(SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount FROM " . DB_PREFIX . "product p INNER JOIN " . DB_PREFIX . "product_special pd ON (p.product_id = pd.product_id) INNER JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.product_id IN (" . $products_id_enum . ") AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");
			
			$prices_values = array();
			if ($query_prices->num_rows) {
				foreach ($query_prices->rows as $query_price) {
					if ((float)$query_price['special']) {
						$special = $this->tax->calculate($query_price['special'], $query_price['tax_class_id'], $this->config->get('config_tax'));
					} else {
						if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
							$price = $this->tax->calculate(($query_price['discount'] ? $query_price['discount'] : $query_price['price']), $query_price['tax_class_id'], $this->config->get('config_tax'));
						} else {
							$price = false;
						}
						$special = false;
					}
					$prices_values[] = 	(int)($special ? $special : $price);
				}
			}
			if ($prices_values) {
				$common_value_data['prices'][] = array(
					'max'      => max($prices_values),
					'min'      => min($prices_values)
				);
			}
		}
		
		if ($products_id) {
			return $common_value_data;
		} else {
			return false;
		}

}

	public function generationResultsSeo() {
		$seo_keywords = array();
		$temp_seo_keywords = array();
		$module_info = $this->getGofilter('gofilter');
		if (isset($module_info['gofilter_data'])) {
			$module_info_decode = json_decode($module_info['gofilter_data'], true);
		} else {
			$module_info_decode = false;
		}
		if (isset($module_info_decode['seo']['seo_keywords'])) {
			foreach ($module_info_decode['seo']['seo_keywords'] as $key_seo => $value_seo) {
				$temp_seo_keywords[$value_seo['keyword']] = $value_seo['language'];
			}
			foreach ($temp_seo_keywords as $key_seo_2_massiv => $value_seo_2_massiv) {
				foreach ($value_seo_2_massiv as $key_seo_2 => $value_seo_2) {
					if ($key_seo_2 == (int)$this->config->get('config_language_id')) {
						$seo_keywords[trim($value_seo_2)] = trim($key_seo_2_massiv);
					}
				}
			}
		}
		return $seo_keywords;
	}
	

	public function generationResultsCategory() {
		$result = "";
		
		$module_info = $this->getGofilter('gofilter');
		$seo_separator = $this->getGenerationSeparator();
		
		$seo_keywords = $this->generationResultsSeo();
		$filter_array = array();
		if (strpos($this->request->server['REQUEST_URI'], "gofilter/") !== false) {
			$massivs = explode('/', (string)$this->request->server['REQUEST_URI']);
			foreach ($massivs as $key => $value) {
				if ($value == "gofilter") {
					$key_route = $key + 2;
				}
			}
			foreach ($massivs as $key => $value) {
				if ($key >= $key_route) {
					$massiv = explode($seo_separator, $value);
					$key_shift = array_shift($massiv);
					$id = 0; foreach ($massiv as $value_2) {
						$filter_array[$key_shift][] = $value_2;
					}
				}
			}
			$name = "";$d = 0;
			if ($filter_array) {
				foreach ($filter_array as $keys_gen => $values_gen) {
					if ($keys_gen == "options" or $keys_gen == "attributes") {
						$d = 0;
					}
					foreach ($values_gen as $key_gen => $value_gen) {
						if ($keys_gen == "options" or $keys_gen == "attributes") {
							$result .= " ";
							if ($d%2 == 0) {$result .= ", ";}
						}

						$name = $this->testSeparator("_", $value_gen);
						$name = $this->resultSeparator("?", "/", $name);
						if ($seo_keywords) {
							foreach ($seo_keywords as $key_test_seo => $value_test_seo) {
								if (trim($name) == trim($value_test_seo)) {
									$name = $key_test_seo;
								}
							}
						}
						$result .= $name;
						if ($keys_gen != "options" and $keys_gen != "attributes") {
							$result .= ", ";
						}
						$d = $d + 1;
					}
				}
			}
		}
		return $result;
	}

}