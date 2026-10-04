<?php
class ControllerExtensionModuleGofilter extends Controller {

	public function index() {
		
		if (strpos($this->request->server['REQUEST_URI'], "gofilter/") === false) {setcookie('reset' . $this->session->getId(), $this->request->server['REQUEST_URI'], time() + 3600, '/', $this->request->server['HTTP_HOST']);}
		if (isset($this->request->get['path'])) {
			$pars = explode('_', (string)$this->request->get['path']);
			$categ_id = (int)array_pop($pars);
			if (strpos($this->request->server['REQUEST_URI'], "gofilter/") === false) {setcookie('categ' . $this->session->getId(), $categ_id, time() + 3600, '/', $this->request->server['HTTP_HOST']);}
		}
		
		$this->document->addStyle('catalog/view/javascript/jquery/go_filter/go_filter.css');
		$this->document->addStyle('catalog/view/javascript/jquery/go_filter/jquery-ui.css');
		$this->document->addStyle('catalog/view/javascript/jquery/go_filter/jquery.scrollbar.css');
		
		$this->document->addScript('catalog/view/javascript/jquery/go_filter/jquery-ui.js');
		$this->document->addScript('catalog/view/javascript/jquery/go_filter/go_filter.js');
		$this->document->addScript('catalog/view/javascript/jquery/go_filter/jquery.scrollbar.js');
		
		$data = $this->filter_common();

		return $this->load->view('extension/module/gofilter', $data);
	
	}
	
	public function filter_common() {
		
		static $modul = 0;
		$this->load->language('extension/module/gofilter');
		
		if (strpos($this->request->server['REQUEST_URI'], "gofilter/") !== false) {
			$this->load->model('extension/module/gofilter');
			$temp_result = $this->language->get('text_error').": ";
			$temp_result .= $this->model_extension_module_gofilter->generationResultsCategory();
			$this->document->setTitle($temp_result);
		}
		
		if (isset($this->request->cookie['reset' . $this->session->getId()])) {
			$data['set_cookie'] = str_replace('&amp;', '&', $_COOKIE['reset' . $this->session->getId()]);
		} else {
			$data['set_cookie'] = false;
		}
		
		if (isset($this->request->cookie['categ' . $this->session->getId()])) {
			$set_categ_cookie = $_COOKIE['categ' . $this->session->getId()];
		} else {
			$set_categ_cookie = false;
		}
		
		$this->load->model('tool/image');

		$data['text_find_products'] = $this->language->get('text_find_products');
		$data['text_products'] = $this->language->get('text_products');
		$data['text_select_category'] = $this->language->get('text_select_category');
		$data['text_select_category_parametr'] = $this->language->get('text_select_category_parametr');
		$data['text_more'] = $this->language->get('text_more');
		$data['text_hide'] = $this->language->get('text_hide');
		$data['text_select_option'] = $this->language->get('text_select_option');
		$data['text_products_no_empty'] = $this->language->get('text_products_no_empty');
		$data['text_option_no_empty'] = $this->language->get('text_option_no_empty');
		$data['text_show'] = $this->language->get('text_show');
		$data['text_reset'] = $this->language->get('text_reset');
		$data['text_price'] = $this->language->get('text_price');
		$data['text_range_price'] = $this->language->get('text_range_price');
		$data['text_from'] = $this->language->get('text_from');
		$data['text_to'] = $this->language->get('text_to');
		$data['text_category'] = $this->language->get('text_category');
		$data['text_select'] = $this->language->get('text_select');
		$data['text_manufacturers'] = $this->language->get('text_manufacturers');
		$data['text_stock_status'] = $this->language->get('text_stock_status');
		$data['text_rating'] = $this->language->get('text_rating');
		$data['text_keywords'] = $this->language->get('text_keywords');
		$data['text_keywords_placeholder'] = $this->language->get('text_keywords_placeholder');
		$data['text_keywords_placeholder_zap'] = $this->language->get('text_keywords_placeholder_zap');
		$data['text_keywords_placeholder_empty'] = $this->language->get('text_keywords_placeholder_empty');
		$data['text_reset_all'] = $this->language->get('text_reset_all');
		
		$data['text_filter'] = $this->language->get('text_filter');
		
			
		if (isset($this->request->post['go_mobile'])) {
			$data['go_mobile'] = true;
		} else {
			$data['go_mobile'] = false;
		}
		
		
		$data['url'] = '';

		if (isset($this->request->get['sort'])) {
			$data['url'] .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$data['url'] .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['limit'])) {
			$data['url'] .= '&limit=' . $this->request->get['limit'];
		}
		
		if (isset($this->request->get['nofilter'])) {
			$data['nofilter'] = $this->request->get['nofilter'];
		} else {
			$data['nofilter'] = false;
		}
		
		$data['route'] = 'common/home';
		
		if (isset($this->request->get['route'])) {
			$data['route'] = $this->request->get['route'];
		}
		
		if (isset($this->request->post['route_layout'])) {
			$data['route'] = $this->request->post['route_layout'];
		}
		
		if (isset($this->request->get['route_layout'])) {
			$data['route'] = $this->request->get['route_layout'];
		}
		
		$this->load->model('extension/module/gofilter');
		
		$category_id = false;
		if (isset($this->request->post['category_id'])) {
			$category_id = $this->request->post['category_id'];
		}
		if (isset($this->request->get['path'])) {
			$data['category_id'] = $this->request->get['path'];
			$pars = explode('_', (string)$this->request->get['path']);
			$category_id = (int)array_pop($pars);
			$data['page_category'] = $this->request->get['path'];
			$data['parent_category_hierarchy'] = $this->model_extension_module_gofilter->getHierarchyCategoryname($category_id);
			
		} else {
			$data['page_category'] = false;
		}
		if (isset($this->request->post['path'])) {
			$pars = explode('_', (string)$this->request->post['path']);
			$category_id = (int)array_pop($pars);
		}

		$getCache = $this->model_extension_module_gofilter->getCache();

		$data['location_href'] = $this->request->server['REQUEST_URI'];
			
		$layoute_id = 0;
		
		if ($data['route'] == 'product/category' && isset($this->request->get['path'])) {
			$this->load->model('catalog/category');

			$path = explode('_', (string)$this->request->get['path']);

			$layoute_id = $this->model_catalog_category->getCategoryLayoutId(end($path));
		}
		
		if ($data['route'] == 'product/product' && isset($this->request->get['product_id'])) {
			$this->load->model('catalog/product');

			$layoute_id = $this->model_catalog_product->getProductLayoutId($this->request->get['product_id']);
		}
		
		if ($data['route'] == 'information/information' && isset($this->request->get['information_id'])) {
			$this->load->model('catalog/information');

			$layoute_id = $this->model_catalog_information->getInformationLayoutId($this->request->get['information_id']);
		}
		
		if (!$layoute_id) {
			$layoute_id = $this->model_extension_module_gofilter->getRoute($data['route']);
		}
		
		if (!$layoute_id) {
			$layoute_id = $this->config->get('config_layout_id');
		}
		
		$module_info = $this->model_extension_module_gofilter->getGofilter('gofilter');

		$data['common_massiv'] = array();
		
		$massivs_status = array('status_price','status_category','status_keywords','status_manufacturers','status_stock','status_ratings','status_options','status_attributes','status_filter','attrib_empty','option_empty');
		
		$massivs_collapse = array('collapse_price','collapse_category','collapse_keywords','collapse_manufacturers','collapse_stock','collapse_ratings','collapse_options','collapse_attributes','collapse_filter');
		
		$massivs_desktop = array('desktop_price','desktop_category','desktop_keywords','desktop_manufacturers','desktop_stock','desktop_ratings','desktop_options','desktop_attributes','desktop_filter');
		
		$massivs_mobile = array('mobile_price','mobile_category','mobile_keywords','mobile_manufacturers','mobile_stock','mobile_ratings','mobile_options','mobile_attributes','mobile_filter');
		
		$massivs_style = array('color_caption', 'color_group', 'bg_group', 'color_product', 'color_product_no', 'bg_price');
		
		$massivs_settings = array('show_disable_view', 'job_view', 'popup_view', 'quantity_view', 'stock_quantity', 'id_content', 'id_column_left', 'id_column_right', 'id_button_mobile', 'max_height', 'count_show', 'status_cache', 'status_seo', 'seo_separator', 'filter_scripts');
		
		$massivs_sort_and_data_default = array('prices','categories','keywords_filter','options','attributes','manufacturers','stock_statuses','ratings');
		
		foreach ($massivs_status as $massiv_status => $massiv_value) {$data[$massiv_value] = '1';}
		
		foreach ($massivs_collapse as $massiv_collapse => $massiv_value) {$data[$massiv_value] = 0;}
		
		foreach ($massivs_desktop as $massiv_desktop => $massiv_value) {$data[$massiv_value] = 1;}
		
		foreach ($massivs_mobile as $massiv_mobile => $massiv_value) {$data[$massiv_value] = 1;}
		
		$data['ratings_type'] = 'checkbox';
		$data['status_stock_type'] = 'checkbox';
		$data['manufacturers_type'] = 'checkbox';
		$data['keywords_type'] = ' ';
		$data['category_type'] = 'link';
		
		$data['common_massiv']['view']['prices'] = 0;
		$data['common_massiv']['view']['categories'] = 0;
		$data['common_massiv']['view']['keywords_filter'] = 0;
		$data['common_massiv']['view']['options'] = 0;
		$data['common_massiv']['view']['attributes'] = 0;
		$data['common_massiv']['view']['manufacturers'] = 0;
		$data['common_massiv']['view']['stock_statuses'] = 0;
		$data['common_massiv']['view']['ratings'] = 0;
		$data['common_massiv']['categories_layout'] = 0;
		$data['common_massiv']['child_category'] = 0;
		$data['common_massiv']['view']['filter'] = 0;
		
		$data['on_category'] = false;
		
		$module_info = $this->model_extension_module_gofilter->getGofilter('gofilter');
		if (isset($module_info['gofilter_data'])) {
			$module_info_decode = json_decode($module_info['gofilter_data'], true);
		} else {
			$module_info_decode = false;
		}
		
		$data['seo_keywords'] = array();
		$temp_seo_keywords = array();
		if (isset($module_info_decode['seo']['seo_keywords'])) {
			foreach ($module_info_decode['seo']['seo_keywords'] as $key_seo => $value_seo) {
				$temp_seo_keywords[$value_seo['keyword']] = $value_seo['language'];
			}
			foreach ($temp_seo_keywords as $key_seo_2_massiv => $value_seo_2_massiv) {
				foreach ($value_seo_2_massiv as $key_seo_2 => $value_seo_2) {
					if ($key_seo_2 == (int)$this->config->get('config_language_id')) {
						$data['seo_keywords'][trim($value_seo_2)] = trim($key_seo_2_massiv);
					}
				}
			}
		}
		$attribute_empty = array();
		$options_empty = array();
		$status_massivs = array();
		$temp_categ = array();
		$temp_array_categ = array();
		$test_on_filter = false;
		if (isset($module_info['gofilter_data'])) {
			foreach (json_decode($module_info['gofilter_data'], true) as $module => $value) {
				if (isset($value['layout'])) {
					$temp_categ = array();
					$temp_array_categ = array();
					$test_stock_category = true;
					if (isset($value['categories_layout'])) {
							foreach ($value['categories_layout'] as $key_child => $value_child) {
								$temp_categ[] = $value_child;
							}
							if (isset($value['child_category'])) {
								$temp_array_categ = $this->model_extension_module_gofilter->getChildcategory($temp_categ);
							} else {
								$temp_array_categ = $temp_categ;
							}
						if (!in_array($category_id, $temp_array_categ) and $category_id and $temp_array_categ and $data['route'] == "product/category") {
							$test_stock_category = false;
						}
					}
					
					if (in_array((int)$this->config->get('config_store_id') . "-" . $layoute_id, $value['layout'])) {
						if ($test_stock_category) {
							$test_on_filter = true;
							$data['on_category'] = true;
							$data['common_massiv']['prices']['sort'] = $value['sort_price'];
							$data['common_massiv']['categories']['sort'] = $value['sort_category'];
							$data['common_massiv']['keywords_filter']['sort'] = $value['sort_keywords'];
							$data['common_massiv']['options']['sort'] = $value['sort_options'];
							$data['common_massiv']['attributes']['sort'] = $value['sort_attributes'];
							$data['common_massiv']['manufacturers']['sort'] = $value['sort_manufacturers'];
							$data['common_massiv']['stock_statuses']['sort'] = $value['sort_stock'];
							$data['common_massiv']['ratings']['sort'] = $value['sort_ratings'];
							if (isset($value['sort_filter'])) {$data['common_massiv']['filter']['sort'] = $value['sort_filter'];} else {$data['common_massiv']['filter']['sort'] = '9';}
							if (isset($value['categories_layout'])) {$data['common_massiv']['categories_layout'] = $value['categories_layout'];}
							if ($data['route'] == "product/category") {
								if (isset($value['categories_layout'])) {
									if (in_array($category_id, $temp_array_categ)) {
										if (isset($value['attributes_related'])) {
											foreach ($value['attributes_related'] as $key_attr => $value_massiv) {
												$attribute_empty[] = $value_massiv;
											}
										}
										if (isset($value['options_related'])) {
											foreach ($value['options_related'] as $key_options => $value_massiv_opt) {
												$options_empty[] = $value_massiv_opt;
											}
										}
									}
								} else {
								    if (isset($value['attributes_related'])) {
										foreach ($value['attributes_related'] as $key_attr => $value_massiv) {
											$attribute_empty[] = $value_massiv;
										}
									}
									if (isset($value['options_related'])) {
										foreach ($value['options_related'] as $key_options => $value_massiv_opt) {
											$options_empty[] = $value_massiv_opt;
										}
									}
								}
							} else {
								if (isset($value['attributes_related'])) {
									foreach ($value['attributes_related'] as $key_attr => $value_massiv) {
										$attribute_empty[] = $value_massiv;
									}
								}
								if (isset($value['options_related'])) {
									foreach ($value['options_related'] as $key_options => $value_massiv_opt) {
										$options_empty[] = $value_massiv_opt;
									}
								}
							}
							
							if (isset($value['view_price'])) {$data['common_massiv']['view']['prices'] = $value['view_price'];}
							if (isset($value['view_category'])) {$data['common_massiv']['view']['categories'] = $value['view_category'];}
							if (isset($value['view_keywords'])) {$data['common_massiv']['view']['keywords_filter'] = $value['view_keywords'];}
							if (isset($value['view_options'])) {$data['common_massiv']['view']['options'] = $value['view_options'];}
							if (isset($value['view_attributes'])) {$data['common_massiv']['view']['attributes'] = $value['view_attributes'];}
							if (isset($value['view_manufacturers'])) {$data['common_massiv']['view']['manufacturers'] = $value['view_manufacturers'];}
							if (isset($value['view_stock'])) {$data['common_massiv']['view']['stock_statuses'] = $value['view_stock'];}
							if (isset($value['view_ratings'])) {$data['common_massiv']['view']['ratings'] = $value['view_ratings'];}
							if (isset($value['view_filter'])) {$data['common_massiv']['view']['filter'] = $value['view_filter'];}
							
							foreach ($massivs_status as $massiv_status => $massiv_value) {
								if (isset($value[$massiv_value])) {
									$data[$massiv_value] = $value[$massiv_value];
									$status_massivs[$massiv_value] =  $value[$massiv_value];
								} else {
									$data[$massiv_value] = false;
								}
							}
							foreach ($massivs_collapse as $massiv_collapse => $massiv_value) {
								if (isset($value[$massiv_value])) {$data[$massiv_value] = $value[$massiv_value];} else {$data[$massiv_value] = false;}
							}
							foreach ($massivs_desktop as $massiv_desktop => $massiv_value) {
								if (isset($value[$massiv_value])) {$data[$massiv_value] = $value[$massiv_value];} else {$data[$massiv_value] = false;}
							}
							foreach ($massivs_mobile as $massiv_mobile => $massiv_value) {
								if (isset($value[$massiv_value])) {$data[$massiv_value] = $value[$massiv_value];} else {$data[$massiv_value] = false;}
							}
							if (isset($value['name_module'])) {$data['name_module'] = $value['name_module'];} else {$data['name_module'] = false;}
							$data['ratings_type'] = $value['ratings_type'];
							$data['status_stock_type'] = $value['status_stock_type'];
							$data['manufacturers_type'] = $value['manufacturers_type'];
							$data['keywords_type'] = $value['keywords_type'];
							$data['category_type'] = $value['category_type'];
							
						}
					}
				}
				foreach ($massivs_style as $massiv_key => $massiv_value) {
					if (isset($value[$massiv_value])) {$data[$massiv_value] = $value[$massiv_value];}
				}
				foreach ($massivs_settings as $massiv_key => $massiv_value) {
					if (isset($value[$massiv_value])) {$data[$massiv_value] = $value[$massiv_value];}
				}
			}
		} else {
			$key_sort = 1;
			foreach ($massivs_sort_and_data_default as $massiv_sort_and_data_default => $massiv_sort_and_data_value) {
				$data['common_massiv'][$massiv_sort_and_data_value]['sort'] = $key_sort;
				$key_sort = $key_sort + 1;
			}
		}
		
		if (!isset($data['id_content'])) {$data['id_content'] = "#content";}
		if (!isset($data['id_column_left'])) {$data['id_column_left'] = "#column-left";}
		if (!isset($data['id_column_right'])) {$data['id_column_right'] = "#column-right";}
		if (!isset($data['id_button_mobile'])) {$data['id_button_mobile'] = "#menu";}
		
		$job_view = 0;
		if (!isset($data['job_view'])) {$job_view = 1;}
		
		if (isset($data['seo_separator'])) {$seo_separator = $data['seo_separator'];} else {$seo_separator = "=";}
		
		if (isset($data['name_module'])) {$data['name_module'] = $data['name_module'][(int)$this->config->get('config_language_id')];} else {$data['name_module'] = $this->language->get('heading_title');}

		foreach ($massivs_sort_and_data_default as $massiv_sort_and_data_default => $massiv_sort_and_data_value) {
			$data['common_massiv'][$massiv_sort_and_data_value]['data'] = $massiv_sort_and_data_value;
		}
		
		$data['view_prices'] = false; $data['view_categories'] = false; $data['view_keywords_filter'] = false; $data['view_options'] = false; $data['view_attributes'] = false; $data['view_manufacturers'] = false; $data['view_stock_statuses'] = false; $data['view_ratings'] = false;
		if ($data['common_massiv']['view']['categories'] == 1) {$data['view_categories'] = "column";} else {$data['view_categories'] = "content";}
		if ($data['common_massiv']['view']['prices'] == 1) {$data['view_prices'] = "column";} else {$data['view_prices'] = "content";}
		if ($data['common_massiv']['view']['keywords_filter'] == 1) {$data['view_keywords_filter'] = "column";} else {$data['view_keywords_filter'] = "content";}
		if ($data['common_massiv']['view']['options'] == 1) {$data['view_options'] = "column";} else {$data['view_options'] = "content";}
		if ($data['common_massiv']['view']['attributes'] == 1) {$data['view_attributes'] = "column";} else {$data['view_attributes'] = "content";}
		if ($data['common_massiv']['view']['manufacturers'] == 1) {$data['view_manufacturers'] = "column";} else {$data['view_manufacturers'] = "content";}
		if ($data['common_massiv']['view']['stock_statuses'] == 1) {$data['view_stock_statuses'] = "column";} else {$data['view_stock_statuses'] = "content";}
		if ($data['common_massiv']['view']['ratings'] == 1) {$data['view_ratings'] = "column";} else {$data['view_ratings'] = "content";}
		if ($data['common_massiv']['view']['filter'] == 1) {$data['view_filter'] = "column";} else {$data['view_filter'] = "content";}
		
		if ((($data['view_ratings'] == "content" or !$data['status_ratings']) and ($data['view_stock_statuses'] == "content" or !$data['status_stock']) and ($data['view_manufacturers'] == "content" or !$data['status_manufacturers']) and ($data['view_attributes'] == "content" or !$data['status_attributes']) and ($data['view_options'] == "content" or !$data['status_options']) and ($data['view_keywords_filter'] == "content" or !$data['status_keywords']) and ($data['view_prices'] == "content" or !$data['status_price']) and ($data['view_categories'] == "content" or !$data['status_category'])) or (!$data['mobile_price'] and !$data['mobile_category'] and !$data['mobile_keywords'] and !$data['mobile_manufacturers'] and !$data['mobile_stock'] and !$data['mobile_ratings'] and !$data['mobile_options'] and !$data['mobile_attributes'])) {
			$data['mobile_pult'] = false;
		} else {
			$data['mobile_pult'] = true;
		}

		$data['category_id'] = false;
		
		if (isset($this->request->get['path'])) {
			$data['category_id'] = $this->request->get['path'];
			$pars = explode('_', (string)$this->request->get['path']);
			$data['category_id'] = (int)array_pop($pars);
		}
		
		if (isset($this->request->post['category_id'])) {
			$data['category_id'] = $this->request->post['category_id'];
		}
		
		if (isset($this->request->get['category_id'])) {
			$data['category_id'] = $this->request->get['category_id'];
		}
		
		if (isset($this->request->post['manufacturer_id'])) {
			$data['manufacturer_id'] = $this->request->post['manufacturer_id'];
		}
		if (isset($this->request->get['manufacturer_id'])) {
			$data['manufacturer_id'] = $this->request->get['manufacturer_id'];
		}
		
		if (isset($this->request->post['search'])) {
			$data['search'] = $this->request->post['search'];
		}
		if (isset($this->request->get['search'])) {
			$data['search'] = $this->request->get['search'];
		}
		
		if (isset($this->request->get['path'])) {
			$data['get_category_id'] = $this->request->get['path'];
		} else {
			$data['get_category_id'] = false;
		}
		$data['post_category_id'] = false;
		if ($set_categ_cookie and isset($this->request->post['category_id']) and $this->request->post['category_id'] != $set_categ_cookie) {
			$data['post_category_id'] = true;
		}
		
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
		}
		
		if($filter_array) {
			$data['seo_results'] = $this->model_extension_module_gofilter->getSeoElements($filter_array);
		} else {
			$data['seo_results'] = false;
		}
		
		if (isset($this->request->get['page'])) {
			$data['page'] = $this->request->get['page'];
		} else {
			$data['page'] = false;
		}
		if (isset($this->request->get['limit'])) {
			$data['limit'] = $this->request->get['limit'];
		} else {
			$data['limit'] = false;
		}
		if (isset($this->request->get['sort'])) {
			$data['sort'] = $this->request->get['sort'];
		} else {
			$data['sort'] = false;
		}
		if (isset($this->request->get['order'])) {
			$data['order'] = $this->request->get['order'];
		} else {
			$data['order'] = false;
		}
		if (isset($data['seo_results']['category_id'])) {$data['category_id'] = $data['seo_results']['category_id'];}

		$data['old_category_id'] = $this->getParentcategory($data['category_id']);

		$select = false;
		if (isset($this->request->get['select'])) {$select = $this->request->get['select'];}
		if (isset($this->request->post['select'])) {$select = $this->request->post['select'];}
		$data['select'] = $select;
		$post = false;
		if (isset($this->request->post)) {$post = $this->request->post;}
		
		$data['categories'] = array();
		
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		
		$this->load->model('localisation/currency');
		
		$data['code'] = $this->session->data['currency'];
		
		$data['currencies'] = array();

		$resuls = $this->model_localisation_currency->getCurrencies();

		foreach ($resuls as $result) {
			if ($result['status']) {
				$data['currencies'][] = array(
					'title'        => $result['title'],
					'code'         => $result['code'],
					'symbol_left'  => $result['symbol_left'],
					'symbol_right' => $result['symbol_right']
				);
			}
		}
		foreach ($data['currencies'] as $currency) {
			if ($currency['symbol_left'] && $currency['code'] == $data['code']) {
				$data['value_max_currency_left'] = $currency['symbol_left'];
			} elseif ($currency['symbol_right'] && $currency['code'] == $data['code']) {
				$data['value_max_currency_right'] = $currency['symbol_right'];
			}
		}
		if (isset($this->request->post['category_id'])) {
			$data['category_name'] = $this->model_extension_module_gofilter->getCategoryname($this->request->post['category_id']);
		} elseif (isset($this->request->get['category_id'])) {
			$data['category_name'] = $this->model_extension_module_gofilter->getCategoryname($this->request->get['category_id']);
		} else {
			if (isset($this->request->get['path'])) {
				$parts = explode('_', (string)$this->request->get['path']);
				$parts = (int)array_pop($parts);
				$data['category_name'] = $this->model_extension_module_gofilter->getCategoryname($parts);
			} else {
				$data['category_name'] = false;
			}
		}
		if (isset($this->request->get['category_id']) or isset($this->request->get['products_id']) or isset($this->request->get['category_filter']) or isset($this->request->get['prices_min_value']) or isset($this->request->get['prices_max_value']) or isset($this->request->get['keywords_filter']) or isset($this->request->get['manufacturers_filter']) or isset($this->request->get['attributes_filter'])) {
			$data['filter_getting'] = true;
		} else {$data['filter_getting'] = false;}
		 
		$data['options'] = array();
		$data['attributes'] = array();
		
		if (isset($this->request->post['cl'])) {$data['count_container'] = $this->request->post['cl'];} else {$data['count_container'] = false;}
		
		$prev_product_array = "";
		if (isset($this->request->post['prev_product'])) {
			$prev_product_array = $this->request->post['prev_product'];
		}
		if (isset($this->request->get['prev_product'])) {
			$prev_product_array = $this->request->get['prev_product'];
		}
		
		if (isset($data['quantity_view'])) {
			$quantity_view = false; $data['quantity_view'] = false;
		} else {
			$quantity_view = true; $data['quantity_view'] = true;
		}
		
		if (isset($data['show_disable_view'])) {
			$data['show_disable_view'] = true;
		} else {
			$data['show_disable_view'] = false;
		}
		
		if ($this->model_extension_module_gofilter->stockStock()) {
			$stockStock = true;
		} else {
			$stockStock = false;
		}
		
		$data['attribute_empty'] = $attribute_empty;
		$data['options_empty'] = $options_empty;
		
		$results = array();
		if ($test_on_filter) {
			if ($data['category_id']) {
				$results = $this->model_extension_module_gofilter->getCommonProducts($data['category_id'], $quantity_view, $getCache, $stockStock, $status_massivs, $data);
			} else {
				$results = $this->model_extension_module_gofilter->getCommonProducts(false, $quantity_view, $getCache, $stockStock, $status_massivs, $data);
			}
		}

		$data['old_price'] = false;
		if (isset($this->request->post['op'])) {$data['old_price'] = $this->request->post['op'];}
		if (isset($this->request->get['op'])) {$data['old_price'] = $this->request->get['op'];}
		unset($this->request->post['op']); unset($this->request->get['op']);
		
		$count = 0;
		if (isset($this->request->get)) {$count = count($this->request->get);}
		if (isset($this->request->post)) {$count = count($this->request->post);}
		
		$data['class'] = "no_popup";
		if (isset($this->request->post['class'])) {$data['class'] = $this->request->post['class'];}
		if (isset($this->request->get['class'])) {$data['class'] = $this->request->get['class'];}
		unset($this->request->post['class']); unset($this->request->get['class']);

		if (isset($this->request->get['route_layout']) or isset($this->request->post['route_layout']) or strpos($this->request->server['REQUEST_URI'], "gofilter/") !== false) {
			if ($this->request->get && $this->request->get['route'] != "extension/module/gofilter/live_option_product" && $this->request->get['route'] != "extension/module/gofilter/live_home_filter") {
				$results_product = $this->model_extension_module_gofilter->getParameterProducts($this->request->get, $quantity_view, $stockStock, $status_massivs, $job_view);
			}
			if ($this->request->post) {
				$results_product = $this->model_extension_module_gofilter->getParameterProducts($this->request->post, $quantity_view, $stockStock, $status_massivs, $job_view); 
				$data['count_products'] = $results_product['count'];
			}
		}
		if ($results) {
			$common_results_category = array();
			if (isset($results_product['categories'])) {
				foreach ($results_product['categories'] as $key => $value) {
					$common_results_category[] = (int)$value['category_id'];
				}
			} else {$common_results_category = false;}

			if (isset($results['categories'])) {
				if ($data['category_id']) {
					$categories = $this->model_extension_module_gofilter->getCategories($data['category_id'], $getCache);
					$data['parent_category_id'] = $data['category_id'];
					$data['parent_category_hierarchy'] = $this->model_extension_module_gofilter->getHierarchyCategoryname($data['category_id']);
				} else {
					$categories = $this->model_extension_module_gofilter->getCategories(0, $getCache);
				}
				if ($categories) {
					$temp_categories = array();
					foreach ($categories as $category) {
						$temp_categories[] = $category['category_id'];
					}
					$categories_enum = $this->getGenerationEnumeration($temp_categories);
					if (!isset($results_product['categories']) and $quantity_view) {
						$total_categories_massiv = $this->model_extension_module_gofilter->getTotalProducts($categories_enum, $getCache, $stockStock);
						$temp_category = array();
						if ($total_categories_massiv) {
							foreach ($total_categories_massiv as $total_categ) {
								$temp_category[$total_categ['categ']] = $total_categ['total'];
							}
						}
					}
					
					$total_categories = 0;
					foreach ($categories as $category) {
						
						if ($data['category_id'] == $category['category_id']) {
							$category_value_required = "1";
						} else {
							$category_value_required = false;
						}
						if (isset($temp_category) and isset($temp_category[$category['category_id']])) {
							$total_categories = $temp_category[$category['category_id']];
						} else {
							$total_categories = 0;
							$category_stock_required = false;
						}
						$category_stock_required = true;
						if ($common_results_category) {
							if (!in_array($category['category_id'], $common_results_category) and $common_results_category) {
								$category_stock_required = false;
							}
						}
						if (isset($results_product['categories']) and $quantity_view) {
							foreach ($results_product['categories'] as $key_results => $value_results) {
								if ($value_results['category_id'] == $category['category_id']) {
									$total_categories = $value_results['count'];
									if ($total_categories > 0) {$category_stock_required = true;}
								}
							}
						}
						
						if (isset($category['category_id'])) {
							if (isset($this->request->post['category_id']) and isset($this->request->post)) {
								$post_child_category = $this->request->post;
								unset($post_child_category['category_id']);
								$post_child_category['category_id'] = $category['category_id'];
							}
						}
						
						if ($category['name']) {
							$data['categories'][] = array(
								'category_id' 						=> $category['category_id'],
								'name'        						=> $category['name'],
								'total'        						=> $total_categories,
								'category_value_required'   		=> $category_value_required,
								'category_stock_required'   		=> $category_stock_required
							);
						}
					}
				}
				
				if (isset($data['categories'])) {
					foreach ($data['categories'] as $key => $value) {
						$sort_type_filter[$key] = $value['category_stock_required'];
						$data_type_filter[$key] = $value['name'];
					}
					if (isset($results_product['categories'])) {
						if (isset($sort_type_filter) and isset($data_type_filter)) {array_multisort($sort_type_filter, SORT_DESC, $data_type_filter, SORT_ASC, $data['categories']);}
					}
				}
					
				
			} else {
				if ($data['category_id']) {
					$data['parent_category_id'] = $this->model_catalog_category->getCategory($data['category_id']);
					$data['parent_category_hierarchy'] = $this->model_extension_module_gofilter->getHierarchyCategoryname($data['category_id']);
				}
			}
			if (isset($results['options'])) {
				$common_results_options_product = array();
				if (isset($results_product['options'])) {
					foreach ($results_product['options'] as $result_product) {
						foreach ($result_product['option_value'] as $key => $value) {
							$common_results_options_product[] = (int)$value['option_value_id'];
						}
					}
				}
				$option_value_array_required = array();
				if (isset($this->request->post['option_filter'])) {
					foreach ($this->request->post['option_filter'] as $key => $value) {
						$option_value_array_required[] = (int)$value;
					}
				}
				if (isset($this->request->get['option_filter'])) {
					foreach ($this->request->get['option_filter'] as $key => $value) {
						$option_value_array_required[] = (int)$value;
					}
				}
				
				foreach ($results['options'] as $result_option) {
					$option_values = array();
					foreach ($result_option['option_value'] as $value) {
						$option_value_required = true;
						if (!in_array($value['option_value_id'], $option_value_array_required)) {
							$option_value_required = false;
						}
						$option_stock_required = true;
						if (!in_array($value['option_value_id'], $common_results_options_product) and $common_results_options_product) {
							$option_stock_required = false;
						}
						$total_options = $value['option_value_total'];
						if (isset($results_product)) {
							if (isset($data['job_view'])) {$total_options = 0;}
							if (isset($results_product['options'])) {
								foreach ($results_product['options'] as $result_product) {
									foreach ($result_product['option_value'] as $value_result) {
										if ($value_result['option_value_id'] == $value['option_value_id']) {
											$total_options = $value_result['option_value_total'];
											if ($total_options > 0) {$option_stock_required = true;}
										}
									}
								}
							} else {
								$option_stock_required = false;
							}
						}
						
						if (isset($post['attributes_filter']) or isset($post['rating_filter']) or isset($post['keywords_filter']) or isset($post['stock_status_filter']) or isset($post['manufacturers_filter']) or isset($post['prices_max_value']) or isset($post['filter_filter'])) {
							$test_click_no_option_status = true;
						}
						$text_select_options = "options" . $result_option['option_id'];
						if (isset($post['select']) and $post['select'] == $text_select_options and !isset($test_click_no_option_status)) {
							if (!isset($data['job_view'])) {$option_stock_required = true;}
						}
						$test_option = true;
						if ($data['show_disable_view'] and !$option_stock_required) {
							$test_option = false;
						}
						if ($test_option) {
							if ($value['option_value_name']) {
								$option_values[] = array(
									'option_value_id'          => $value['option_value_id'],
									'option_value_required'    => $option_value_required,
									'option_stock_required'    => $option_stock_required,
									'option_value_name' 	   => $value['option_value_name'],
									'option_value_total' 	   => $total_options,
									'image'                    => $value['option_value_image'] ? $this->model_tool_image->resize($value['option_value_image'], 20, 20) : '',
								);
							}
						}
					}
					if ($option_values) {
						$sort_type_filter = array(); $data_type_filter = array();
						foreach ($option_values as $key => $value) {
							$sort_type_filter[$key] = $value['option_stock_required'];
							$data_type_filter[$key] = $value['option_value_name'];
						}
						if (isset($results_product['options'])) {
							array_multisort($sort_type_filter, SORT_DESC, $data_type_filter, SORT_ASC, $option_values);
						}
					}
					$options_show_required = true;
					if ($options_empty) {
						foreach ($options_empty as $key_empty_array => $value_empty_array) {
							if (!$data['option_empty']) {
								if ($result_option['option_id'] == $value_empty_array) {
									$options_show_required = false;
								}
							} else {
								if ($result_option['option_id'] == $value_empty_array) {
									$options_show_required = true;
								} else {
									$options_show_required = false;
								}
							}
						}
					}
					
					if ($data['show_disable_view']) {
						$option_show_group = true;
						if (isset($results_product)) {
							$option_show_group = false;
							if ($option_values) {
								foreach ($option_values as $test_stock_option) {
									if ($test_stock_option['option_stock_required']) {$option_show_group = true;}
								}
							}
							if (!$option_show_group) {$options_show_required = false;}
						}
					}
					
					$data['options'][] = array(
						'option_id'  					=> $result_option['option_id'],
						'name'       					=> $result_option['name'],
						'type'       					=> $result_option['type'],
						'options_show_required'    		=> $options_show_required,
						'option_value'      			=> $option_values
					);
				}
			}
			
			
			
			$common_results_product_attributes_product = array(); $common_results_key_attributes_product = array();
			
			if (isset($results['attributes'])) {
				
				if (isset($results_product['attributes'])) {
					foreach ($results_product['attributes'] as $key => $value) {
						foreach ($value as $result_value) {
							$common_results_product_attributes_product[] = $result_value['text'];
							$common_results_key_attributes_product[] = $key;
						}
					}
				}
				
				$attributes_value_array_required = array(); $attributes_key_array_required = array();
				if (isset($this->request->post['attributes_filter'])) {
					foreach ($this->request->post['attributes_filter'] as $key => $value) {
						foreach ($value as $key_2 => $value_2) {
							$attributes_value_array_required[] = $value_2;
						}
						$attributes_key_array_required[] = $key;
					}
				}
				if (isset($this->request->get['attributes_filter'])) {
					foreach ($this->request->get['attributes_filter'] as $key => $value) {
						foreach ($value as $key_2 => $value_2) {
							$attributes_value_array_required[] = $value_2;
						}
						$attributes_key_array_required[] = $key;
					}
				}
				
				foreach ($results['attributes'] as $attribute) {
					$attribute_values = array();
					foreach ($attribute['attribute'] as $value) {
						$attribute_value_required = false;
						if (in_array($value['text'], $attributes_value_array_required) and in_array($value['attribute_id'], $attributes_key_array_required) and $attributes_key_array_required) {
							$attribute_value_required = true;
						}
						$attribute_stock_required = true;
						if ((!in_array($value['text'], $common_results_product_attributes_product) and $common_results_product_attributes_product) and (!in_array($value['attribute_id'], $common_results_key_attributes_product) and $common_results_key_attributes_product)) {
							$attribute_stock_required = false;
						}
						if (isset($post['option_filter']) or isset($post['rating_filter']) or isset($post['keywords_filter']) or isset($post['stock_status_filter']) or isset($post['manufacturers_filter']) or isset($post['prices_max_value']) or isset($post['filter_filter'])) {
							$test_click_no_attribute_status = true;
						}
						$text_select_attributes = "attributes" . $value['attribute_id'];
						
						$attribute_stock_required = true;
						$total_attributes = $value['attribute_text_total'];
						if (isset($results_product)) {
							if (isset($post['select']) and $post['select'] != $text_select_attributes) {$total_attributes = 0;}
							if (isset($results_product['attributes'])) {
								if (isset($data['job_view'])) {$total_attributes = 0;}
								if (isset($results_product['attributes'][$value['attribute_id']])) {
									foreach ($results_product['attributes'][$value['attribute_id']] as $key => $result_value) {
										if ($result_value['text'] == $value['text']) {
											$total_attributes = $result_value['total'];
										}
									}
								}
							}
						}
						if ($total_attributes == 0) {$attribute_stock_required = false;}
						
						if (isset($post['select']) and $post['select'] == $text_select_attributes and !isset($test_click_no_attribute_status)) {
							if (!isset($data['job_view'])) {$attribute_stock_required = true;}
						}
						
						$test_attribute = true;
						if ($data['show_disable_view'] and !$attribute_stock_required) {
							$test_attribute = false;
						}
						if ($test_attribute) {
							if ($value['text']) {
								$attribute_values[] = array(
									'attribute_id'          			=> $value['attribute_id'],
									'text' 	   							=> $value['text'],
									'attribute_text_total' 	  			=> $total_attributes,
									'attribute_value_required'  		=> $attribute_value_required,
									'attribute_stock_required'  		=> $attribute_stock_required
								);
							}
						}
					}
					
					if ($attribute_values) {
						$sort_type_filter = array(); $data_type_filter = array();
						foreach ($attribute_values as $key => $value) {
							$sort_type_filter[$key] = $value['attribute_stock_required'];
							$data_type_filter[$key] = $value['text'];
						}
						if (isset($results_product['attributes'])) {
							array_multisort($sort_type_filter, SORT_DESC, $data_type_filter, SORT_ASC, $attribute_values);
						} else {
							array_multisort($data_type_filter, SORT_NUMERIC, $attribute_values);
						}
					}
					
					$attribute_show_required = true;
					if ($attribute_empty) {
						foreach ($attribute_empty as $key_empty_array => $value_empty_array) {
							if (!$data['attrib_empty']) {
								if ($value['attribute_id'] == $value_empty_array) {
									$attribute_show_required = false;
								}
							} else {
								if ($value['attribute_id'] == $value_empty_array) {
									$attribute_show_required = true;
								} else {
									$attribute_show_required = false;
								}
							}
						}
					}
					
					if ($data['show_disable_view']) {
						$attribute_show_group = true;
						if (isset($results_product)) {
							$attribute_show_group = false;
							if ($attribute_values) {
								foreach ($attribute_values as $test_stock_attribute) {
									if ($test_stock_attribute['attribute_stock_required']) {$attribute_show_group = true;}
								}
							}
							if (!$attribute_show_group) {$attribute_show_required = false;}
						}
					}
					
					$data['attributes'][] = array(
						'attribute_id'  					=> $attribute['attribute_id'],
						'name'       					 	=> $attribute['name'],
						'attribute'       					=> $attribute_values,
						'attribute_show_required'  			=> $attribute_show_required,
					);
				} 
			}

			/* filters */

			$filter_value_array_required = array();
			if (isset($this->request->post['filter_filter'])) {
				foreach ($this->request->post['filter_filter'] as $key => $value) {
					$filter_value_array_required[] = (int)$value;
				}
			}
			if (isset($this->request->get['filter_filter'])) {
				foreach ($this->request->get['filter_filter'] as $key => $value) {
					$filter_value_array_required[] = (int)$value;
				}
			}
			
			$common_results_filter_product = array();
			if (isset($results_product['filters'])) {
				foreach ($results_product['filters'] as $key_product => $result_product) {
					$common_results_filter_product[] = (int)$result_product['filter_id'];
				}
			}
			$data['filters'] = array();
			if (isset($results['filters'])) {
				foreach ($results['filters'] as $result_filters) {
					$filter_data = array();

					foreach ($result_filters['filter'] as $filter) {
						
						$filter_value_required = true;
						if (!in_array($filter['filter_id'], $filter_value_array_required)) {
							$filter_value_required = false;
						}
						
						$filter_stock_required = true;
						if (!in_array($filter['filter_id'], $common_results_filter_product) and $common_results_filter_product) {
							$filter_stock_required = false;
						}
						
						$total_filters = $filter['filter_value_total'];
						if (isset($results_product)) {
							if (isset($data['job_view'])) {$total_filters = 0;}
							if (isset($results_product['filters'])) {
								foreach ($results_product['filters'] as $key_product => $result_product) {
									if ($result_product['filter_id'] == $filter['filter_id']) {
										$total_filters = $result_product['filter_value_total'];
										if ($total_filters > 0) {$filter_stock_required = true;}
									}
								}
							} else {
								$filter_stock_required = false;
							}
						}
						
						if (isset($post['attributes_filter']) or isset($post['rating_filter']) or isset($post['keywords_filter']) or isset($post['stock_status_filter']) or isset($post['manufacturers_filter']) or isset($post['prices_max_value']) or isset($post['option_filter'])) {
							$test_click_no_filters_status = true;
						}
						$text_select_filters = "filter" . $result_filters['filter_group_id']; 
						if (isset($post['select']) and $post['select'] == $text_select_filters and !isset($test_click_no_filters_status)) {
							if (!isset($data['job_view'])) {$filter_stock_required = true;}
						}
						
						
						
						$test_filter = true;
						if ($data['show_disable_view'] and !$filter_stock_required) {
							$test_filter = false;
						}
						if ($test_filter) {
							$filter_data[] = array(
								'filter_id' 				=> $filter['filter_id'],
								'filter_value_required'     => $filter_value_required,
								'filter_stock_required'     => $filter_stock_required,
								'filter_value_total'     	=> $total_filters,
								'name'      				=> $filter['name']
							);
						}
					}
					$filter_show_required = true;
					if (isset($filter_empty)) {
						foreach ($filter_empty as $key_empty_array => $value_empty_array) {
							if ($filter['filter_id'] == $value_empty_array) {
								$filter_show_required = false;
							}
						}
					}
					if ($data['show_disable_view']) {
						$filter_show_group = true;
						if (isset($results_product)) {
							$filter_show_group = false;
							if ($filter_data) {
								foreach ($filter_data as $test_stock_filter) {
									if ($test_stock_filter['filter_stock_required']) {$filter_show_group = true;}
								}
							}
							if (!$filter_show_group) {$filter_show_required = false;}
						}
					}
					if ($result_filters['filter']) {
						$data['filters'][] = array(
							'filter_group_id' 			=> $result_filters['filter_group_id'],
							'name'            			=> $result_filters['name'],
							'filter_show_required'     	=> $filter_show_required,
							'filter'          			=> $filter_data
						);
					}
				}
			}

			/* filters */
			
			$data['manufacturers'] = array();
			if (isset($results['manufacturers'])) {
				$manufacturer_value_array_required = array();
				if (isset($this->request->post['manufacturers_filter'])) {
					foreach ($this->request->post['manufacturers_filter'] as $key_status => $value_status) {
						$manufacturer_value_array_required[] = $value_status;
					}
				}
				if (isset($this->request->get['manufacturers_filter'])) {
					foreach ($this->request->get['manufacturers_filter'] as $key_status => $value_status) {
						$manufacturer_value_array_required[] = $value_status;
					}
				}
				$common_results_manufacturers_product = array();
				if (isset($results_product['manufacturers'])) {
					foreach ($results_product['manufacturers'] as $key => $value) {
						$common_results_manufacturers_product[] = (int)$value['manufacturer_id'];
					}
				}
				foreach ($results['manufacturers'] as $manufacturer) {
					$manufacturers_value_required = false;
					if (in_array($manufacturer['manufacturer_id'], $manufacturer_value_array_required)) {
						$manufacturers_value_required = true;
					}
					$manufacturers_stock_required = true;
					if ($common_results_manufacturers_product) {
						if (!in_array($manufacturer['manufacturer_id'], $common_results_manufacturers_product) and $common_results_manufacturers_product) {
							$manufacturers_stock_required = false;
						}
					}
					if (isset($post['option_filter']) or isset($post['rating_filter']) or isset($post['keywords_filter']) or isset($post['stock_status_filter']) or isset($post['attributes_filter']) or isset($post['prices_max_value']) or isset($post['filter_filter'])) {
						$test_click_no_manuf = true;
					}
					if (isset($select) and $select == "manuf" and !isset($test_click_no_manuf)) {$manufacturers_stock_required = true; $manufacturers_stock_or = true;} else {
						$manufacturers_stock_or = false;
					}
					if (!isset($post['manufacturers_filter'])) {$manufacturers_stock_or = false;}
					$total_manufacturers = $manufacturer['manufacturer_value_total'];
					if (isset($results_product)) {
						if (isset($results_product['manufacturers'])) {
							foreach ($results_product['manufacturers'] as $value) {
								if ($value['manufacturer_id'] == $manufacturer['manufacturer_id']) {
									$total_manufacturers = $value['manufacturer_value_total'];
									if ($total_manufacturers > 0) {$manufacturers_stock_required = true;}
								}
							}
						} else {
							$manufacturers_stock_required = false;
						}
					}
					$test_manuf = true;
					if ($data['show_disable_view'] and !$manufacturers_stock_required) {
						$test_manuf = false;
					}
					if ((isset($data['manufacturer_id']) or strpos($data['route'], "product/manufacturer/info") !== false) and $data['manufacturer_id'] != $manufacturer['manufacturer_id']) {$test_manuf = false;}
					
					if ($test_manuf) {
						if ($manufacturer['name']) {
							$data['manufacturers'][] = array(
								'manufacturer_id'  					=> $manufacturer['manufacturer_id'],
								'name'       						=> $manufacturer['name'],
								'image'       						=> $manufacturer['image'],
								'manufacturer_value_required'   	=> $manufacturers_value_required,
								'manufacturer_value_total'   		=> $total_manufacturers,
								'manufacturers_stock_required'  	=> $manufacturers_stock_required,
								'manufacturers_stock_or'  			=> $manufacturers_stock_or
							);
						}
					}
				}
				if ($data['show_disable_view']) {
					$manuf_show_group = true;
					if (isset($results_product)) {
						$manuf_show_group = false;
						if ($data['manufacturers']) {
							foreach ($data['manufacturers'] as $test_stock_manuf) {
								if ($test_stock_manuf['manufacturers_stock_required']) {$manuf_show_group = true;}
							}
						}
						if (!$manuf_show_group) {$data['manufacturers'] = array();}
					}
				}
				if ($data['manufacturers']) {
					if (isset($results_product['manufacturers'])) {
						$sort_type_filter = array(); $data_type_filter = array();
						foreach ($data['manufacturers'] as $key => $value) {
							$sort_type_filter[$key] = $value['manufacturers_stock_required'];
							$data_type_filter[$key] = $value['name'];
						}
						array_multisort($sort_type_filter, SORT_DESC, $data_type_filter, SORT_ASC, $data['manufacturers']);
					}
				}
			}
			
			if (isset($results['stock_status'])) {
				$stock_status_value_array_required = array();
				if (isset($this->request->post['stock_status_filter'])) {
					foreach ($this->request->post['stock_status_filter'] as $key_status => $value_status) {
						$stock_status_value_array_required[] = $value_status;
					}
				}
				if (isset($this->request->get['stock_status_filter'])) {
					foreach ($this->request->get['stock_status_filter'] as $key_status => $value_status) {
						$stock_status_value_array_required[] = $value_status;
					}
				}
				$common_results_stock_status_product = array();
				if (isset($results_product['stock_status'])) {
					foreach ($results_product['stock_status'] as $value) {
						$common_results_stock_status_product[] = $value['stock_status_id'];
					}
				}
				
				foreach ($results['stock_status'] as $key_status => $stock_status) {
					if (mb_strtolower($stock_status['name'], 'UTF-8') != mb_strtolower($this->language->get('text_stock'), 'UTF-8')) {$status_id = $stock_status['status_id'];} else {$status_id = "stock";}
					if (in_array($status_id, $stock_status_value_array_required)) {
						$stock_status_value_required = true;
					} else {
						$stock_status_value_required = false;
					}
					$stock_status_stock_required = true;
					if (!in_array($status_id, $common_results_stock_status_product) and $common_results_stock_status_product) {
						$stock_status_stock_required = false;
					}
					if (isset($post['attributes_filter']) or isset($post['rating_filter']) or isset($post['keywords_filter']) or isset($post['option_filter']) or isset($post['manufacturers_filter']) or isset($post['prices_max_value']) or isset($post['filter_filter'])) {
						$test_click_no_stock_status = true;
					}
					if (isset($post['select']) and $post['select'] == "stock" and !isset($test_click_no_stock_status)) {$stock_status_stock_required = true; $stock_status_stock_or = true;} else {
						$stock_status_stock_or = false;
					}
					if (!isset($post['stock_status_filter'])) {$stock_status_stock_or = false;}
					$total_stock = $stock_status['count'];
					if (isset($results_product)) {
						if (isset($results_product['stock_status'])) {
							if (isset($results_product['stock_status'])) {
								foreach ($results_product['stock_status'] as $value) {
									if ($value['stock_status_id'] == $status_id) {
										$total_stock = $value['total'];
										if ($total_stock > 0) {$stock_status_stock_required = true;}
									}
								}
							}
						} else {
							$stock_status_stock_required = false;
						}
					}
					$test_stock = true;
					if ($data['show_disable_view'] and !$stock_status_stock_required) {
						$test_stock = false;
					}
					if ($test_stock) {
						if ($stock_status['name']) {
							$data['stock_statuses'][] = array(
								'status_id'       					=> $status_id,
								'stock_status_value_required'   	=> $stock_status_value_required,
								'name'       						=> $stock_status['name'],
								'stock_status_value_total'   		=> $total_stock,
								'stock_status_stock_required'   	=> $stock_status_stock_required,
								'stock_status_stock_or'   			=> $stock_status_stock_or,
							);
						}
					}
					}
				if ($data['show_disable_view']) {
					$stock_show_group = true;
					if (isset($results_product)) {
						$stock_show_group = false;
						if ($data['stock_statuses']) {
							foreach ($data['stock_statuses'] as $test_stock_stock) {
								if ($test_stock_stock['stock_status_stock_required']) {$stock_show_group = true;}
							}
						}
						if (!$stock_show_group) {$data['stock_statuses'] = array();}
					}
				}
				if ($data['stock_statuses']) {
					$sort_type_filter = array(); $data_type_filter = array();
					foreach ($data['stock_statuses'] as $key => $value) {
						$sort_type_filter[$key] = $value['stock_status_stock_required'];
						$data_type_filter[$key] = $value['name'];
					}
					if (isset($results_product['stock_status'])) {
						array_multisort($sort_type_filter, SORT_DESC, $data_type_filter, SORT_ASC, $data['stock_statuses']);
					} else {
						array_multisort($data_type_filter, SORT_ASC, $data['stock_statuses']);
					}
				}
			}
			
			if (isset($results['prices'])) {
				foreach ($results['prices'] as $price) {
					$data['max_price'] = $price['max'];
					if ($data['old_price']) {
						$data['max_price_const'] = $data['old_price'];
					} else {
						$data['max_price_const'] = $price['max'];
					}
					$data['prices_max_value'] = $price['max'];
					$data['min_price'] = $price['min'];
					$data['step_price'] = $data['max_price']/20;
					$data['min_const_price'] = $price['min'];
					
				}
				
				if (isset($results_product['prices'])) {
					foreach ($results_product['prices'] as $value) {
						$data['max_price'] = $value['max'];
						if ($data['old_price']) {
						$data['max_price_const'] = $data['old_price'];
					} else {
						$data['max_price_const'] = $value['max'];
					}
					$data['prices_max_value'] = $value['max'];
					$data['min_price'] = $value['min'];
					$data['step_price'] = $data['max_price']/20;
					}
				}
				if (strpos($this->request->server['REQUEST_URI'], "gofilter/") !== false) {
					if (isset($data['seo_results']['prices_min_value'])) {
						$data['min_price'] = $data['seo_results']['prices_min_value'];
					}
					if (isset($data['seo_results']['prices_max_value'])) {
						$data['max_price'] = $data['seo_results']['prices_max_value'];
					}
				}
			}
			
			$common_results_ratings_product = array();

			if (isset($results_product['ratings'])) {
				foreach ($results_product['ratings'] as $key => $value) {
					$common_results_ratings_product[] = (int)$key;
				}
			}
			$data['ratings'] = array();
			if (isset($results['ratings'])) {
				$rating_value_array_required = array();
				if (isset($this->request->post['rating_filter'])) {
					foreach ($this->request->post['rating_filter'] as $key_rating => $value_rating) {
						$rating_value_array_required[] = $value_rating;
					}
				}
				if (isset($this->request->get['rating_filter'])) {
					foreach ($this->request->get['rating_filter'] as $key_rating => $value_rating) {
						$rating_value_array_required[] = $value_rating;
					}
				}
				foreach ($results['ratings'] as $key => $value) {
					$ratings_stock_required = true;
					if ($common_results_ratings_product) {
						if (!in_array($value['rating'], $common_results_ratings_product) and $common_results_ratings_product) {
							$ratings_stock_required = false;
						}
					}
					if (in_array($value['rating'], $rating_value_array_required)) {
						$rating_value_required = "1";
					} else {
						$rating_value_required = false;
					}
					if (isset($this->request->post['option_filter']) or isset($this->request->post['attributes_filter']) or isset($this->request->post['keywords_filter']) or isset($this->request->post['stock_status_filter']) or isset($this->request->post['manufacturers_filter']) or isset($this->request->post['prices_max_value']) or isset($post['filter_filter'])) {
						$test_click_no_rating = true;
					}
					if ($select == "rating" and !isset($test_click_no_rating)) {
						$ratings_stock_required = true; $ratings_stock_or = true;
					} else {
						$ratings_stock_or = false;
					}
					
					if (!isset($post['rating_filter'])) {$ratings_stock_or = false;}
					
					$rating_value_total = $value['rating_value_total'];
					
					if (isset($results_product)) {
						if (isset($results_product['ratings'])) {
							foreach ($results_product['ratings'] as $value_res) {
								if ($value_res['rating'] == $value['rating']) {
									$rating_value_total = $value_res['total'];
									if ($rating_value_total > 0) {$ratings_stock_required = true;}
								}
							}
						} else {
							$ratings_stock_required = false;
						}
					}
					
					$test_rating = true;
					if ($data['show_disable_view'] and !$ratings_stock_required) {
						$test_rating = false;
					}
					if ($test_rating) {
						$data['ratings'][] = array(
							'rating'  						=> $value['rating'],
							'rating_value_total'  			=> $rating_value_total,
							'rating_value_required'   		=> $rating_value_required,
							'ratings_stock_required'   		=> $ratings_stock_required,
							'ratings_stock_or'   			=> $ratings_stock_or
						);
					}
				}
			}
			if ($data['show_disable_view']) {
				$rating_show_group = true;
				if (isset($results_product)) {
					$rating_show_group = false;
					if ($data['ratings']) {
						foreach ($data['ratings'] as $test_stock_rating) {
							if ($test_stock_rating['ratings_stock_required']) {$rating_show_group = true;}
						}
					}
					if (!$rating_show_group) {$data['ratings'] = array();}
				}
			}
		}
		
		$q = 0;
		foreach ($data['common_massiv'] as $key => $value) {
			if (isset($value['sort'])) {
				$data_type_filter_massiv[] = $key;
				$sort_type_filter_massiv[] = $value['sort'];
			} else {
				$q = $q + 1;
				$data_type_filter_massiv[] = $key;
				$sort_type_filter_massiv[] = $q;
			}

		}
		
		array_multisort($sort_type_filter_massiv, SORT_ASC, $data_type_filter_massiv);

		
		$data['type_filters'] = $data_type_filter_massiv;

		/* формирование массива данных для сортировки */
		
		if (isset($data['keywords_type'])) {$data['delimitier'] = $data['keywords_type'];} else {$data['delimitier'] = " ";}
		
		if (isset($this->request->get['keywords_filter'])) {
			if ($this->request->get['keywords_filter'] != "") {
				$data['keywords_filter'] = array();
				$data['keywords_filter_text'] = $this->request->get['keywords_filter'];
				$arr_kewords = explode($data['delimitier'], (string)$this->request->get['keywords_filter']);
				foreach ($arr_kewords as $key => $value) {
					$data['keywords_filter'][] = $value;
				}
			}
		}
		
		if (isset($this->request->post['keywords_filter'])) {
			if ($this->request->post['keywords_filter'] != "") {
				$data['keywords_filter'] = array();
				$data['keywords_filter_text'] = $this->request->post['keywords_filter'];
				$arr_kewords = explode($data['delimitier'], (string)$this->request->post['keywords_filter']);
				foreach ($arr_kewords as $key => $value) {
					$data['keywords_filter'][] = $value;
				}
			}
		}

		
		if (isset($this->request->post['prices_max_value'])) {
			$data['get_price_max'] = $this->request->post['prices_max_value'];
			$data['get_price_min'] = $this->request->post['prices_min_value'];
			$data['max_price'] = $this->request->post['prices_max_value'];
			$data['prices_max_value'] = $this->request->post['prices_max_value'];
		} else {
			if (!isset($data['prices_max_value'])) {
				$data['prices_max_value'] = false;
			} 
			$data['get_price_max'] = false;
			$data['get_price_min'] = false;
		}
		
		if (isset($this->request->get['prices_max_value'])) {$data['get_price_max'] = $this->request->get['prices_max_value'];}
		if (isset($this->request->get['op'])) {$data['max_price_const'] = $this->request->get['op'];}

		if (isset($this->request->post['prices_min_value'])) {$data['min_price'] = $this->request->post['prices_min_value'];}
		
		if (strpos($this->request->server['REQUEST_URI'], "gofilter/") !== false) {
			if (isset($data['seo_results']['prices_min_value'])) {
				$data['min_price'] = $data['seo_results']['prices_min_value'];
			}
			if (isset($data['seo_results']['prices_max_value'])) {
				$data['max_price'] = $data['seo_results']['prices_max_value'];
			}
		}
		
		$data['gofilter_cloud'] = $this->load->controller('product/gofilterscripts');
		
		$data['modul'] = $modul++;
		
		return $data;
		
	}
	
	public function live_option_product() {
		
		$data = $this->filter_common();

		return $this->response->setOutput($this->load->view('extension/module/gofilter', $data));

	}
	
	public function getParentcategory($category_id) {

		$data_parent = false ;
		
		if ($category_id) {
			$query_parent = $this->db->query("SELECT parent_id FROM " . DB_PREFIX . "category WHERE category_id = '" . (int)$category_id . "'");
		
			if ($query_parent->rows) {
				if ($query_parent->rows) {$data_parent = $query_parent->row['parent_id'];}
			}
		}

		return $data_parent;
	}
	
	public function getGenerationEnumeration($perem_id_data) {
		
		$perem_id = "-1";
		$k = 0;
		if ($perem_id_data) {
			foreach ($perem_id_data as $key => $value) {
				$k = $k + 1;
				if ($k == 1) {
					$perem_id = (int)$value;
				} else {
					$perem_id .= "," . (int)$value;
				}
			}
		}
		return $perem_id;
	}
	
	public function live_home_filter() {
		
		$data = $this->filter_common();
		
		return $this->response->setOutput($this->load->view('extension/module/gofilter', $data));
		
	}
	
}