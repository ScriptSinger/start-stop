<?php
class ModelExtensionModuleUniReviews extends Model
{
    public function getAllReviews($start = 0, $limit = 12, $page = 1) {
		$lang_id = (int)$this->config->get('config_language_id');
		$store_id = (int)$this->config->get('config_store_id');
		
		$cache_name = 'unishop.reviews.all.'.(int)$page.'.'.$lang_id.'.'.$store_id;
		
		$reviews_data = $this->cache->get($cache_name);

		if(!$reviews_data) {
			$sql = "SELECT r.review_id, r.author, r.rating, r.text, p.product_id, pd.name, p.price, p.image, r.date_added FROM ".DB_PREFIX."review r LEFT JOIN `".DB_PREFIX."product` p ON (r.product_id = p.product_id) LEFT JOIN `".DB_PREFIX."product_description` pd ON (p.product_id = pd.product_id) LEFT JOIN `".DB_PREFIX."product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE p2s.store_id = '".$store_id."' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '".$lang_id."' ORDER BY r.date_added DESC";
			
			if ($start < 0) {
				$start = 0;
			}

			if ($limit < 1) {
				$limit = 12;
			}
	
			$sql .= " LIMIT ".(int)$start.", ".(int)$limit;
			
			$query = $this->db->query($sql);
			
			$reviews_data = $query->rows;
			
			$this->cache->set($cache_name, $reviews_data);
		}
		
		return $reviews_data;
    }

    public function getTotalReviews() {
		$lang_id = (int)$this->config->get('config_language_id');
		$store_id = (int)$this->config->get('config_store_id');
		
		$cache_name = 'unishop.reviews.total'.$lang_id.'.'.$store_id;
		
		$reviews_data = $this->cache->get($cache_name);

		if(!$reviews_data) {
			$query = $this->db->query("SELECT COUNT(*) AS total FROM `".DB_PREFIX."review` r LEFT JOIN `".DB_PREFIX."product` p ON (r.product_id = p.product_id) LEFT JOIN `".DB_PREFIX."product_to_store` p2s ON (p.product_id = p2s.product_id) WHERE p2s.store_id = '".$store_id."' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1'");
			$reviews_data = $query->row['total'];
			$this->cache->set($cache_name, $reviews_data);
		}
		
		return $reviews_data;
    }

    public function getLatestReviews($limit = 5, $category_id = 0) {
		$lang_id = (int)$this->config->get('config_language_id');
		$store_id = (int)$this->config->get('config_store_id');
		
		$cache_name = 'unishop.reviews.latest.'.(int)$category_id.'.'.$lang_id.'.'.$store_id;
		
		$reviews_data = $this->cache->get($cache_name);
		
		if(!$reviews_data) {
			$reviews_data = $this->getReviews($limit, $category_id, false);
			$this->cache->set($cache_name, $reviews_data);
		}
		
		return $reviews_data;
    }

    public function getRandomReviews($limit = 5, $category_id = 0) {
		$lang_id = (int)$this->config->get('config_language_id');
		$store_id = (int)$this->config->get('config_store_id');
		
		$cache_name = 'unishop.reviews.random.'.(int)$category_id.'.'.$lang_id.'.'.$store_id;
		
        $reviews_data = $this->cache->get($cache_name);

		if(!$reviews_data) {
			$reviews_data = $this->getReviews($limit, $category_id, true);
			$this->cache->set($cache_name, $reviews_data);
		}
		
		return $reviews_data;
    }

    private function getReviews($limit, $category_id, $random) {
		$lang_id = (int)$this->config->get('config_language_id');
		$store_id = (int)$this->config->get('config_store_id');
	
		$sql = "SELECT DISTINCT r.author, r.rating, r.text, r.date_added, p.product_id, pd.name, p.price, p.image FROM ".DB_PREFIX."review r LEFT JOIN ".DB_PREFIX."product p ON (r.product_id = p.product_id) LEFT JOIN ".DB_PREFIX."product_description pd ON (p.product_id = pd.product_id) LEFT JOIN ".DB_PREFIX."product_to_store p2s ON (p.product_id = p2s.product_id)";
		$sql .= $category_id != 0 ? " LEFT JOIN ".DB_PREFIX."product_to_category p2c ON (p.product_id = p2c.product_id)" : "";
		$sql .= " WHERE p2s.store_id = '" .$store_id. "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '".$lang_id."' ";
		$sql .= $category_id != 0 ? " AND p2c.category_id = '".(int)$category_id."'" : "";
        $sql .= $random ? " ORDER BY RAND() " : " ORDER BY date_added DESC";
        $sql .= " LIMIT ".(int)$limit;

		$query = $this->db->query($sql);

        return $query->rows;
    }
	
	public function getReviewById($review_id) {
		$query = $this->db->query("SELECT * FROM `".DB_PREFIX."review` WHERE review_id = '".(int)$review_id."' LIMIT 1");
		
		return $query->row;
	}
	
	public function getMostPopularReviewbyProductId($product_id) {
		$query = $this->db->query("SELECT * FROM `".DB_PREFIX."review` WHERE product_id = '".(int)$product_id."' AND votes_plus > 0 ORDER BY votes_plus DESC LIMIT 1");
		
		return $query->row;
	}
	
	public function setReviewsVotes($review_id, $vote) {
		if($vote == 'plus' || $vote == 'minus') {
			$sql = "UPDATE `".DB_PREFIX."review` SET";
		
			if($vote == 'plus') {
				$sql .= " votes_plus = (votes_plus + 1)";
			} else if ($vote == 'minus') {
				$sql .= " votes_minus = (votes_minus + 1)";
			}
		
			$sql .= " WHERE review_id = '".(int)$review_id."'";
		
			$this->db->query($sql);
		}
	}
	
	public function getAllReviewImages($product_id) {
		$query = $this->db->query("SELECT images FROM `".DB_PREFIX."review` WHERE product_id = '".(int)$product_id."' AND status = '1'");
		
		$result = [];
		
		if($query->rows) {
			$images = array_column($query->rows, 'images');
			
			if($images) {
				foreach($images as $image){
					$imgs = json_decode($image, true);
					
					if($imgs && is_array($imgs)) {
						foreach($imgs as $img) {
							$result[] = $img;
						}
					}
				}
			}
		}
		
		return $result;
	}
	
	public function checkBuyer($product_id, $customer_id) {
		$implode = [];

		$order_statuses = $this->config->get('config_complete_status');

		foreach ($order_statuses as $order_status_id) {
			$implode[] = "o.order_status_id = '".(int)$order_status_id."'";
		}
		
        $query = $this->db->query("SELECT product_id FROM `".DB_PREFIX."order_product` op LEFT JOIN `".DB_PREFIX."order` o ON (op.order_id = o.order_id) WHERE op.product_id = '".(int)$product_id."' AND o.customer_id = '".(int)$customer_id."' AND (".implode(" OR ", $implode).")");
		
        return $query->row ? true : false;
    }
}
?>