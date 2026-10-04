<?php
class ControllerExtensionModuleUniReviews extends Controller {
	private $error = [];

	public function index() {
		$this->load->language('extension/module/uni_reviews');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
		
		$this->document->addStyle('view/stylesheet/unishop.css');

		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('uni_reviews', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}
			
			$this->cache->delete('unishop.reviews');

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $data['heading_title'] = $this->language->get('heading_title');

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['error_name'] = isset($this->error['name']) ? $this->error['name'] : '';
        $data['error_header'] = isset($this->error['module_header']) ? $this->error['module_header'] : '';
		$data['error_order_type'] = isset($this->error['order_type']) ? $this->error['order_type'] : '';
        $data['error_layout'] = isset($this->error['layout']) ? $this->error['layout'] : '';
        $data['error_width'] = isset($this->error['width']) ? $this->error['width'] : '';
		$data['error_height'] = isset($this->error['height']) ? $this->error['height'] : '';

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true)
		];

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = [
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/uni_reviews', 'user_token=' . $this->session->data['user_token'], true)
			];
		} else {
			$data['breadcrumbs'][] = [
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/uni_reviews', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			];			
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/uni_reviews', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/uni_reviews', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}
		
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token='.$this->session->data['user_token'].'&type=module', true);
		
		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}
		
		$data['name'] = isset($module_info['name']) ? $module_info['name'] : '';
		$data['title'] = isset($module_info['title']) ? $module_info['title'] : '';
		$data['order_type'] = isset($module_info['order_type']) ? $module_info['order_type'] : '';
		
		if (isset($this->request->post['image_width'])) {
			$data['image_width'] = $this->request->post['image_width'];
		} elseif (!empty($module_info)) {
			$data['image_width'] = $module_info['image_width'];
		} else {
			$data['image_width'] = 100;
		}
		
		if (isset($this->request->post['image_height'])) {
			$data['image_height'] = $this->request->post['image_height'];
		} elseif (!empty($module_info)) {
			$data['image_height'] = $module_info['image_height'];
		} else {
			$data['image_height'] = 100;
		}
		
		$data['text_limit'] = isset($module_info['text_limit']) ? $module_info['text_limit'] : 100;
		$data['category_sensitive'] = isset($module_info['category_sensitive']) ? $module_info['category_sensitive'] : '';
		$data['show_all_button'] = isset($module_info['show_all_button']) ? $module_info['show_all_button'] : '';
		$data['show_all_button_link'] = isset($module_info['show_all_button_link']) ? $module_info['show_all_button_link'] : '';
		$data['limit'] = isset($module_info['limit']) ? $module_info['limit'] : 4;
		$data['view_type'] = isset($module_info['view_type']) ? $module_info['view_type'] : '';
		$data['status'] = isset($module_info['status']) ? $module_info['status'] : '';

        $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/uni_reviews', $data));
	}
	
	public function install() {	
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();
		
		$query = $this->db->query("SELECT query FROM `".DB_PREFIX."seo_url` WHERE `keyword` LIKE 'reviews' LIMIT 1");
		if ($query->num_rows == 0) {
			foreach ($languages as $language) {
				$this->db->query("INSERT INTO `".DB_PREFIX . "seo_url` SET store_id = 0, language_id = '".(int)$language['language_id']."', query = 'product/uni_reviews', keyword = 'reviews'");
			}
		}
	}
	
	public function newDataForReview($review_info = []) {
		
		$this->load->language('extension/module/uni_reviews');
		
		$data = [];
		
		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} elseif (!empty($review_info)) {
			$product_id = $review_info['product_id'];
		} else {
			$product_id = 0;
		}
		
		if (isset($this->request->post['review_id'])) {
			$data['review_id'] = $this->request->post['review_id'];
		} elseif (!empty($review_info)) {
			$data['review_id'] = $review_info['review_id'];
		} else {
			$data['review_id'] = 0;
		}
			
		if (isset($this->request->post['plus'])) {
			$data['plus'] = $this->request->post['plus'];
		} elseif (!empty($review_info)) {
			$data['plus'] = $review_info['plus'];
		} else {
			$data['plus'] = '';
		}
			
		if (isset($this->request->post['minus'])) {
			$data['minus'] = $this->request->post['minus'];
		} elseif (!empty($review_info)) {
			$data['minus'] = $review_info['minus'];
		} else {
			$data['minus'] = '';
		}
			
		if (isset($this->request->post['admin_reply'])) {
			$data['admin_reply'] = $this->request->post['admin_reply'];
		} elseif (!empty($review_info)) {
			$data['admin_reply'] = $review_info['admin_reply'];
		} else {
			$data['admin_reply'] = '';
		}
			
		if (isset($this->request->post['votes_plus'])) {
			$data['votes_plus'] = $this->request->post['votes_plus'];
		} elseif (!empty($review_info)) {
			$data['votes_plus'] = $review_info['votes_plus'];
		} else {
			$data['votes_plus'] = 0;
		}
			
		if (isset($this->request->post['votes_minus'])) {
			$data['votes_minus'] = $this->request->post['votes_minus'];
		} elseif (!empty($review_info)) {
			$data['votes_minus'] = $review_info['votes_minus'];
		} else {
			$data['votes_minus'] = 0;
		}
		
		if (isset($this->request->post['images'])) {
			$data['images'] = $this->request->post['images'];
			
			$this->load->model('tool/image');
			
			$data['thumbs'] = [];
			
			if($data['images']) {
				foreach($data['images'] as $image){
					$data['thumbs'][] = $this->model_tool_image->resize($image, 150, 150);
				}
			}
		} elseif (!empty($review_info)) {
			$data['images'] = json_decode($review_info['images'], true);
			
			$this->load->model('tool/image');
			
			$data['thumbs'] = [];
			
			if($data['images']) {
				foreach($data['images'] as $image){
					$data['thumbs'][] = $this->model_tool_image->resize($image, 150, 150);
				}
			}
		} else {
			$data['images'] = [];
			$data['thumbs'] = [];
		}
		
		if (isset($this->request->post['real_buyer'])) {
			$data['real_buyer'] = $this->request->post['real_buyer'];
		} elseif (!empty($review_info)) {
			$data['real_buyer'] = $review_info['real_buyer'];
		} else {
			$data['real_buyer'] = 0;
		}
		
		if (!isset($this->request->get['review_id'])) {
			$data['action'] = $this->url->link('extension/module/uni_reviews/uploadPhoto', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/uni_reviews/uploadPhoto', 'user_token=' . $this->session->data['user_token'] . '&review_id=' . $this->request->get['review_id'], true);
		}
		
		return $data;
	}
	
	public function uploadPhoto() {
		
		$this->load->language('extension/module/uni_reviews');
		
		$product_id = isset($this->request->post['product_id']) ? (int)$this->request->post['product_id'] : 0;
		$review_id = isset($this->request->post['review_id']) ? (int)$this->request->post['review_id'] : 0;
		
		$error = '';
		
		if(!isset($this->request->post['real_buyer'])) {
			$this->request->post['real_buyer'] = 0;
		}
		
		if(!isset($this->request->post['images'])) {
			$this->request->post['images'] = [];
		}	
		
		if($product_id && !empty($this->request->files['images'])) {
			
			$images = [];
			
			foreach($this->request->files['images'] as $key => $array) {
				foreach ($array as $index => $value) {
					$images[$index][$key] = $value;
				}
			}
			
			foreach($images as $key => $img) {
				if($img['name'] == '') {
					unset($images[$key]);
				}
			}
				
			$limit_photo = 5;
				
			if(count($images) > $limit_photo) {
				$images = array_slice($images, 0, $limit_photo);
			}
			
			if($images) {
				$memory_limit = ini_get('memory_limit');
				
				$rel_path = 'catalog/uni-reviews/';
				$abs_path = DIR_IMAGE.$rel_path;
				$tar_path = $abs_path.$product_id.'/';
				$img_path = $rel_path.$product_id.'/';
			
				$allowed_extensions = ['jpg', 'jpeg', 'png'];
				$allowed_mime_types = ['image/jpeg', 'image/png'];
				$allowed_img_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG];
			
				$max_size = 5242880;
				$min_res = 500;
				$max_res = 5000;
				$new_max_res = 1600;
				
				$htaccess = 'php_flag engine off'.PHP_EOL;
				$htaccess .= 'Require all denied'.PHP_EOL;
				$htaccess .= 'Order Deny, Allow'.PHP_EOL;
				$htaccess .= 'Deny from all'.PHP_EOL;
				
				if(!is_dir($abs_path)) {
					mkdir($abs_path, 0755);
				}
			
				if(!file_exists($abs_path.'.htaccess')) {
					file_put_contents($abs_path.'.htaccess', $htaccess);
				}
			
				if(is_dir($abs_path) && !is_dir($tar_path)) {
					mkdir($tar_path, 0755);
				}
				
				foreach($images as $key => $image) {
					if(is_file($image['tmp_name']) && is_uploaded_file($image['tmp_name'])) {
					
						$extension = mb_strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));

						if (!in_array($extension, $allowed_extensions)) {
							$error = sprintf($this->language->get('error_filetype'), $key+1);
						}
						
						$img_info = getimagesize($image['tmp_name']);
							
						if($img_info && is_array($img_info)) {
								
							$width = $img_info[0];
							$height = $img_info[1];
							$type = $img_info[2];
							$bits = $img_info['bits'];
							$mime = $img_info['mime'];
								
							if(!in_array($type, $allowed_img_types)) {
								$error = sprintf($this->language->get('error_filetype'), $key+1);
							}
								
							if(!in_array($mime, $allowed_mime_types)) {
								$error = sprintf($this->language->get('error_filetype'), $key+1);
							}

							if (($width < $min_res || $width > $max_res) || ($height < $min_res || $height > $max_res)) {
								$error = sprintf($this->language->get('error_resolution'), $key+1);
							}
						} else {
							$error = sprintf($this->language->get('error_filetype'), $key+1);
						}

						$content = file_get_contents($image['tmp_name']);

						if (preg_match('/\<\?php/i', $content)) {
							$error = sprintf($this->language->get('error_filetype'), $key+1);
						}
						
						if (filesize($image['tmp_name']) > $max_size) {
							$error = $this->error['warning'] = sprintf($this->language->get('error_filesize'), $key+1);
						}

						if ($image['error'] != UPLOAD_ERR_OK) {
							$error = $this->language->get('error_upload_' . $image['error']);
						}
						
						if (!$error) {
							$chars = 'qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP'; 
							$name_length = 8; 
							$chars_length = mb_strlen($chars)-1; 
					
							$img_name = ''; 

							while($name_length--) {
								$img_name .= $chars[rand(0, $chars_length)];
							}
						
							$file = $img_name.'.'.$extension;
						
							if($width > $new_max_res) {
								$width = $new_max_res;
								$height = $new_max_res / ($width / $height);
							} else if($height > $new_max_res) {
								$width = $new_max_res * ($width / $height);
								$height = $new_max_res;
							}
							
							if (extension_loaded('imagick')) {
								$new_image = new Imagick($image['tmp_name']);
								$new_image->stripImage();
								$new_image->resizeImage($width, 0, Imagick::FILTER_LANCZOS, 1);
								//$image->thumbnailImage($width, $height, true, true);
								$new_image->writeImage($tar_path.$file);
								$new_image->clear();
								$new_image->destroy();
							} else {
								if($width > 1200 || $height > 1200) {
									ini_set('memory_limit', '96M');
								}

								$new_image = new Image($image['tmp_name']);
								$new_image->resize($width, $height);
								$new_image->save($tar_path.$file);
							}
						
							$this->request->post['images'][] = $img_path.$file;
						}
							
						unlink($image['tmp_name']);
						
					} else {
						$error = $this->language->get('error_upload');
					}
				}
				
				ini_set('memory_limit', $memory_limit);
			}
		}
		
		if($error) {
			$this->request->post['error'] = $error;
		}
		
		return $this->load->controller('catalog/review/'.($review_id ? 'edit' : 'add'));
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/uni_reviews')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}