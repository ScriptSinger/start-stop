<?php
class ControllerProductUniReviews extends Controller {
    public function index() {
		$uniset = $this->config->get('config_unishop2');
		$lang_id = (int)$this->config->get('config_language_id');
		
		if($this->config->get('config_review_status')) {
			$this->load->language('extension/module/uni_othertext');
			$this->load->language('product/uni_reviews');
		
			$this->load->model('catalog/product');
			$this->load->model('extension/module/uni_reviews');
			$this->load->model('tool/image');
		
			$this->document->addStyle('catalog/view/theme/unishop2/stylesheet/reviews.css');
			
			$data['shop_name'] = $this->config->get('config_name');
		
			$route = isset($this->request->get['route']) ? $this->request->get['route'] : '';
			$menu_schema = isset($uniset['menu_schema']) ? $uniset['menu_schema'] : [];
			$data['menu_expanded'] = ($uniset['menu_type'] == 1 && in_array($route, $menu_schema)) ? true : false;
			
			$data['route'] = isset($this->request->get['route']) ? $this->request->get['route'] : ''; 
			$data['menu_schema'] = isset($uniset['menu_schema']) ? $uniset['menu_schema'] : [];
		
			$data['show_grid_button'] = isset($uniset['show_grid_button']) ? true : false;
			$data['show_list_button'] = isset($uniset['show_list_button']) ? true : false;
			$data['show_compact_button'] = isset($uniset['show_compact_button']) ? true : false;
		
			if(isset($uniset['catalog']['limit']['status'])) {
				$new_limit = explode(',', $uniset['catalog']['limit']['value']);
				$limit = $new_limit[0] ? (int)$new_limit[0] : $limit;
	
				$this->config->set('theme_'.$this->config->get('config_theme').'_product_limit', $limit);
			}

			if (isset($this->request->get['page'])) {
				$page = (int)$this->request->get['page'];
			} else {
				$page = 1;
			}

			if (isset($this->request->get['limit'])) {
				$limit = (int)$this->request->get['limit'];
			} else {
				$limit = $this->config->get('theme_'.$this->config->get('config_theme').'_product_limit');
			}
		
			$data['limit'] = $limit;

			$this->document->setTitle($this->language->get('page_heading_title'));

			$data['breadcrumbs'] = [];

			$data['breadcrumbs'][] = [
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home')
			];

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = [
				'text'      => $this->language->get('page_heading_title'),
				'href'      => $this->url->link('product/uni_reviews', $url)
			];

			$data['heading_title'] = $this->language->get('page_heading_title');

			$data['text_empty'] = $this->language->get('text_empty');

			$data['reviews'] = [];

			$reviews_total = $this->model_extension_module_uni_reviews->getTotalReviews();

			$results = $this->model_extension_module_uni_reviews->getAllReviews(($page - 1) * $limit, $limit, $page);

			foreach ($results as $result) {
				if ($this->config->get('config_review_status')) {
					$rating = $result['rating'];
				} else {
					$rating = false;
				}

				if ($result['product_id']) {
					$product = $this->model_catalog_product->getProduct($result['product_id']);
				
					$image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_'.$this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_'.$this->config->get('config_theme') . '_image_product_height'));

					$data['reviews'][] = [
						'review_id'   => $result['review_id'],
						'rating'      => $rating,
						'rating_new'  => $result['rating'],
						'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['text'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
						'author'      => $result['author'],
						'product_id'  => $product['product_id'],
						'prod_thumb'  => $image,
						'prod_name'   => $product['name'],
						'prod_model'  => $product['model'],
						'prod_href'   => $this->url->link('product/product', 'product_id=' . $product['product_id']),
					];
				}
			}
		
			$data['limits'] = [];

			$limits = array_unique(array($this->config->get('theme_'.$this->config->get('config_theme').'_product_limit'), 25, 50, 75, 100));
		
			if(isset($uniset['catalog']['limit']['status'])) {
				$new_limits = array_unique(explode(',', $uniset['catalog']['limit']['value']));

				$limits = $new_limits ? $new_limits : $limits;
			}

			sort($limits);

			foreach($limits as $value) {
				$data['limits'][] = array(
					'text'  => $value,
					'value' => $value,
					'href'  => $this->url->link('product/uni_reviews', '&limit='.$value)
				);
			}

			$url = '';

			if (isset($this->request->get['limit'])) {
				$url .= '&limit='.(int)$this->request->get['limit'];
			}

			$pagination = new Pagination();
			$pagination->total = $reviews_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('product/uni_reviews', '&page={page}');

			$data['pagination'] = $pagination->render();

			$data['results'] = sprintf($this->language->get('text_pagination'), ($reviews_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($reviews_total - $limit)) ? $reviews_total : ((($page - 1) * $limit) + $limit), $reviews_total, ceil($reviews_total / $limit));

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('/product/uni_reviews', $data));
		} else {
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
    }
	
	public function getReviews($filter = [0, 5, 5]) {
		
		if(!$this->config->get('config_review_status')) {
			return ['render' => '', 'microdata' => ''];
		}
		
		$product_id = (int)$filter[0];
		$start = (int)$filter[1];
		$limit = (int)$filter[2];
		
		$uniset = $this->config->get('config_unishop2');
		
		$settings = isset($uniset['product']['review']) ? $uniset['product']['review'] : [];
		
		$data['product_id'] = $product_id;
		$data['start'] = $start + $limit;
		$data['limit'] = $limit;
		
		$this->load->language('product/product');
		$this->load->language('extension/module/uni_othertext');
		$this->load->language('product/uni_reviews');
		
		$this->load->model('catalog/review');
		$this->load->model('extension/module/uni_reviews');
		
		$data['review_image_status'] = isset($settings['photo']['status']) ? true : false;
		
		$data['review_images'] = [];
		$data['all_review_images'] = '';
		
		if($start == 0 && $limit == 5) {
			if(isset($settings['photo']['status']) &&  isset($settings['photo']['gallery']['status'])) {
				$this->load->model('tool/image');
				$theme = $this->config->get('config_theme');
			
				$all_review_images = $this->model_extension_module_uni_reviews->getAllReviewImages($product_id);
			
				if($all_review_images) {
					$review_images = [];
					
					foreach($all_review_images as $image) {
						$review_images[] = [
							'thumb' => $this->model_tool_image->resize($image, $this->config->get('theme_' . $theme . '_image_additional_width'), $this->config->get('theme_' . $theme . '_image_additional_height')),
							'popup' => $this->model_tool_image->resize($image, $this->config->get('theme_' . $theme . '_image_popup_width'), $this->config->get('theme_' . $theme . '_image_popup_height'))
						];
					}
					
					$limit_photo = 40;

					$data['review_images'] = array_slice($review_images, 0, $limit_photo);
					$data['all_review_images'] = base64_encode(json_encode($review_images));
					
					$data['show_all_review_images'] = count($all_review_images) > $limit_photo ? true : false;
				}
			}
		}
		
		$data['votes_status'] = $votes_status = isset($settings['votes']['status']) ? true : false;
			
		$data['reviews'] = $microdata = [];
		
		$reviews = $this->model_catalog_review->getReviewsByProductId((int)$product_id, (int)$start, (int)$limit);
			
		if($reviews) {
			$reviews_total = $this->model_catalog_review->getTotalReviewsByProductId((int)$product_id);
			
			$data['review_show_more'] = $data['start'] < $reviews_total ? true : false;
			
			foreach ($reviews as $result) {
				$data['reviews'][$result['review_id']] = $this->getReviewData($result, 0);
				
				$microdata[] = [
					'author'      => $result['author'],
					'text'        => trim(str_replace(["\r\n", "\r", "\n", '"', '&nbsp;'], ' ',  $result['text'])),
					'rating'      => (int)$result['rating'],
					'date_added'  => date('Y-m-d', strtotime($result['date_added'])),
				];
			}
			
			if($votes_status && $reviews_total >= 3) {
				$popular_review = $this->model_extension_module_uni_reviews->getMostPopularReviewbyProductId((int)$product_id);
					
				if($popular_review) {
					$popular_review_id = $popular_review['review_id'];
					
					if(isset($data['reviews'][$popular_review_id])) {
						unset($data['reviews'][$popular_review_id]);
					}
					
					if($start == 0) {
						array_unshift($data['reviews'], $this->getReviewData($popular_review, 1));
					}
				}
			}
		}

		return [
			'render' => $this->load->view('product/review', $data),
			'microdata'	 => $microdata
		];
	}
	
	private function getReviewData($data, $status) {
		$uniset = $this->config->get('config_unishop2');
		$settings = isset($uniset['product']['review']) ? $uniset['product']['review'] : [];
		
		$images = [];
		
		if(isset($settings['photo']['status']) && $data['images']) {
			$this->load->model('tool/image');
			
			$theme = $this->config->get('config_theme');
			
			$images_arr = json_decode($data['images'], true);
			
			if($images_arr && is_array($images_arr)) {
				foreach($images_arr as $image) {
					$images[] = [
						'thumb' => $this->model_tool_image->resize($image, $this->config->get('theme_' . $theme . '_image_additional_width'), $this->config->get('theme_' . $theme . '_image_additional_height')),
						'popup' => $this->model_tool_image->resize($image, $this->config->get('theme_' . $theme . '_image_popup_width'), $this->config->get('theme_' . $theme . '_image_popup_height'))
					];
				}
			}
		}
		
		$real_buyer = isset($settings['show_buyer_title']) ? (int)$data['real_buyer'] : '';
		
		$review = [
			'review_id'	  => $data['review_id'],
			'author'      => $data['author'],
			'text'        => nl2br($data['text']),
			'plus'     	  => nl2br($data['plus']),
			'minus'       => nl2br($data['minus']),
			'admin_reply' => nl2br($data['admin_reply']),
			'rating'      => (int)$data['rating'],
			'date_added'  => date($this->language->get('date_format_short'), strtotime($data['date_added'])),
			'votes_plus'  => $data['votes_plus'] ? $data['votes_plus'] : '',
			'votes_minus' => $data['votes_minus'] ? $data['votes_minus'] : '',
			'images'	  => $images,
			'most_popular'=> $status,
			'real_buyer'  => $real_buyer
		];

		return $review;
	}
	
	public function getReviewsRender() {
		if(!$this->return404()) {
			$product_id = isset($this->request->get['pid']) ? (int)$this->request->get['pid'] : 0;
			$start = isset($this->request->get['start']) ? (int)$this->request->get['start'] : 0;
			$limit = isset($this->request->get['limit']) ? (int)$this->request->get['limit'] : 0;

			if($product_id && $start && $limit) {
				$result = $this->getReviews([$product_id, $start, $limit]);

				$this->response->setOutput($result['render']);
			}
		}
	}
	
	public function setReviewsVotes() {

		$json = [];
		
		if(!$this->return404()) {
			$this->load->language('product/uni_reviews');
		
			$uniset = $this->config->get('config_unishop2');
			$settings = isset($uniset['product']['review']) ? $uniset['product']['review'] : [];
		
			$votes_status = isset($settings['votes']['status']) ? true : false;
			$votes_guest = isset($settings['votes']['guest']) ? true : false;
		
			if($votes_status && ($votes_guest || $this->customer->isLogged())) {
				if(isset($this->request->post['id']) && isset($this->request->post['vote'])) {
			
					$review_id = (int)$this->request->post['id'];
					$vote = $this->request->post['vote'];
			
					if(!isset($this->request->cookie['voted_reviews_'.$review_id])) {
						setcookie('voted_reviews_'.$review_id, $review_id, strtotime('+1 day'), '/');
				
						$this->load->model('extension/module/uni_reviews');
				
						$this->model_extension_module_uni_reviews->setReviewsVotes($review_id, $vote);
				
						$json['success'] = $this->language->get('text_votes_send');
					} else {
						$json['error'] = $this->language->get('text_votes_error');
					}
				}
			} else {
				$json['error'] = $this->language->get('text_votes_error_guest');
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}
	
	public function newDataForReview($json) {
		$product_id = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;
		
		if(!$this->return404() && $product_id) {
			$this->load->language('extension/module/uni_othertext');
			$this->load->language('product/uni_reviews');
			
			$uniset = $this->config->get('config_unishop2');
			$settings = isset($uniset['product']['review']) ? $uniset['product']['review'] : [];
			
			if(isset($settings['plus_minus']['status'])) {
				if(!isset($this->request->post['plus'])) {
					$this->request->post['plus'] = '';
				}
				
				if(!isset($this->request->post['minus'])) {
					$this->request->post['minus'] = '';
				}
				
				if(isset($settings['plus_minus']['required'])) {
					if ((utf8_strlen($this->request->post['plus']) < 3) || (utf8_strlen($this->request->post['plus']) > 1000)) {
						$json['error'] = $this->language->get('error_plus');
					}
					if ((utf8_strlen($this->request->post['minus']) < 3) || (utf8_strlen($this->request->post['minus']) > 1000)) {
						$json['error'] = $this->language->get('error_minus');
					}
				}
			} else {
				$this->request->post['plus'] = $this->request->post['minus'] = '';
			}
			
			$this->request->post['real_buyer'] = $real_buyer = 0;
		
			if($this->customer->isLogged()) {
				$this->load->model('extension/module/uni_reviews');
			
				$customer_id = $this->customer->getId();
			
				$real_buyer = $this->model_extension_module_uni_reviews->checkBuyer($product_id, $customer_id);
			
				if($real_buyer) {
					$this->request->post['real_buyer'] = 1;
				}
			}
		
			if(isset($settings['check_buyer']) && !$real_buyer) {
				$json['error'] = $this->language->get('error_check_buyer');
			}
		
			if ($this->config->get('config_account_id')) {
				$this->load->model('catalog/information');

				$information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));

				if ($information_info && !isset($this->request->post['agree'])) {
					$json['error'] = sprintf($this->language->get('error_agree'), $information_info['title']);
				}
			}
		
			$this->request->post['images'] = [];
		
			if(isset($settings['photo']['status']) && !isset($json['error']) && !empty($this->request->files['images'])) {
			
				$images = [];
			
				foreach($this->request->files['images'] as $key => $array) {
					foreach ($array as $key2 => $value) {
						$images[$key2][$key] = $value;
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
					$new_res = 1600;
				
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
								$json['error'] = sprintf($this->language->get('error_filetype'), $key+1);
							}

							$img_info = getimagesize($image['tmp_name']);
							
							if($img_info && is_array($img_info)) {
								
								$width = $img_info[0];
								$height = $img_info[1];
								$type = $img_info[2];
								$bits = $img_info['bits'];
								$mime = $img_info['mime'];
								
								if(!in_array($type, $allowed_img_types)) {
									$json['error'] = sprintf($this->language->get('error_filetype'), $key+1);
								}
								
								if(!in_array($mime, $allowed_mime_types)) {
									$json['error'] = sprintf($this->language->get('error_filetype'), $key+1);
								}

								if (($width < $min_res || $width > $max_res) || ($height < $min_res || $height > $max_res)) {
									$json['error'] = sprintf($this->language->get('error_resolution'), $key+1);
								}
							} else {
								$json['error'] = sprintf($this->language->get('error_filetype'), $key+1);
							}

							$content = file_get_contents($image['tmp_name']);

							if(preg_match('/\<\?php/i', $content)) {
								$json['error'] = sprintf($this->language->get('error_filetype'), $key+1);
							}
						
							if (filesize($image['tmp_name']) > $max_size) {
								$json['error'] = sprintf($this->language->get('error_filesize'), $key+1);
							}
							
							if($image['error'] != UPLOAD_ERR_OK) {
								$json['error'] = $this->language->get('error_upload_' . $image['error']);
							}
							
							if(!isset($json['error'])) {
								$chars = 'qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP'; 
								$name_length = 8; 
								$chars_length = mb_strlen($chars)-1; 
					
								$img_name = ''; 

								while($name_length--) {
									$img_name .= $chars[rand(0, $chars_length)];
								}
						
								$file = $img_name.'.'.$extension;
							
								if($width > $new_res) {
									$width = $new_res;
									$height = $new_res / ($width / $height);
								} else if($height > $new_res) {
									$width = $new_res * ($width / $height);
									$height = $new_res;
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
							$json['error'] = $this->language->get('error_upload');
						}
					}
					
					ini_set('memory_limit', $memory_limit);
				}
			}
		} else {
			$json['error'] = 'Error';
		}
		
		return $json;
	}
	
	private function return404() {
		if (isset($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			return false;
		} else {
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
			
			return true;
		}
	}
}