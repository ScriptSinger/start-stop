<?php
class ControllerExtensionModuleUniCatwall extends Controller {
	public function index($setting) {
		static $module = 0;
		
		$store_id = (int)$this->config->get('config_store_id');
		$lang_id = (int)$this->config->get('config_language_id');
		
		$this->load->language('extension/module/uni_catwall');
		
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');		
		$this->load->model('tool/image');
		
		$data['show_name'] = $setting['show_name'];
		$data['uni_catwall_view_res'] = $setting['uni_catwall_view_res'];
		$data['uni_catwall_type'] = $setting['uni_catwall_type'];
		$data['uni_catwall_style'] = $setting['uni_catwall_style'];
		
		$this->document->addStyle('catalog/view/theme/default/stylesheet/uni_catwall.css');
		
		if ($setting['show_name']) {
			$data['heading_title'] = $setting['name'];
		} else {
			$data['heading_title'] = '';			
		}
		
		$data['text_view'] = $this->language->get('text_view');
		$data['text_view_cat'] = $this->language->get('text_view_cat');
		
		$cache_name = 'category.catwall.'.$module.'.'.$lang_id.'.'.$store_id;
		
		$data['categories'] = $this->cache->get($cache_name);
		
		if(!$data['categories']) {
			foreach ($setting['category'] as $cat_id) {
				$category = $this->model_catalog_category->getCategory((int)$cat_id);
			
				if($category) {
			
					$children_data = array();
			
					if ($setting['show_subcat']) {
					
						$children = $this->model_catalog_category->getCategories($category['category_id']);
					
						foreach (array_slice($children, 0, ($setting['subcat_limit'])) as $child) {
							$children_data[] = array(
								'category_id' => $child['category_id'],
								'name' 		  => $child['name'],
								'href' 		  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])
							);
						}
					}
			
					if ($category['image']) {
						$thumb = $this->model_tool_image->resize($category['image'], $setting['width'], $setting['height']);
					} else {
						$thumb = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}
			
					if ($setting['show_description']) {
						$description = utf8_substr(strip_tags(html_entity_decode($category['description'], ENT_QUOTES, 'UTF-8')), 0, $setting['description_limit']) . '..';
					} else {
						$description = '';				
					}
			
					$data['categories'][] = array(
						'category_id' => $category['category_id'],
						'description' => $description,
						'thumb'       => $thumb,
						'name'        => $category['name'],
						'children'    => $children_data,
						'href'        => $this->url->link('product/category', 'path=' . $category['category_id'])
					);
				}
			}
			
			if($data['categories']) {
				$this->cache->set($cache_name, $data['categories']);
			}
		}
		
		$data['module'] = $module++;

		return $this->load->view('extension/module/uni_catwall', $data);
	}
}