<?php
class ControllerExtensionModuleUniCustomMenuV2 extends Controller {
	public function index($setting) {
		static $module = 0;
		
		$store_id = (int)$this->config->get('config_store_id');
		
		$data = [];
		
		if(isset($setting['stores']) && in_array($store_id, $setting['stores'])) {
			$this->load->model('design/banner');
			$this->load->model('tool/image');
		
			$uniset = $this->config->get('config_unishop2');
		
			$lang_id = (int)$this->config->get('config_language_id');
		
			$this->document->addStyle('catalog/view/theme/unishop2/stylesheet/custom_menu_v2.css');
			$data['heading_title'] = isset($setting['title'][$lang_id]) ? $setting['title'][$lang_id] : '';
			$data['position'] = isset($setting['position']) ? $setting['position'] : '';
		
			$items = isset($setting['items']) ? $setting['items'] : [];
			$categories = isset($setting['categories']) ? $setting['categories'] : [];
		
			if (isset($this->request->get['path'])) {
				$parts = explode('_', (string)$this->request->get['path']);

				$category_id = (int)end($parts);
			} else {
				$category_id = 0;
			}
		
			$data['items'] = [];
		
			$isMobile = $uniset['is_mobile'];
		
			$setting['hide_on_mobile'] = isset($setting['hide_on_mobile']) ? $setting['hide_on_mobile'] : false;
		
			if(!$setting['hide_on_mobile'] || ($setting['hide_on_mobile'] && !$isMobile)) {
				if($items && (in_array('-1', $categories) || in_array($category_id, $categories))) {
					foreach($items as $item) {
						if(isset($item['title'][$lang_id])) {
					
							$childs = [];
					
							if(isset($item['child'])) {
								foreach($item['child'] as $child) {
									if(isset($child['title'][$lang_id])) {
								
										$childs2 = [];
								
										if(isset($child['child'])) {
											foreach($child['child'] as $child2) {
												if(isset($child2['title'][$lang_id])) {
													$childs2[] = [
														'title'	=> $child2['title'][$lang_id],
														'link'	=> $child2['link'][$lang_id],
														'sort'	=> $child2['sort'][$lang_id]
													];
												}
											}
									
											if(count($childs2) > 1) {
												array_multisort(array_column($childs2, 'sort'), SORT_ASC, $childs2);
											}
										}
								
										$childs[] = [
											'title'	=> $child['title'][$lang_id],
											'link'	=> $child['link'][$lang_id],
											'sort'	=> $child['sort'][$lang_id],
											'child'	=> $childs2
										];
									}
								}
						
								if(count($childs) > 1) {
									array_multisort(array_column($childs, 'sort'), SORT_ASC, $childs);
								}
							}
					
							$data['items'][] = [
								'title'	=> $item['title'][$lang_id],
								'link'	=> $item['link'][$lang_id],
								'sort'	=> $item['sort'][$lang_id],
								'child'	=> $childs
							];
						}
					}
			
					if(count($data['items']) > 1) {
						array_multisort(array_column($data['items'], 'sort'), SORT_ASC, $data['items']);
					}
				}
		
				$data['module'] = $module++;
			}
		}

		return $this->load->view('extension/module/uni_custom_menu_v2', $data);
	}
}
?>