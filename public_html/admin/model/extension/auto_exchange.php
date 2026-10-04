<?php

class ModelExtensionAutoExchange extends Model {

    /**
     * Product fileds identity
     *
     * @var array
     */
    protected $fileds = [
        'sku',
        'model'
    ];

    /**
     * Export catalogue
     *
     * @param $field string
     * @return array
     */
    public function export($field)
    {
        $fields = ['sku', 'model'];
        $sql_field = (in_array($field, $fields)) ? $field : 'sku';

        $sql = "SELECT b.brand_name, m.model_name, mf.modif_name, p." . $sql_field . "
            FROM " . DB_PREFIX . "auto_brands b 
                LEFT JOIN " . DB_PREFIX . "auto_models m ON m.brand_id = b.brand_id 
                LEFT JOIN " . DB_PREFIX . "auto_modifications mf ON mf.model_id = m.model_id 
                JOIN " . DB_PREFIX . "auto_link_part alp ON alp.modif_id = mf.modif_id 
                LEFT JOIN " . DB_PREFIX . "product p ON p.product_id = alp.part_id 
            WHERE b.active = 1 
                AND m.active = 1 
                AND mf.active = 1 
            ORDER BY b.brand_name";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    /**
     * Import catalogue
     *
     * @param $data array
     * @param $field_product string
     * @return boolean
     */
    public function import(array $data, $field_product)
    {
        $product_id = $this->getProductId($data[3], $field_product);
        $brand_id = $this->getbrandId($data[0]);
        $model_id = $this->getModelId($brand_id, $data[1]);
        $modif_id = $this->getModificationId($model_id, $data[2]);

        if (!$product_id) {
            return;
        }

        return $this->getLinks($product_id, $modif_id);
    }

    /**
     * Get product
     *
     * @param $data string
     * @param $field_product string
     * @return int
     */
    protected function getProductId($data, $field_product) {
        if (!in_array($field_product, $this->fileds)) {
            return;
        }

        $query = $this->db->query("SELECT product_id 
            FROM " . DB_PREFIX . "product 
            WHERE `" . $field_product . "` = '" . $this->db->escape($data) . "' 
            LIMIT 1");

        if (empty($query->row)) {
            return;
        }

        return $query->row['product_id'];
    }

    /**
     * Get brand id
     *
     * @param $brand_name string
     * @return int
     */
    protected function getbrandId($brand_name)
    {
        $query = $this->db->query("
            SELECT brand_id FROM " . DB_PREFIX . "auto_brands WHERE brand_name = '" . $this->db->escape($brand_name) . "'
        ");

        if (empty($query->row)) {
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "auto_brands (brand_name, active) VALUES ('" . $this->db->escape($brand_name) . "', 1)
            ");

            $brand_id = (int) $this->db->getLastId();
        } else {
            $brand_id = (int) $query->row['brand_id'];
        }

        return $brand_id;
    }

    /**
     * Get model id
     *
     * @param $brand_id int
     * @param $model_name string
     * @return int
     */
    protected function getModelId($brand_id, $model_name)
    {
        $query = $this->db->query("
            SELECT model_id FROM " . DB_PREFIX . "auto_models WHERE brand_id = '" . (int) $brand_id . "' AND model_name = '" . $this->db->escape($model_name) . "'
        ");

        if (empty($query->row)) {
            $this->db->query(
                "INSERT INTO " . DB_PREFIX . "auto_models (brand_id, model_name, model_start, model_end, active) VALUES ('" . (int) $brand_id . "', '" . $this->db->escape($model_name) . "', 0, 0, 1)"
            );

            $model_id = (int) $this->db->getLastId();
        } else {
            $model_id = (int) $query->row['model_id'];
        }

        return $model_id;
    }

    /**
     * Get modification id
     *
     * @param $model_id int
     * @param $modif_name string
     * @return int
     */
    protected function getModificationId($model_id, $modif_name)
    {
        $query = $this->db->query("
            SELECT modif_id FROM " . DB_PREFIX . "auto_modifications WHERE model_id = '" . (int) $model_id . "' AND modif_name = '" . $this->db->escape($modif_name) . "'
        ");

        if (empty($query->row)) {
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "auto_modifications 
                    (model_id, modif_name, active) 
                VALUES ('" . (int) $model_id . "', '" . $this->db->escape($modif_name) . "', 1)    
            ");

            $modif_id = (int) $this->db->getLastId();
        } else {
            $modif_id = (int) $query->row['modif_id'];
        }

        return $modif_id;
    }

    /**
     * Get link product to modification
     *
     * @param $product_id
     * @param $modif_id
     * @return mixed
     */
    protected function getLinks($product_id, $modif_id)
    {
        $query = $this->db->query("SELECT part_id FROM " . DB_PREFIX . "auto_link_part 
            WHERE part_id = '" . (int) $product_id . "' AND modif_id = '" . (int) $modif_id . "'");

        $link = $query->row;

        if (empty($link)) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "auto_link_part (modif_id, part_id) 
                VALUES ('" . (int) $modif_id . "', '" . (int) $product_id . "')");

            return true;
        }

        return;
    }
}