<?php
class ControllerExtensionModuleUniCustomMenuV2 extends Controller {
	private $error = [];

	public function index() {
		$this->load->language('extension/module/uni_custom_menu_v2');
		
		$this->load->model('localisation/language');
		$this->load->model('setting/store');
		$this->load->model('catalog/category');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
		
		$this->load->model('setting/module');
		$token = 'user_token='.$this->session->data['user_token'];
		
		$data['token'] = $token;
		
		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('uni_custom_menu_v2', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', $token . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $token, true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', $token . '&type=module', true)
		];

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = [
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/uni_custom_menu_v2', $token, true)
			];
		} else {
			$data['breadcrumbs'][] = [
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/uni_custom_menu_v2', $token . '&module_id=' . $this->request->get['module_id'], true)
			];
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/uni_custom_menu_v2', $token, true);
		} else {
			$data['action'] = $this->url->link('extension/module/uni_custom_menu_v2', $token . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', $token . '&type=module', true);
		
		$filter_data = [
			'filter_name' => '',
			'sort'        => 'name',
			'order'       => 'ASC',
			'start'       => 0,
			'limit'       => 2000
		];

		$data['categories'] = $this->model_catalog_category->getCategories($filter_data);
		
		$data['stores'][0] = [
			'store_id' => 0, 
			'name' => html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')
		];
		
        $stores = $this->model_setting_store->getStores();
		
		if($stores) {
			foreach ($stores as $store) {
				$data['stores'][$store['store_id']] = [
					'store_id' => $store['store_id'],
					'name' => html_entity_decode($store['name'], ENT_QUOTES, 'UTF-8')
				];
			}
		}

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['title'])) {
			$data['title'] = $this->request->post['title'];
		} elseif (!empty($module_info['title'])) {
			$data['title'] = $module_info['title'];
		} else {
			$data['title'] = '';
		}

		if (isset($this->request->post['items'])) {
			$data['items'] = $this->request->post['items'];
		} elseif (!empty($module_info['items'])) {
			$data['items'] = $module_info['items'];
		} else {
			$data['items'] = '';
		}
		
		if (isset($this->request->post['categories'])) {
			$data['selected_categories'] = $this->request->post['categories'];
		} elseif (!empty($module_info['categories'])) {
			$data['selected_categories'] = $module_info['categories'];
		} else {
			$data['selected_categories'] = [];
		}
		
		if (isset($this->request->post['stores'])) {
			$data['selected_stores'] = $this->request->post['stores'];
		} elseif (!empty($module_info['stores'])) {
			$data['selected_stores'] = $module_info['stores'];
		} else {
			$data['selected_stores'] = [];
		}
		
		if (isset($this->request->post['hide_on_mobile'])) {
			$data['hide_on_mobile'] = $this->request->post['hide_on_mobile'];
		} elseif (!empty($module_info)) {
			$data['hide_on_mobile'] = $module_info['hide_on_mobile'];
		} else {
			$data['hide_on_mobile'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/uni_custom_menu_v2', $data));
	}
	
	public function getLinks() {
		$this->load->language('extension/module/uni_custom_menu_v2');
		
		$this->load->model('extension/module/uni_settings');
		$this->load->model('setting/store');
		
		$elem = isset($this->request->get['elem']) ? $this->request->get['elem'] : '';
		$store_id = isset($this->request->get['store_id']) ? (int)$this->request->get['store_id'] : 0;
		$lang_id = isset($this->request->get['lang_id']) ? (int)$this->request->get['lang_id'] : 1;
		
		$stores_arr[0] = [
			'store_id' => 0, 
			'name' => html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')
		];
		
        $stores = $this->model_setting_store->getStores();
		
		if($stores) {
			foreach ($stores as $store) {
				$stores_arr[$store['store_id']] = [
					'store_id' => $store['store_id'],
					'name' => html_entity_decode($store['name'], ENT_QUOTES, 'UTF-8')
				];
			}
		} else {
			$store_id = 0;
		}
		
		if(count($stores_arr) > 1 && $store_id == -1) {
			$result = '<div class="heading">Выберите магазин</div>';
			
			$result .= '<ul class="popup-links">';
			
			foreach($stores_arr as $store) {
				$result .= '<li onclick="popupLinks(\''.$elem.'\', '.$store['store_id'].', '.$lang_id.')">'.$store['name'].'</li>';
			}
			
			$result .= '</ul>';
		}
		
		if($store_id != -1) {
			
			$result = '<div class="heading">Выберите ссылку</div>';
			$result .= '<ul class="popup-links links">';
		
			$settings = $this->model_extension_module_uni_settings->getSetting($store_id);

			if(isset($settings['header']['headerlinks2']) && $settings['header']['headerlinks2']) {
				
				$result1 = '';
				
				foreach($settings['header']['headerlinks2'] as $link) {
					if(isset($link['title'][$lang_id])) {
						$result1 .= '<li>';
						$result1 .= '<span data-elem="'.$elem.'" data-title="'.$link['title'][$lang_id].'" data-link="'.$link['link'][$lang_id].'">'.$link['title'][$lang_id].'</span>';
				
						if(isset($link['children'])) {
						
							$result1 .= '<ul>';
						
							foreach ($link['children'] as $child) {
								if(isset($child['title'][$lang_id])) {
								
									$result1 .= '<li>';
									$result1 .= '<span data-elem="'.$elem.'" data-title="'.$child['title'][$lang_id].'" data-link="'.$child['link'][$lang_id].'">'.$child['title'][$lang_id].'</span>';
								
									if(isset($child['children'])) {
										$result1 .= '<ul>';
									
										foreach ($child['children'] as $child2) {
											if(isset($child2['title'][$lang_id])) {
												$result .= '<li><span data-elem="'.$elem.'" data-title="'.$child2['title'][$lang_id].'" data-link="'.$child2['link'][$lang_id].'">'.$child2['title'][$lang_id].'</span></li>';
											}
										}
									
										$result1 .= '</ul>';
									}
								
									$result1 .= '</li>';
								}
							}
						
							$result1 .= '</ul>';
						}
						
						$result1 .= '</li>';
					}
				}
				
				if(!$result1) {
					$result .= '<li>'.$this->language->get('text_links_not_found').'</li>';
				} else {
					$result .= $result1;
				}
			} else {
				$result .= '<li>'.$this->language->get('text_links_not_found').'</li>';
			}
		
		}
		
		$result .= '</ul>';
		
		echo $result;
	}
	
	public function install() {
		
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/uni_custom_menu_v2')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}