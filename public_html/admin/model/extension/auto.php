<?php

class ModelExtensionAuto extends Model {

    protected $languages = [];

    protected $stores = [];

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->initStoreConfig();
    }

    public function getUniversal()
    {
        $sql = "SELECT ab.brand_id, ab.brand_name, am.model_id, am.model_name, amf.modif_id, amf.modif_name 
                FROM " . DB_PREFIX . "auto_brands ab
                    JOIN " . DB_PREFIX . "auto_models am ON ab.brand_id = am.brand_id 
                    JOIN " . DB_PREFIX . "auto_modifications amf ON am.model_id = amf.model_id
                WHERE ab.brand_name = 'Universal' 
                    AND am.model_name = 'Universal' 
                    AND amf.modif_name = 'Universal'";

        $query = $this->db->query($sql);

        return $query->row;
    }

    public function getBrands()
    {
        $sql = "SELECT * 
            FROM " . DB_PREFIX . "auto_brands
            ORDER BY brand_name";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getBrand($brand_id)
    {
        $sql = "SELECT * 
            FROM " . DB_PREFIX . "auto_brands 
            WHERE brand_id = '" . (int) $brand_id . "'";

        $query = $this->db->query($sql);

        return $query->row;
    }

    public function deleteBrands($brand_id)
    {
        $this->deleteBrandAlias($brand_id);

        $this->db->query("DELETE FROM " . DB_PREFIX . "auto_brands WHERE brand_id = '" . (int) $brand_id . "'");
    }

    public function addBrand($data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "auto_brands 
            SET brand_name = '" . $this->db->escape($data['brand']['brand_name']) . "', 
                active = '" . (int) $data['status'] . "', 
                brand_img = '" . $this->db->escape($data['image']) . "' 
            ");

        $insert_id = $this->db->getLastId();        

        $this->addBrandSlug($insert_id);        

        return $insert_id;
    }

    public function editBrand($brand_id, $data)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "auto_brands 
            SET brand_name = '" . $this->db->escape($data['brand']['brand_name']) . "', 
                active = '" . (int) $data['status'] . "', 
                brand_img = '" . $this->db->escape($data['image']) . "'
            WHERE brand_id = " . (int) $brand_id);
    }

    public function getModels($brand_id)
    {
        $sql = "SELECT * 
            FROM " . DB_PREFIX . "auto_models 
            WHERE brand_id = '" . (int) $brand_id . "'
            ORDER BY model_name";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getModel($model_id)
    {
        $sql = "SELECT * 
            FROM " . DB_PREFIX . "auto_models 
            WHERE model_id = '" . (int) $model_id . "'";

        $query = $this->db->query($sql);
        $result = $query->row;

        $data = [];

        if (!empty($result)) {
            $data = [
                'model_id' => $result['model_id'],
                'brand_id' => $result['brand_id'],
                'model_name' => $result['model_name'],
                'model_start' => $result['model_start'],
                'model_end' => $result['model_end'] ?: 0,
                'model_img' => $result['model_img'],
                'active' => $result['active']
            ];
        }

        return $data;
    }

    public function getModelsAutoCmpl($data = array())
    {
        $sql = "SELECT am.model_id, am.model_name, ab.brand_name 
            FROM " . DB_PREFIX . "auto_models am 
            LEFT JOIN " . DB_PREFIX . "auto_brands ab 
                ON am.brand_id = ab.brand_id
            WHERE 1=1";

        if (!empty($data['filter_name'])) {
            $sql .= " AND am.model_name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sql .= " ORDER BY am.model_name";

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
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

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function addModel($data)
    {
        $sql = "INSERT INTO " . DB_PREFIX . "auto_models SET 
                brand_id = '" . (int) $data['brand_id'] . "', 
                model_name = '" . $this->db->escape($data['model_name']) . "', 
                model_start = '" . $this->db->escape($data['model_start']) . "', 
                model_end = '" . $this->db->escape($data['model_end'] ?: 0) . "', 
                model_img = '" . $this->db->escape($data['image']) . "',
                active = '" . (int) $data['model_status'] . "'";

        $this->db->query($sql);            
        $insert_id = $this->db->getLastId();

        $this->db->query("INSERT INTO " . DB_PREFIX . "auto_modifications 
            (model_id, modif_name, modif_dvs, modif_hp, modif_start, modif_end, modif_body, active) 
            VALUES ('" . (int) $insert_id . "', 'all', '-', '-', '-', '-', '-', 1)");

        $this->addModelSlug($insert_id);        

        return $insert_id;
    }

    public function editModel($model_id, $data)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "auto_models SET 
                model_name = '" . $this->db->escape($data['model']['model_name']) . "', 
                model_start = '" . $this->db->escape($data['model']['model_start']) . "', 
                model_end = '" . $this->db->escape($data['model']['model_end'] ? : 0) . "',
                model_img = '" . $this->db->escape($data['image']) . "',  
                active = '" . (int) $data['status'] . "'
            WHERE model_id = '" . (int) $model_id . "'");
    }

    public function deleteModel($model_id)
    {
        $this->deleteModelAlias($model_id);

        $this->db->query("DELETE FROM " . DB_PREFIX . "auto_models WHERE model_id = '" . (int) $model_id . "'");
    }

     public function getModifies($model_id)
    {
        $sql = "SELECT * 
            FROM " . DB_PREFIX . "auto_modifications 
            WHERE model_id = '" . (int) $model_id . "'
            ORDER BY (modif_name = 'all') DESC, modif_name";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getModify($modif_id)
    {
        $sql = "SELECT * 
            FROM " . DB_PREFIX . "auto_modifications 
            WHERE modif_id = '" . (int) $modif_id . "'";

        $query = $this->db->query($sql);
        $result = $query->row;

        $data = [
            'modif_id' =>  $result['modif_id'],
            'model_id' => $result['model_id'],
            'modif_name' => $result['modif_name'],
            'modif_dvs' => $result['modif_dvs'],
            'modif_hp' => $result['modif_hp'],
            'modif_body' => $result['modif_body'],
            'modif_start' => $result['modif_start'],
            'modif_end' => $result['modif_end'] ? $result['modif_end'] : 0,
            'active' => $result['active']
        ];

        return $data;
    }

    public function addModify($data)
    {
        $sql = "INSERT INTO " . DB_PREFIX . "auto_modifications SET 
                model_id = '" . (int) $data['model_id'] . "', 
                modif_name = '" . $this->db->escape($data['modif_name']) . "', 
                modif_dvs = '" . $this->db->escape($data['modif_dvs']) . "',
                modif_hp = '" . $this->db->escape($data['modif_hp']) . "',
                modif_body = '" . $this->db->escape($data['modif_body']) . "',
                modif_start = '" . $this->db->escape($data['modif_start']) . "', 
                modif_end = '" . $this->db->escape($data['modif_end'] ? $data['modif_end'] : 0) . "', 
                active = '" . (int) $data['modif_status'] . "'";

        $this->db->query($sql);
        $insert_id = $this->db->getLastId();

        return $insert_id;
    }

    public function editModify($modif_id, $data)
    {
        $sql = "UPDATE " . DB_PREFIX . "auto_modifications SET  
                modif_name = '" . $this->db->escape($data['modif']['modif_name']) . "', 
                modif_dvs = '" . $this->db->escape($data['modif']['modif_dvs']) . "',
                modif_hp = '" . $this->db->escape($data['modif']['modif_hp']) . "',
                modif_body = '" . $this->db->escape($data['modif']['modif_body']) . "',
                modif_start = '" . $this->db->escape($data['modif']['modif_start']) . "', 
                modif_end = '" . $this->db->escape($data['modif']['modif_end'] ? $data['modif']['modif_end'] : 0) . "', 
                active = '" . (int) $data['status'] . "'
                WHERE modif_id = '" . (int) $modif_id . "'";

        $this->db->query($sql);
    }

    public function deleteModify($modif_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "auto_modifications WHERE modif_id = '" . (int) $modif_id . "'");

        $this->deleteModifAlias($modif_id);
    }

    public function getLinks($part_id)
    {
        $sql = "SELECT amf.*, amd.model_id, amd.model_name, ab.brand_id, ab.brand_name 
            FROM " . DB_PREFIX . "auto_link_part alp 
            LEFT JOIN " . DB_PREFIX . "auto_modifications amf ON amf.modif_id = alp.modif_id
            LEFT JOIN " . DB_PREFIX . "auto_models amd ON amd.model_id = amf.model_id 
            LEFT JOIN " . DB_PREFIX . "auto_brands ab ON ab.brand_id = amd.brand_id 
            WHERE alp.part_id = '" . (int) $part_id . "'
            ORDER BY ab.brand_name";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function addLinks($product_id, $modif_array)
    {
        foreach($modif_array as $modif) {
            $insert[] = "('" . (int) $modif . "', '" . (int) $product_id .  "')";
        }

        $sql = "INSERT INTO " . DB_PREFIX . "auto_link_part (modif_id, part_id) VALUES " . rtrim(implode(',', $insert), ',');

        $this->db->query($sql);
    }

    public function getModelsInfo($model_array)
    {
        $data = array_map(function ($elem) {
            return (int) $elem;
        }, $model_array);

        $sql = "SELECT amd.*, ab.brand_id, ab.brand_name 
            FROM " . DB_PREFIX . "auto_models amd
            LEFT JOIN " . DB_PREFIX . "auto_brands ab ON ab.brand_id = amd.brand_id 
            WHERE amd.model_id IN (" . implode(",", $data) . ")
            ORDER BY ab.brand_name, amd.model_name";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getModifiesInfo($modif_array)
    {
        $data = array_map(function ($elem) {
            return (int) $elem;
        }, $modif_array);

        $sql = "SELECT amf.*, amd.model_id, amd.model_name, ab.brand_id, ab.brand_name 
            FROM " . DB_PREFIX . "auto_modifications amf
            LEFT JOIN " . DB_PREFIX . "auto_models amd ON amd.model_id = amf.model_id 
            LEFT JOIN " . DB_PREFIX . "auto_brands ab ON ab.brand_id = amd.brand_id 
            WHERE amf.modif_id IN (" . implode(",", $data) . ")
            ORDER BY ab.brand_name, amd.model_name, amf.modif_name";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function delLinks($product_id, $modif_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "auto_link_part 
            WHERE modif_id = '" . (int) $modif_id . "' AND part_id = '" . (int) $product_id . "'");
    }

    public function noSlugBrandCount() 
    {
        $sql = "SELECT COUNT(DISTINCT b.brand_id) as brand
                FROM " . DB_PREFIX . "auto_brands b
                JOIN " . DB_PREFIX . "auto_models md ON b.brand_id = md.brand_id";
        $query = $this->db->query($sql);
        $total = (int) $query->row['brand'];

        $sql = "SELECT COUNT(*) AS brand
                FROM " . DB_PREFIX . "seo_url
                WHERE query LIKE 'auto_brand=%' 
                    AND language_id = '" . (int) $this->config->get('config_language_id') . "'
                    AND store_id = 0";
        $query = $this->db->query($sql);
        $totalSlug = (int) $query->row['brand'];

        return (($total - $totalSlug) > 0) ? 1 : 0;
    }

    public function noSlugModelCount()
    {
        $sql = "SELECT COUNT(DISTINCT md.model_id) as model
                FROM " . DB_PREFIX . "auto_models md
                JOIN " . DB_PREFIX . "auto_modifications mf ON md.model_id = mf.model_id";
        $query = $this->db->query($sql);
        $total = (int) $query->row['model'];

        $sql = "SELECT COUNT(*) AS model
                    FROM " . DB_PREFIX . "seo_url
                    WHERE query LIKE 'auto_model=%'
                        AND language_id = '" . (int) $this->config->get('config_language_id') . "'
                        AND store_id = 0";
        $query = $this->db->query($sql);
        $totalSlug = (int) $query->row['model'];

        return (($total - $totalSlug) > 0) ? 1 : 0;
    }

    public function noSlugModifCount()
    {
        $sql = "SELECT COUNT(*) as modif
                FROM " . DB_PREFIX . "auto_modifications mf
                JOIN " . DB_PREFIX . "auto_link_part alp ON mf.modif_id = alp.modif_id
                WHERE mf.modif_id NOT IN (
                    SELECT SUBSTRING(`query` FROM 13) 
                    FROM " . DB_PREFIX . "seo_url
                    WHERE query LIKE 'auto_filter=%'
                )";

        $query = $this->db->query($sql);

        return (int) $query->row['modif'];
    }

    public function noSlugModule()
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'extension/module/model_auto' 
                    AND language_id = '" . (int) $this->config->get('config_language_id') . "'
                    AND store_id = 0";

        $query = $this->db->query($sql);

        return ($query->num_rows) ? 0 : 1;
    }

    public function noSlugModel($start, $limit)
    {
        $sql = "SELECT DISTINCT m.model_id, LOWER(CONCAT_WS('-', 'model', b.brand_name, m.model_name)) AS slug
                FROM " . DB_PREFIX . "auto_models m
                JOIN " . DB_PREFIX . "auto_brands b ON b.brand_id = m.brand_id
                LIMIT " . (int) $start . ", " . (int) $limit;

        $query = $this->db->query($sql);

        $slugs = [];

        foreach ($query->rows as $row) {
            $slugs[$row['model_id']] = $row['slug'];
        }

        return $slugs;
    }

    public function noSlugModif()
    {
        $sql = "SELECT LOWER(CONCAT_WS('-', b.brand_name, m.model_name, mf.modif_name, LEFT( mf.modif_start, 4 ), mf.modif_body)) AS slug, mf.modif_id
                FROM " . DB_PREFIX . "auto_modifications mf
                JOIN " . DB_PREFIX . "auto_link_part alp ON mf.modif_id = alp.modif_id
                JOIN " . DB_PREFIX . "auto_models m ON m.model_id = mf.model_id
                JOIN " . DB_PREFIX . "auto_brands b ON b.brand_id = m.brand_id
                WHERE mf.modif_id NOT IN (
                    SELECT SUBSTRING(`query` FROM 13) 
                    FROM " . DB_PREFIX . "seo_url
                    WHERE query LIKE 'auto_filter=%'
                )";

        $query = $this->db->query($sql);

        $slugs = [];

        foreach ($query->rows as $row) {
            $slugs[$row['modif_id']] = $row['slug'];
        }

        return $slugs;
    }

    public function noSlugBrand()
    {
        $sql = "SELECT DISTINCT LOWER(CONCAT_WS('-', 'brand', b.brand_name)) AS slug, b.brand_id
                FROM " . DB_PREFIX . "auto_brands b
                JOIN " . DB_PREFIX . "auto_models m ON b.brand_id = m.brand_id
                JOIN " . DB_PREFIX . "auto_modifications mf ON m.model_id = mf.model_id
                JOIN " . DB_PREFIX . "auto_link_part alp ON mf.modif_id = alp.modif_id
                WHERE b.brand_id NOT IN (
                    SELECT SUBSTRING(`query` FROM 12) 
                    FROM " . DB_PREFIX . "seo_url
                    WHERE query LIKE 'auto_brand=%'
                        AND language_id = '" . (int) $this->config->get('config_language_id') . "'
                        AND store_id = 0
                )";

        $query = $this->db->query($sql);

        $slugs = [];

        foreach ($query->rows as $row) {
            $slugs[$row['brand_id']] = $row['slug'];
        }

        return $slugs;
    }

    public function generateModifSlug()
    {
        $insert = [];

        foreach ($this->languages as $language) {
            foreach($this->noSlugModif() as $id=>$value) {
                $insert[] = "(0, '" . (int) $language['language_id'] . "', 'auto_filter=" . (int) $id . "', '" .
                    $this->db->escape($this->url_slug($value)) . "')";
            }
        }

        if (!empty($insert)) {
            $sql = "INSERT INTO " . DB_PREFIX . "seo_url (store_id, language_id,query, keyword) VALUES "
                . implode(',', $insert);
            $this->db->query($sql);
        }
    }

    public function generateModelSlug($start, $limit)
    {
        $insert = [];

        foreach ($this->languages as $language) {
            $prefix = $this->getLanguagePrefix($language['language_id'], $language['code']);

            foreach($this->noSlugModel($start, $limit) as $id=>$value) {
                $insert[] = "(0, '" . (int) $language['language_id'] . "','auto_model=" . (int) $id . "', '" .
                    $this->db->escape($prefix . $this->url_slug($value)) . "')";
            }
        }

        if (!empty($insert)) {
            $sql = "INSERT INTO " . DB_PREFIX . "seo_url (store_id, language_id, query, keyword) VALUES " .
                implode(',', $insert);
            $this->db->query($sql);
        }
    }

    public function generateBrandSlug()
    {
        $insert = [];

        foreach ($this->languages as $language) {
            $prefix = $this->getLanguagePrefix($language['language_id'], $language['code']);

            foreach($this->noSlugBrand() as $id=>$value) {
                $insert[] = "(0, '" . (int) $language['language_id'] . "','auto_brand=" . (int) $id . "', '" .
                    $this->db->escape($prefix . $this->url_slug($value)) . "')";
            }
        }

        if (!empty($insert)) {
            $sql = "INSERT INTO " . DB_PREFIX . "seo_url (store_id, language_id, query, keyword) VALUES "
                . implode(',', $insert);
            $this->db->query($sql);
        }
    }

    public function generateModuleSlug($slug = 'model')
    {
        foreach ($this->languages as $language) {
            $prefix = $this->getLanguagePrefix($language['language_id'], $language['code']);
            $slug = $prefix . $slug;

            $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url (store_id, language_id, query, keyword) 
            VALUES (0, '" . (int) $language['language_id'] . "', 'extension/module/model_auto', '" .
                             $this->db->escape($slug) . "')");
        }
    }

    public function addBrandSlug($brand_id)
    {
        $sql = "SELECT LOWER(CONCAT_WS('-', 'brand', b.brand_name)) AS slug, b.brand_id
                FROM " . DB_PREFIX . "auto_brands b
                WHERE b.brand_id = " . (int) $brand_id;

        $query = $this->db->query($sql);

        foreach ($this->languages as $language) {
            $prefix = $this->getLanguagePrefix($language['language_id'], $language['code']);

            $this->db->query(
            "INSERT INTO " . DB_PREFIX . "seo_url (store_id, language_id, query, keyword) 
            VALUES (0, '" . (int)$language['language_id'] . "', 'auto_brand=" . (int)$brand_id . "', '"
                . $this->db->escape($prefix . $this->url_slug($query->row['slug'])) . "')"
            );
        }
    }

    public function addModelSlug($model_id)
    {
        $sql = "SELECT LOWER(CONCAT_WS('-', 'model', b.brand_name, m.model_name)) AS slug, m.model_id
                FROM " . DB_PREFIX . "auto_models m
                JOIN " . DB_PREFIX . "auto_brands b ON b.brand_id = m.brand_id
                WHERE m.model_id = " . (int) $model_id;

        $query = $this->db->query($sql);

        foreach ($this->languages as $language) {
            $prefix = $this->getLanguagePrefix($language['language_id'], $language['code']);

            $this->db->query("
            INSERT INTO " . DB_PREFIX . "seo_url (store_id, language_id, query, keyword) 
            VALUES (0, '" . (int)$language['language_id'] . "', 'auto_model=" . (int) $model_id . "', '" .
                             $this->db->escape($this->url_slug($prefix . $query->row['slug'])) . "')
        ");
        }
    }

     public function addModifySlug($modif_id)
    {
        $sql = "SELECT CONCAT_WS('-', 'modif', b.brand_name, m.model_name, mf.modif_name, LEFT( mf.modif_start, 4 ), mf.modif_body) AS slug, mf.modif_id
                FROM " . DB_PREFIX . "auto_modifications mf
                JOIN " . DB_PREFIX . "auto_models m ON m.model_id = mf.model_id
                JOIN " . DB_PREFIX . "auto_brands b ON b.brand_id = m.brand_id
                WHERE mf.modif_id = '" . (int) $modif_id . "'";

        $query = $this->db->query($sql);

        foreach ($this->languages as $language) {
            $prefix = $this->getLanguagePrefix($language['language_id'], $language['code']);

            $this->db->query("
            INSERT INTO " . DB_PREFIX . "seo_url (store_id, language_id, query, keyword) 
            VALUES (0, '" . (int) $language['language_id'] . "','auto_filter=" . (int) $modif_id . "', '" .
                             $this->db->escape($this->url_slug($prefix . $query->row['slug'])) . "')
        ");
        }
    }

    /**
     * Create a web friendly URL slug from a string.
     *
     * Although supported, transliteration is discouraged because
     *     1) most web browsers support UTF-8 characters in URLs
     *     2) transliteration causes a loss of information
     *
     * @author Sean Murphy <sean@iamseanmurphy.com>
     * @copyright Copyright 2012 Sean Murphy. All rights reserved.
     * @license http://creativecommons.org/publicdomain/zero/1.0/
     *
     * @param string $str
     * @param array $options
     * @return string
     */
    function url_slug($str, $options = array()) {

        $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());

        $defaults = array(
            'delimiter' => '-',
            'limit' => null,
            'lowercase' => true,
            'replacements' => array(),
            'transliterate' => true,
        );

        $options = array_merge($defaults, $options);

        $char_map = array(
            // Latin
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
            'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
            'ß' => 'ss',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
            'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
            'ÿ' => 'y',

            // Latin symbols
            '©' => '(c)',

            // Russian
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
            'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
            'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
            'Я' => 'Ya',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
            'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
            'я' => 'ya',

            // Ukrainian
            'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
            'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',
        );

        $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);

        if ($options['transliterate']) {
            $str = str_replace(array_keys($char_map), $char_map, $str);
        }

        $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);

        $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

        $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');

        $str = trim($str, $options['delimiter']);

        return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
    }

    public function countProductAttribute()
    {
        $query = $this->db->query("SELECT COUNT(*) aS total FROM " . DB_PREFIX . "product_attribute");

        return $query->row['total'];
    }

    public function refreshProductAttribute($start, $limit, $iteration)
    {
        $query = $this->db->query("SELECT * 
            FROM " . DB_PREFIX . "product_attribute 
            ORDER BY product_id
            LIMIT " . (int) $start . ", " . (int) $limit);

        if (count($query->rows)) {

            if ($iteration == 0) {
                $this->db->query("TRUNCATE TABLE " . DB_PREFIX . "filter_atr_value");
                $this->db->query("TRUNCATE TABLE " . DB_PREFIX . "filter_atr_product");
            }

            $insert_attr_product = [];

            foreach ($query->rows as $row) {
                $attribute = $this->attrExist($row['attribute_id'], $row['text'], $row['language_id']);

                if (empty($attribute)) {
                    $filter_atr_value_id = $this->addAttrValue($row['attribute_id'], $row['text'], $row['language_id']);
                } else {
                    $filter_atr_value_id = $attribute['filter_atr_value_id'];
                }

                $insert_attr_product[] = [
                    'filter_atr_value_id' => $filter_atr_value_id,
                    'product_id' => $row['product_id']
                ];
            }

            if (!empty($insert_attr_product)) {
                $this->addAttrProduct($insert_attr_product);
            }

            return true;
        }

        return false;
    }

    public function getAttributes()
    {
        $query = $this->db->query("SELECT CONCAT_WS(' > ', agd.name, ad.name) AS name, a.attribute_id
            FROM " . DB_PREFIX . "attribute a 
                LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) AND ad.language_id = '" . (int) $this->config->get('config_language_id') . "'
                LEFT JOIN " . DB_PREFIX . "attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id)
                LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) AND agd.language_id = '" . (int) $this->config->get('config_language_id') . "'");

        return $query->rows;
    }

    public function createTable()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "auto_link_part`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "auto_modifications`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "auto_models`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "auto_brands`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "auto_seo`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "auto_seo_description`");

        $this->db->query("CREATE TABLE `" . DB_PREFIX . "auto_brands` (
                            `brand_id` INT(11) NOT NULL AUTO_INCREMENT,
                            `brand_name` VARCHAR(255) NOT NULL,
                            `brand_img` VARCHAR(255) NOT NULL,
                            `active` TINYINT(4) NOT NULL DEFAULT '1',
                            PRIMARY KEY (`brand_id`),
                            INDEX `brand_name` (`brand_name`)
                        )
                        COLLATE='utf8_general_ci'
                        ENGINE=InnoDB;"
        );

        $this->db->query("CREATE TABLE `" . DB_PREFIX . "auto_models` (
                            `model_id` INT(10) NOT NULL AUTO_INCREMENT,
                            `brand_id` INT(10) NULL DEFAULT NULL,
                            `model_name` VARCHAR(100) NOT NULL DEFAULT '',
                            `model_start` VARCHAR(20) NULL DEFAULT NULL,
                            `model_end` VARCHAR(20) NULL DEFAULT NULL,
                            `model_img` VARCHAR(255) NULL DEFAULT NULL,
                            `active` TINYINT(4) NOT NULL DEFAULT '1',
                            PRIMARY KEY (`model_id`),
                            INDEX `parent_id` (`brand_id`)
                        )
                        COLLATE='utf8_general_ci'
                        ENGINE=InnoDB;"
        );
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "auto_models` ADD CONSTRAINT `" . DB_PREFIX . "auto_models_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `" . DB_PREFIX . "auto_brands` (`brand_id`) ON DELETE CASCADE ON UPDATE CASCADE;");


        $this->db->query("CREATE TABLE `" . DB_PREFIX . "auto_modifications` (
                            `modif_id` INT(11) NOT NULL AUTO_INCREMENT,
                            `model_id` INT(11) NOT NULL,
                            `modif_name` VARCHAR(255) NOT NULL,
                            `modif_dvs` VARCHAR(255) NULL DEFAULT NULL,
                            `modif_hp` VARCHAR(255) NULL DEFAULT NULL,
                            `modif_start` VARCHAR(255) NULL DEFAULT NULL,
                            `modif_end` VARCHAR(255) NULL DEFAULT NULL,
                            `modif_body` VARCHAR(255) NULL DEFAULT NULL,
                            `active` TINYINT(1) NOT NULL DEFAULT '1',
                            PRIMARY KEY (`modif_id`),
                            INDEX `model_id` (`model_id`),
                            INDEX `modif_name` (`modif_name`)
                        )
                        COLLATE='utf8_general_ci'
                        ENGINE=InnoDB
                        ROW_FORMAT=COMPACT"
        );
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "auto_modifications` ADD CONSTRAINT `" . DB_PREFIX . "auto_modifications_ibfk_1` FOREIGN KEY (`model_id`) REFERENCES `" . DB_PREFIX . "auto_models` (`model_id`) ON DELETE CASCADE ON UPDATE CASCADE;");

        $this->db->query("CREATE TABLE `" . DB_PREFIX . "auto_link_part` (
                            `modif_id` INT(10) NULL DEFAULT NULL,
                            `part_id` INT(10) NULL DEFAULT NULL,
                            UNIQUE INDEX `part` (`modif_id`, `part_id`),
                            INDEX `modify` (`modif_id`),
                            INDEX `part_id` (`part_id`)
                        )
                        COLLATE='utf8_general_ci'
                        ENGINE=InnoDB;"
        );
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "auto_link_part` ADD CONSTRAINT `" . DB_PREFIX . "auto_link_part_ibfk_1` FOREIGN KEY (`modif_id`) REFERENCES `" . DB_PREFIX . "auto_modifications` (`modif_id`) ON DELETE CASCADE ON UPDATE CASCADE;");

        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "filter_atr_product`");
        $this->db->query("CREATE TABLE `" . DB_PREFIX . "filter_atr_product` (
                          `product_id` int(11) NOT NULL,
                          `filter_atr_value_id` int(11) NOT NULL,
                          INDEX `product_id_IDX` (`product_id`),
                          INDEX `fatr_value_id_IDX` (`filter_atr_value_id`)
                        ) 
                        COLLATE='utf8_general_ci'
                        ENGINE=InnoDB"
        );

        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "filter_atr_value`");
        $this->db->query("CREATE TABLE `" . DB_PREFIX . "filter_atr_value` (
                          `filter_atr_value_id` int(11) NOT NULL AUTO_INCREMENT,
                          `attribute_id` int(11) NOT NULL,
                          `language_id` int(3) NOT NULL,
                          `value` varchar(255) NOT NULL,
                          PRIMARY KEY (`filter_atr_value_id`),
                          INDEX `attribute` (`attribute_id`)
                        ) 
                        COLLATE='utf8_general_ci'
                        ENGINE=InnoDB"
        );

        $this->generateModuleSlug('model');

        $this->db->query("CREATE TABLE `" . DB_PREFIX . "auto_seo` (
                          `seo_id` int(11) NOT NULL AUTO_INCREMENT,
                          `category_id` int(11) NOT NULL,
                          `model_id` int(11) NOT NULL,
                          PRIMARY KEY (`seo_id`),
                          UNIQUE KEY `seo` (`category_id`,`model_id`)
                        ) 
                        COLLATE='utf8_general_ci' 
                        ENGINE=InnoDB"
        );

        $this->db->query("CREATE TABLE `" . DB_PREFIX . "auto_seo_description` (
                          `seo_id` int(11) NOT NULL,
                          `language_id` int(11) NOT NULL,
                          `description` text NOT NULL,
                          `meta_title` varchar(255) NOT NULL,
                          `html_h1` varchar(255) NOT NULL,
                          `meta_description` varchar(255) NOT NULL,
                          `meta_keyword` varchar(255) NOT NULL,
                          PRIMARY KEY (`seo_id`,`language_id`)
                        ) 
                        COLLATE='utf8_general_ci' 
                        ENGINE=InnoDB"
        );
    }

    public function removeTable()
    {
        $this->db->query("DROP TABLE `" . DB_PREFIX . "auto_link_part`");
        $this->db->query("DROP TABLE `" . DB_PREFIX . "auto_modifications`");
        $this->db->query("DROP TABLE `" . DB_PREFIX . "auto_models`");
        $this->db->query("DROP TABLE `" . DB_PREFIX . "auto_brands`");
        $this->db->query("DROP TABLE `" . DB_PREFIX . "filter_atr_product`");
        $this->db->query("DROP TABLE `" . DB_PREFIX . "filter_atr_value`");
        $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'extension/module/model_auto'");
        $this->db->query("DROP TABLE `" . DB_PREFIX . "auto_seo`"); 
        $this->db->query("DROP TABLE `" . DB_PREFIX . "auto_seo_description`");
        $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query LIKE 'auto_filter=%'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query LIKE 'auto_model=%'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query LIKE 'auto_brand=%'");
    }

    private function deleteBrandAlias($id)
    {
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "seo_url 
            WHERE query IN (
                SELECT CONCAT('auto_filter=', mf.modif_id) 
                FROM " . DB_PREFIX . "auto_modifications mf
                    LEFT JOIN " . DB_PREFIX . "auto_models m ON (mf.model_id = m.model_id)
                WHERE m.brand_id = '" . (int) $id . "'
            )
        ");        

        $this->db->query("
            DELETE FROM " . DB_PREFIX . "seo_url 
            WHERE query IN (
                SELECT CONCAT('auto_model=', model_id) 
                FROM " . DB_PREFIX . "auto_models 
                WHERE brand_id = '" . (int) $id . "'
            )
        ");

        $this->db->query("
            DELETE FROM " . DB_PREFIX . "seo_url 
            WHERE query IN (
                SELECT CONCAT('auto_brand=', b.brand_id) 
                FROM " . DB_PREFIX . "auto_brands b
                WHERE b.brand_id = '" . (int) $id . "'
            )
        ");
    }

    private function deleteModelAlias($id)
    {
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "seo_url 
            WHERE query IN (
                SELECT CONCAT('auto_filter=', mf.modif_id) 
                FROM " . DB_PREFIX . "auto_modifications mf
                WHERE mf.model_id = '" . (int) $id . "'
            )
        ");

        $this->db->query("
            DELETE FROM " . DB_PREFIX . "seo_url 
            WHERE query IN (
                SELECT CONCAT('auto_model=', model_id) 
                FROM " . DB_PREFIX . "auto_models 
                WHERE model_id = '" . (int) $id . "'
            )
        ");
    }

    private function deleteModifAlias($id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'auto_filter=". (int) $id ."'");
    }

    private function attrExist($attribute_id, $value, $language_id)
    {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "filter_atr_value 
            WHERE attribute_id = '" . (int) $attribute_id . "' AND 
                language_id = '" . (int) $language_id . "' AND
                value = '" . $this->db->escape($value) . "' 
                
        ");

        return $query->row;
    }

    private function addAttrValue($attribute_id, $value, $language_id)
    {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "filter_atr_value 
            SET attribute_id = '" . (int) $attribute_id . "',
                language_id = '" . (int) $language_id . "',
                value = '" . $this->db->escape($value) . "'");

        return $this->db->getLastId();
    }

    private function addAttrProduct($data)
    {
        $insert = '';

        foreach ($data as $row) {
            $insert .= "('" . (int) $row['product_id'] . "', '" . (int) $row['filter_atr_value_id'] . "'),";
        }

        $this->db->query("INSERT INTO " . DB_PREFIX . "filter_atr_product 
            (product_id, filter_atr_value_id) VALUES " . rtrim($insert, ',') . "
        ");
    }

    protected function initStoreConfig()
    {
        $this->load->model('setting/store');
        $this->load->model('localisation/language');

        $this->languages = $this->model_localisation_language->getLanguages();
        $stores = $this->model_setting_store->getStores();
        
        $this->stores = (empty($stores)) ? ['store_id' => 0] : $stores;
    }

    protected function getLanguagePrefix($language_id, $code)
    {
        if ($language_id == (int) $this->config->get('config_language_id')) {
            return '';
        }

        $codeArr = explode('-', $code);

        return array_shift($codeArr) . '_';
    }
}