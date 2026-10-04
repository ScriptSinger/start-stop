<?php
class ControllerExtensionModuleUniCatwall extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/uni_catwall');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_extension_module->addModule('uni_catwall', $this->request->post);
			} else {
				$this->model_extension_module->editModule($this->request->get['module_id'], $this->request->post);
			}
			
			$this->cache->delete('cache_name');

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true));
		}
		
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_style'] = $this->language->get('entry_style');
		$data['entry_name_show'] = $this->language->get('entry_name_show');
		$data['entry_category'] = $this->language->get('entry_category');
		$data['entry_subcat'] = $this->language->get('entry_subcat');
		$data['entry_subcat_limit'] = $this->language->get('entry_subcat_limit');
		$data['entry_width'] = $this->language->get('entry_width');
		$data['entry_height'] = $this->language->get('entry_height');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_description'] = $this->language->get('entry_description');
		$data['entry_description_limit'] = $this->language->get('entry_description_limit');
		$data['entry_show_name'] = $this->language->get('entry_show_name');
		$data['entry_view_resolution'] = $this->language->get('entry_view_resolution');
		$data['entry_type'] = $this->language->get('entry_type');
		$data['text_res_768'] = $this->language->get('text_res_768');
		$data['text_res_992'] = $this->language->get('text_res_992');
		$data['text_res_1200'] = $this->language->get('text_res_1200');		
		$data['text_choose'] = $this->language->get('text_choose');
		$data['text_type_1'] = $this->language->get('text_type_1');
		$data['text_type_2'] = $this->language->get('text_type_2');
		$data['text_type_3'] = $this->language->get('text_type_3');
		$data['text_style_0'] = $this->language->get('text_style_0');
		$data['text_style_1'] = $this->language->get('text_style_1');
		$data['text_style_2'] = $this->language->get('text_style_2');		
		$data['help_category'] = $this->language->get('help_category');
		$data['help_style'] = $this->language->get('help_style');
		$data['text_subcat_help'] = $this->language->get('text_subcat_help');
		$data['text_subcat_limit_help'] = $this->language->get('text_subcat_limit_help');
		$data['text_name_help'] = $this->language->get('text_name_help');
		$data['text_description_help'] = $this->language->get('text_description_help');
		$data['text_subcat_description_help'] = $this->language->get('text_subcat_description_help');		
				
		$data['tab_settings']      	    = $this->language->get('tab_settings');	
		$data['tab_help']      	        = $this->language->get('tab_help');	
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

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
		
		if (isset($this->error['subcat_limit'])) {
			$data['error_subcat_limit'] = $this->error['subcat_limit'];
		} else {
			$data['error_subcat_limit'] = '';
		}

		if (isset($this->error['width'])) {
			$data['error_width'] = $this->error['width'];
		} else {
			$data['error_width'] = '';
		}

		if (isset($this->error['height'])) {
			$data['error_height'] = $this->error['height'];
		} else {
			$data['error_height'] = '';
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/uni_catwall', 'token=' . $this->session->data['token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/uni_catwall', 'token=' . $this->session->data['token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/uni_catwall', 'token=' . $this->session->data['token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/uni_catwall', 'token=' . $this->session->data['token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_extension_module->getModule($this->request->get['module_id']);
		}

		$data['token'] = $this->session->data['token'];

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}
		
		if (isset($this->request->post['show_subcat'])) {
			$data['show_subcat'] = $this->request->post['show_subcat'];
		} elseif (!empty($module_info)) {
			$data['show_subcat'] = $module_info['show_subcat'];
		} else {
			$data['show_subcat'] = 1;
		}
		
		if (isset($this->request->post['subcat_limit'])) {
			$data['subcat_limit'] = $this->request->post['subcat_limit'];
		} elseif (!empty($module_info)) {
			$data['subcat_limit'] = $module_info['subcat_limit'];
		} else {
			$data['subcat_limit'] = 5;
		}
		
		if (isset($this->request->post['show_description'])) {
			$data['show_description'] = $this->request->post['show_description'];
		} elseif (!empty($module_info)) {
			$data['show_description'] = $module_info['show_description'];
		} else {
			$data['show_description'] = 1;
		}
		
		if (isset($this->request->post['show_name'])) {
			$data['show_name'] = $this->request->post['show_name'];
		} elseif (!empty($module_info)) {
			$data['show_name'] = $module_info['show_name'];
		} else {
			$data['show_name'] = 1;
		}
		
		if (isset($this->request->post['description_limit'])) {
			$data['description_limit'] = $this->request->post['description_limit'];
		} elseif (!empty($module_info)) {
			$data['description_limit'] = $module_info['description_limit'];
		} else {
			$data['description_limit'] = 195;
		}		
				
		if (isset($this->request->post['uni_catwall_view_res'])) {
			$data['uni_catwall_view_res'] = $this->request->post['uni_catwall_view_res'];
		} elseif (!empty($module_info)) {
			$data['uni_catwall_view_res'] = $module_info['uni_catwall_view_res'];
		} else {
			$data['uni_catwall_view_res'] = '0';
		}
		
		if (isset($this->request->post['uni_catwall_type'])) {
			$data['uni_catwall_type'] = $this->request->post['uni_catwall_type'];
		} elseif (!empty($module_info)) {
			$data['uni_catwall_type'] = $module_info['uni_catwall_type'];
		} else {
			$data['uni_catwall_type'] = '0';
		}
		
		if (isset($this->request->post['uni_catwall_style'])) {
			$data['uni_catwall_style'] = $this->request->post['uni_catwall_style'];
		} elseif (!empty($module_info)) {
			$data['uni_catwall_style'] = $module_info['uni_catwall_style'];
		} else {
			$data['uni_catwall_style'] = '1';
		}

		$this->load->model('catalog/category');

		$data['categorys'] = array();

		if (!empty($this->request->post['category'])) {
			$categorys = $this->request->post['category'];
		} elseif (!empty($module_info['category'])) {
			$categorys = $module_info['category'];
		} else {
			$categorys = array();
		}

		foreach ($categorys as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$data['categorys'][] = array(
					'category_id' => $category_info['category_id'],
					'name'       => $category_info['name']
				);
			}
		}

		if (isset($this->request->post['width'])) {
			$data['width'] = $this->request->post['width'];
		} elseif (!empty($module_info)) {
			$data['width'] = $module_info['width'];
		} else {
			$data['width'] = 200;
		}

		if (isset($this->request->post['height'])) {
			$data['height'] = $this->request->post['height'];
		} elseif (!empty($module_info)) {
			$data['height'] = $module_info['height'];
		} else {
			$data['height'] = 200;
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

		$this->response->setOutput($this->load->view('extension/module/uni_catwall', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/uni_catwall')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (!$this->request->post['width']) {
			$this->error['width'] = $this->language->get('error_width');
		}

		if (!$this->request->post['height']) {
			$this->error['height'] = $this->language->get('error_height');
		}
		
		return !$this->error;
	}
}