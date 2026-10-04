<?php

class ModelExtensionModuleFilterAuto extends Model {

    public function getBrands($category_id = null)
    {
        $joinCategory = '';
        $whereCategory = '';

        if ($category_id) {
            $joinCategory = "JOIN " . DB_PREFIX . "product_to_category ptc ON ptc.product_id = alp.part_id";
            $whereCategory = " AND ptc.category_id = '" . (int) $category_id . "'";
        }

        $sql = "SELECT DISTINCT ab.brand_id, ab.brand_name, ab.brand_img 
                FROM " . DB_PREFIX . "auto_brands ab 
                    JOIN " . DB_PREFIX . "auto_models am ON (am.brand_id = ab.brand_id) AND am.active = 1 
                    JOIN " . DB_PREFIX . "auto_modifications amf ON (amf.model_id = am.model_id) AND amf.active = 1
                    JOIN " . DB_PREFIX . "auto_link_part alp ON alp.modif_id = amf.modif_id
                    " . $joinCategory . "
                WHERE ab.active = 1 
                    AND ab.brand_name != 'Universal'
                    " . $whereCategory . " 
                ORDER BY ab.brand_name";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getAllBrands()
    {
        $sql = "SELECT * 
            FROM " . DB_PREFIX . "auto_brands 
            WHERE active = 1 
                AND brand_name != 'Universal'
            ORDER BY brand_name";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getModels($brand_id, $category_id = null)
    {
        $where = '';

        if ($category_id) {
            $where .= " AND ptc.category_id = '" . (int) $category_id . "'";
        }

        if ($brand_id) {
            $where .= " AND am.brand_id = '" . (int) $brand_id . "'";
        }

        $sql = "SELECT DISTINCT am.model_id, am.model_name 
                FROM " . DB_PREFIX . "auto_models am
                JOIN " . DB_PREFIX . "auto_modifications amf ON (amf.model_id = am.model_id) AND amf.active = 1
                JOIN " . DB_PREFIX . "auto_link_part alp ON alp.modif_id = amf.modif_id
                JOIN " . DB_PREFIX . "product_to_category ptc ON ptc.product_id = alp.part_id
                WHERE am.active = 1 
                    " . $where . "
                ORDER BY am.model_name";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getModelsBrand($brand_id)
    {
        $sql = "SELECT * 
            FROM " . DB_PREFIX . "auto_models 
            WHERE brand_id = '" . (int) $brand_id . "' AND 
                active = 1";

        $query = $this->db->query($sql);

        $data = [];

        foreach ($query->rows as $row) {
            $data[] = [
                'model_id' => $row['model_id'],
                'model_name' => $row['model_name'],
                'model_img' => $row['model_img']
            ];
        }

        return $data;
    }

    public function getModifies(array $filter)
    {
        $where = " AND amf.model_id = '" . (int) $filter['model_id'] . "'";

        if (!empty($filter['category_id'])) {
            $where .= " AND ptc.category_id = '" . (int) $filter['category_id'] . "'";
        }

        if (!empty($filter['year'])) {
            $where .= " AND (amf.modif_start <= " . (int) $filter['year'] . " 
                AND amf.modif_end >= " . (int) $filter['year'] . ")";
        }

        $sql = "SELECT DISTINCT amf.*  
                FROM " . DB_PREFIX . "auto_modifications amf
                JOIN " . DB_PREFIX . "auto_link_part alp ON alp.modif_id = amf.modif_id
                JOIN " . DB_PREFIX . "product_to_category ptc ON ptc.product_id = alp.part_id
                WHERE amf.active = 1 
                    " . $where . "
                ORDER BY amf.modif_name";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getYears(int $model_id, int $category_id = null): array
    {
        $where = " AND amf.model_id = '" . $model_id . "'";

        if ($category_id) {
            $where .= " AND ptc.category_id = '" . $category_id . "'";
        }

        $sql = "SELECT am.model_start, am.model_end
                FROM " . DB_PREFIX . "auto_models am
                    JOIN " . DB_PREFIX . "auto_modifications amf ON (amf.model_id = am.model_id) AND amf.active = 1
                    JOIN " . DB_PREFIX . "auto_link_part alp ON alp.modif_id = amf.modif_id
                    JOIN " . DB_PREFIX . "product_to_category ptc ON ptc.product_id = alp.part_id
                WHERE am.active = 1
                        " . $where . "
                GROUP BY am.model_id
                LIMIT 1";

        $query = $this->db->query($sql);

        $model_start = $query->row['model_start'];
        $model_end = $query->row['model_end'];

        if (!$model_start) {
            return [];
        }

        $model_end = ($model_end) ?: date('Y');

        return range($model_start, $model_end);
    }

    public function getBrandInfo($brand_id)
    {
        $sql = "SELECT ab.*
                FROM " . DB_PREFIX . "auto_brands ab
                WHERE ab.brand_id = '" . (int) $brand_id . "'";

        $query = $this->db->query($sql);

        return $query->row;
    }

    public function getModelInfo($model_id)
    {
        $sql = "SELECT am.*, ab.*
                FROM " . DB_PREFIX . "auto_models am 
                LEFT JOIN " . DB_PREFIX . "auto_brands ab ON ab.brand_id = am.brand_id 
                WHERE am.model_id = '" . (int) $model_id . "'";

        $query = $this->db->query($sql);

        return $query->row;
    }

    public function getModifyInfo($modify_id)
    {
        $sql = "SELECT amf.*, am.*, ab.*
                FROM " . DB_PREFIX . "auto_modifications amf 
                LEFT JOIN " . DB_PREFIX . "auto_models am ON am.model_id = amf.model_id 
                LEFT JOIN " . DB_PREFIX . "auto_brands ab ON ab.brand_id = am.brand_id 
                WHERE amf.modif_id = '" . (int) $modify_id . "'";

        $query = $this->db->query($sql);

        return $query->row;
    }

    public function getCategories($modif_id) {
        $sql = "SELECT DISTINCT c.category_id, c.parent_id, cd.name, c.image 
                FROM " . DB_PREFIX . "auto_modifications amf
                    JOIN " . DB_PREFIX . "auto_link_part alp ON alp.modif_id = amf.modif_id
                    JOIN " . DB_PREFIX . "product_to_category ptc ON ptc.product_id = alp.part_id
                    LEFT JOIN " . DB_PREFIX . "category c ON ptc.category_id = c.category_id
                    LEFT JOIN " . DB_PREFIX . "category_description cd ON c.category_id = cd.category_id
                    LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON c.category_id = c2s.category_id 
                WHERE amf.modif_id = '" . (int) $modif_id . "'
                    AND cd.language_id = '" . (int) $this->config->get('config_language_id') . "' 
                    AND c2s.store_id = '" . (int) $this->config->get('config_store_id') . "' 
                    AND c.status = '1' 
                ORDER BY c.sort_order, LCASE(cd.name)";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getCategoriesModel($model_id) {
        $sql = "SELECT DISTINCT c.category_id, c.parent_id, cd.name, c.image 
                FROM " . DB_PREFIX . "auto_models am 
                    JOIN " . DB_PREFIX . "auto_modifications amf ON am.model_id = amf.model_id
                    JOIN " . DB_PREFIX . "auto_link_part alp ON alp.modif_id = amf.modif_id
                    JOIN " . DB_PREFIX . "product_to_category ptc ON ptc.product_id = alp.part_id
                    LEFT JOIN " . DB_PREFIX . "category c ON ptc.category_id = c.category_id
                    LEFT JOIN " . DB_PREFIX . "category_description cd ON c.category_id = cd.category_id
                    LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON c.category_id = c2s.category_id 
                WHERE am.model_id = '" . (int) $model_id . "'
                    AND cd.language_id = '" . (int) $this->config->get('config_language_id') . "' 
                    AND c2s.store_id = '" . (int) $this->config->get('config_store_id') . "' 
                    AND c.status = '1' 
                ORDER BY c.sort_order, LCASE(cd.name)";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getCategoriesBrand($brand_id)
    {
        $sql = "SELECT DISTINCT c.category_id, c.parent_id, cd.name, c.image 
                FROM " . DB_PREFIX . "auto_brands ab
                    JOIN " . DB_PREFIX . "auto_models am ON ab.brand_id = am.brand_id
                    JOIN " . DB_PREFIX . "auto_modifications amf ON am.model_id = amf.model_id
                    JOIN " . DB_PREFIX . "auto_link_part alp ON alp.modif_id = amf.modif_id
                    JOIN " . DB_PREFIX . "product_to_category ptc ON ptc.product_id = alp.part_id
                    LEFT JOIN " . DB_PREFIX . "category c ON ptc.category_id = c.category_id
                    LEFT JOIN " . DB_PREFIX . "category_description cd ON c.category_id = cd.category_id
                    LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON c.category_id = c2s.category_id 
                WHERE ab.brand_id = '" . (int) $brand_id . "'
                    AND cd.language_id = '" . (int) $this->config->get('config_language_id') . "' 
                    AND c2s.store_id = '" . (int) $this->config->get('config_store_id') . "' 
                    AND c.status = '1' 
                ORDER BY c.sort_order, LCASE(cd.name)";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalProducts($data = array()) {
        $sql = "SELECT COUNT(DISTINCT p.product_id) AS total";

        $sql .= " FROM " . DB_PREFIX . "product p";

        if (!empty($data['filter_auto'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "auto_link_part alp ON (alp.part_id = p.product_id)";
        } elseif (!empty($data['filter_model'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "auto_link_part alp ON (alp.part_id = p.product_id) 
                    LEFT JOIN " . DB_PREFIX . "auto_modifications mf ON (alp.modif_id = mf.modif_id)";
        } elseif (!empty($data['filter_brand'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "auto_link_part alp ON (alp.part_id = p.product_id) 
                    LEFT JOIN " . DB_PREFIX . "auto_modifications mf ON (alp.modif_id = mf.modif_id) 
                    LEFT JOIN " . DB_PREFIX . "auto_models am ON (mf.model_id = am.model_id)";
        }

        $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) 
                  LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) 
                WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
                    AND p.status = '1' 
                    AND p.date_available <= NOW() 
                    AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

        if (!empty($data['filter_auto'])) {
            $sql .= " AND alp.modif_id = '" . (int)$data['filter_auto'] . "'";
        } elseif (!empty($data['filter_model'])) {
            $sql .= " AND mf.model_id = '" . (int)$data['filter_model'] . "'";
        } elseif (!empty($data['filter_brand'])) {
            $sql .= " AND am.brand_id = '" . (int) $data['filter_brand'] . "'";
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getProducts($data = array()) {
        $sql = "SELECT p.product_id, 
                    (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, 
                    (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, 
                    (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special";


        $sql .= " FROM " . DB_PREFIX . "product p";

        if (!empty($data['filter_auto'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "auto_link_part alp ON (alp.part_id = p.product_id)";
        } elseif (!empty($data['filter_model'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "auto_link_part alp ON (alp.part_id = p.product_id) 
                    LEFT JOIN " . DB_PREFIX . "auto_modifications mf ON (alp.modif_id = mf.modif_id)";
        } elseif (!empty($data['filter_brand'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "auto_link_part alp ON (alp.part_id = p.product_id) 
                    LEFT JOIN " . DB_PREFIX . "auto_modifications mf ON (alp.modif_id = mf.modif_id) 
                    LEFT JOIN " . DB_PREFIX . "auto_models am ON (mf.model_id = am.model_id)";
        }

        $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) 
                  LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) 
                WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
                    AND p.status = '1' 
                    AND p.date_available <= NOW() 
                    AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";


        if (!empty($data['filter_auto'])) {
            $sql .= " AND alp.modif_id = '" . (int)$data['filter_auto'] . "'";
        } elseif (!empty($data['filter_model'])) {
            $sql .= " AND mf.model_id = '" . (int)$data['filter_model'] . "'";
        } elseif (!empty($data['filter_brand'])) {
            $sql .= " AND am.brand_id = '" . (int) $data['filter_brand'] . "'";
        }

        $sql .= " GROUP BY p.product_id";

        $sort_data = array(
            'pd.name',
            'p.model',
            'p.quantity',
            'p.price',
            'rating',
            'p.sort_order',
            'p.date_added'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
                $sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
            } elseif ($data['sort'] == 'p.price') {
                $sql .= " ORDER BY (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
            } else {
                $sql .= " ORDER BY " . $data['sort'];
            }
        } else {
            $sql .= " ORDER BY p.sort_order";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC, LCASE(pd.name) DESC";
        } else {
            $sql .= " ASC, LCASE(pd.name) ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $product_data = array();

        $query = $this->db->query($sql);

        $this->load->model('catalog/product');

        foreach ($query->rows as $result) {
            $product_data[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
        }

        return $product_data;
    }

    public function getApplicability($product_id)
    {
        $sql = "SELECT CONCAT_WS(' ', ab.brand_name, am.model_name) AS full_name, ab.brand_name, amf.*
                FROM " . DB_PREFIX . "auto_link_part alp 
                    LEFT JOIN " . DB_PREFIX . "auto_modifications amf ON amf.modif_id = alp.modif_id 
                    LEFT JOIN " . DB_PREFIX . "auto_models am ON am.model_id = amf.model_id 
                    LEFT JOIN " . DB_PREFIX . "auto_brands ab ON ab.brand_id = am.brand_id 
                WHERE alp.part_id = '" . (int) $product_id . "'
                    AND amf.active = 1
                    AND am.active = 1
                    AND ab.active = 1
                ORDER BY full_name";

        $query = $this->db->query($sql);

        $data = [];

        foreach ($query->rows as $row) {
            $data[$row['brand_name']][] = $row;
        }

        return $data;
    }

    public function getSeoPage($category_id, $model_id)
    {
        $sql = "SELECT sd.description, sd.meta_title, sd.meta_description, sd.meta_keyword, sd.html_h1 
                FROM " . DB_PREFIX . "auto_seo s 
                    LEFT JOIN " . DB_PREFIX . "auto_seo_description sd ON s.seo_id = sd.seo_id    
                WHERE s.category_id = '" . (int) $category_id . "' 
                    AND s.model_id = '" . (int) $model_id . "' 
                    AND sd.language_id = '" . (int) $this->config->get('config_language_id') . "'";

        $query = $this->db->query($sql);

        return $query->row;
    }

    public function getManufacturers($data)
    {
        $sql = "SELECT DISTINCT m.manufacturer_id, m.name, m.image 
            FROM ". DB_PREFIX . "product_to_category p2c 
                LEFT JOIN " . DB_PREFIX . "product p ON p2c.product_id = p.product_id 
                LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) ";

        if (isset($data['filter_modif']) && !empty($data['filter_modif'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "auto_link_part alp ON p.product_id = alp.part_id";
        } elseif (isset($data['filter_model']) && !empty($data['filter_model'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "auto_link_part alp ON p.product_id = alp.part_id
                LEFT JOIN " . DB_PREFIX . "auto_modifications amf ON alp.modif_id = amf.modif_id";
        }

        $sql .= " JOIN " . DB_PREFIX . "manufacturer m ON p.manufacturer_id = m.manufacturer_id";

        $sql .= " WHERE p2c.category_id = '" . (int) $data['filter_category_id'] . "' AND 
            p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

        if (isset($data['filter_modif']) && !empty($data['filter_modif'])) {
            $sql .= " AND alp.modif_id = '" . (int) $data['filter_modif'] . "'";
        } elseif (isset($data['filter_model']) && !empty($data['filter_model'])) {
            $sql .= " AND amf.model_id = '" . (int) $data['filter_model'] . "'";
        }

        $sql .= " GROUP BY p.product_id";

        $sql .= " ORDER BY m.sort_order";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getManufacturersTotal($data)
    {
        $sql = "SELECT COUNT(p.manufacturer_id) AS total, p.manufacturer_id";

        if (isset($data['filter_modif']) && !empty($data['filter_modif'])) {
            $sql .= " FROM " . DB_PREFIX . "auto_link_part alp 
                        JOIN " . DB_PREFIX . "product p ON alp.part_id = p.product_id";
        } elseif (isset($data['filter_model']) && !empty($data['filter_model'])) {
            $sql .= " FROM (SELECT part_id 
                        FROM  " . DB_PREFIX . "auto_modifications amf1 
                            JOIN " . DB_PREFIX . "auto_link_part alp1 ON amf1.modif_id = alp1.modif_id 
                        WHERE amf1.model_id = '" . (int) $data['filter_model'] . "' 
                        GROUP BY alp1.part_id
                    ) as alp 
                        JOIN " . DB_PREFIX . "product p ON alp.part_id = p.product_id";
        } else {
            $sql .= " FROM " . DB_PREFIX . "product p";
        }

        $sql .= " JOIN " . DB_PREFIX . "product_to_category p2c ON p.product_id = p2c.product_id 
                JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) ";

        if (!empty($data['filter_attribute'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "filter_atr_product fap ON (p.product_id = fap.product_id)";
        }

        $sql .= " WHERE p2c.category_id = '" . (int) $data['filter_category_id'] . "' AND 
            p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

        if (isset($data['filter_modif']) && !empty($data['filter_modif'])) {
            $sql .= " AND alp.modif_id = '" . (int) $data['filter_modif'] . "'";
        }

        if (!empty($data['filter_attribute'])) {
            $attributes = $this->explodeInt($data['filter_attribute']);

            $sql .= " AND fap.filter_atr_value_id IN (" . implode(',', $attributes) . ")";
        }
        
        $sql .= " GROUP BY p.manufacturer_id";

        $query = $this->db->query($sql);

        $result = [];

        foreach ($query->rows as $row) {
            $result[$row['manufacturer_id']] = $row['total'];
        }

        return $result;
    }

    public function getAttributes($data)
    {
        $sql = "SELECT DISTINCT a.attribute_id, ad.name AS atr_name, fav.filter_atr_value_id, fav.value 
            FROM " . DB_PREFIX . "product_to_category p2c 
                LEFT JOIN " . DB_PREFIX . "product p ON p2c.product_id = p.product_id 
                LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) 
                JOIN " . DB_PREFIX . "filter_atr_product fap ON (p.product_id = fap.product_id) 
                JOIN " . DB_PREFIX . "filter_atr_value fav ON (fap.filter_atr_value_id = fav.filter_atr_value_id) 
                    AND fav.language_id = '" . (int) $this->config->get('config_language_id') . "' 
                LEFT JOIN " . DB_PREFIX . "attribute a ON (fav.attribute_id = a.attribute_id) 
                LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) 
                    AND ad.language_id = '" . (int) $this->config->get('config_language_id') . "'";

        if (isset($data['filter_modif']) && !empty($data['filter_modif'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "auto_link_part alp ON p.product_id = alp.part_id";
        } elseif (isset($data['filter_model']) && !empty($data['filter_model'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "auto_link_part alp ON p.product_id = alp.part_id
                LEFT JOIN " . DB_PREFIX . "auto_modifications amf ON alp.modif_id = amf.modif_id";
        }

        $sql .= " WHERE p2c.category_id = '" . (int) $data['filter_category_id'] . "' AND 
            p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

        if (isset($data['filter_modif']) && !empty($data['filter_modif'])) {
            $sql .= " AND alp.modif_id = '" . (int) $data['filter_modif'] . "'";
        } elseif (isset($data['filter_model']) && !empty($data['filter_model'])) {
            $sql .= " AND amf.model_id = '" . (int) $data['filter_model'] . "'";
        }

        if (!empty($data['filter_attr_show'])) {
           $sql .= " AND a.attribute_id IN (" . implode(',', $this->arrayInt($data['filter_attr_show'])) . ")";
        }

        $sql .= " ORDER BY a.sort_order";

        $query = $this->db->query($sql);

        $result = [];
        $neighborAtr = [];

        $total = $this->getAttributesTotal($data);

        if (!empty($data['filter_attribute'])) {
            $attributes = $this->explodeInt($data['filter_attribute']);

            $neighborAtr = $this->neighborAtr($attributes, $data);
        }

        foreach ($query->rows as $row) {
            $result[$row['attribute_id']]['atr_name'] = $row['atr_name'];

            if (!empty($neighborAtr) && key_exists($row['filter_atr_value_id'], $neighborAtr)) {
                $total_atr = $neighborAtr[$row['filter_atr_value_id']];
            } else {
                $total_atr = (key_exists($row['filter_atr_value_id'], $total)) ? $total[$row['filter_atr_value_id']] : 0;
            }

            $result[$row['attribute_id']]['values'][] = [
                'atr_value_id' => $row['filter_atr_value_id'],
                'atr_value' => $row['value'],
                'total' => $total_atr
            ];
        }

        return $result;
    }

    public function getAttributesTotal($data)
    {
        $sql = "SELECT COUNT(fap.product_id) AS total, fap.filter_atr_value_id";

        if (isset($data['filter_modif']) && !empty($data['filter_modif'])) {
            $sql .= " FROM " . DB_PREFIX . "auto_link_part alp 
                        JOIN " . DB_PREFIX . "product p ON alp.part_id = p.product_id";
        } elseif (isset($data['filter_model']) && !empty($data['filter_model'])) {
            $sql .= " FROM (SELECT part_id 
                        FROM  " . DB_PREFIX . "auto_modifications amf1 
                            JOIN " . DB_PREFIX . "auto_link_part alp1 ON amf1.modif_id = alp1.modif_id 
                        WHERE amf1.model_id = '" . (int) $data['filter_model'] . "' 
                        GROUP BY alp1.part_id
                    ) as alp 
                        JOIN " . DB_PREFIX . "product p ON alp.part_id = p.product_id";
        } else {
            $sql .= " FROM " . DB_PREFIX . "product p";
        }

        $sql .= " JOIN " . DB_PREFIX . "product_to_category p2c ON p.product_id = p2c.product_id 
                LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) 
                LEFT JOIN " . DB_PREFIX . "filter_atr_product fap ON (p.product_id = fap.product_id) 
                LEFT JOIN " . DB_PREFIX . "filter_atr_value fav ON (fap.filter_atr_value_id = fav.filter_atr_value_id)
    AND fav.language_id = '" . (int) $this->config->get('config_language_id') . "'";

        if (!empty($data['filter_manufacturer'])) {
            $sql .= " JOIN " . DB_PREFIX . "manufacturer m ON p.manufacturer_id = m.manufacturer_id";
        }

        $sql .= " WHERE p2c.category_id = '" . (int) $data['filter_category_id'] . "' AND 
            p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

        if (isset($data['filter_modif']) && !empty($data['filter_modif'])) {
            $sql .= " AND alp.modif_id = '" . (int) $data['filter_modif'] . "'";
        }

        if (!empty($data['filter_manufacturer'])) {
            $manufacturers = $this->explodeInt($data['filter_manufacturer']);

            $sql .= " AND p.manufacturer_id IN (" . implode(',', $manufacturers) . ")";
        }

        if (!empty($data['filter_attribute'])) {
            $attributes = $this->explodeInt($data['filter_attribute']);

            $sql .= " AND fap.product_id IN (
                SELECT product_id FROM " . DB_PREFIX . "filter_atr_product 
                    WHERE filter_atr_value_id IN (" . implode(',', $attributes) . ") 
            )";
        }

        if (!empty($data['filter_attr_show'])) {
            $sql .= " AND fav.attribute_id IN (" . implode(',', $this->arrayInt($data['filter_attr_show'])) . ")";
        }

        $sql .= " GROUP BY fap.filter_atr_value_id";

        $query = $this->db->query($sql);

        $result = [];

        foreach ($query->rows as $row) {

            $result[$row['filter_atr_value_id']] = $row['total'];
        }

        return $result;
    }

    private function neighborAtr(array $attributes, $data)
    {
        if (count($attributes) > 1) {
            return;
        }

        $sql = "SELECT COUNT(DISTINCT fap2.product_id) as total, fav2.filter_atr_value_id
                FROM " . DB_PREFIX . "filter_atr_value fav1
                    JOIN " . DB_PREFIX . "filter_atr_value fav2 ON (fav1.attribute_id = fav2.attribute_id)
                        AND fav2.language_id = '" . (int) $this->config->get('config_language_id') . "'
                    JOIN " . DB_PREFIX . "filter_atr_product fap2 ON (fav2.filter_atr_value_id = fap2.filter_atr_value_id)";

        if (isset($data['filter_modif']) && !empty($data['filter_modif'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "auto_link_part alp ON fap2.product_id = alp.part_id";
        } elseif (isset($data['filter_model']) && !empty($data['filter_model'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "auto_link_part alp ON fap2.product_id = alp.part_id
                LEFT JOIN " . DB_PREFIX . "auto_modifications amf ON alp.modif_id = amf.modif_id";
        }


        $sql .= " WHERE fav1.filter_atr_value_id = '" . (int) $attributes[0] . "'
                    AND fav2.filter_atr_value_id != '" . (int) $attributes[0] . "'";

        if (isset($data['filter_modif']) && !empty($data['filter_modif'])) {
            $sql .= " AND alp.modif_id = '" . (int) $data['filter_modif'] . "'";
        } elseif (isset($data['filter_model']) && !empty($data['filter_model'])) {
            $sql .= " AND amf.model_id = '" . (int) $data['filter_model'] . "'";
        }

        $sql .= " GROUP BY fav2.filter_atr_value_id";

        $query = $this->db->query($sql);

        $result = [];

        foreach ($query->rows as $row) {
            $result[$row['filter_atr_value_id']] = $row['total'];
        }

        return $result;
    }

    private function explodeInt($data)
    {
        $arr = explode(',', $data);

        return $this->arrayInt($arr);
    }

    private function arrayInt(array $data)
    {
        return array_map(function($val) {
            return (int) $val;
        }, $data);
    }
}