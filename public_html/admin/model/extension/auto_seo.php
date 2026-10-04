<?php

class ModelExtensionAutoSeo extends Model {

    public function getSeoPage($seo_id)
    {
        $data = [];

        $sql = "SELECT s.seo_id, s.category_id, s.model_id, sd.language_id, sd.description, sd.meta_title, sd.meta_description, sd.meta_keyword, sd.html_h1
                FROM " . DB_PREFIX . "auto_seo s 
                    LEFT JOIN " . DB_PREFIX . "auto_seo_description sd ON s.seo_id = sd.seo_id    
                WHERE s.seo_id = '" . (int) $seo_id . "'";
        $query = $this->db->query($sql);

        foreach ($query->rows as $result) {
            $data['category'] = $result['category_id'];
            $data['model'] = $result['model_id'];

            $data['description'][$result['language_id']] = [
                'category' => $result['category_id'],
                'model' => $result['model_id'],
                'description' => $result['description'],
                'meta_title' => $result['meta_title'],
                'meta_description' => $result['meta_description'],
                'meta_keyword' => $result['meta_keyword'],
                'html_h1' => $result['html_h1']
            ];
        }

        return $data;
    }

    public function getTotalSeoPages($data = array())
    {
        $sql = "SELECT COUNT(*) AS total 
                FROM " . DB_PREFIX . "auto_seo 
                WHERE 1=1";

        if (!empty($data['filter_category'])) {
            $sql .= " AND category_id = '" . (int) $data['filter_category'] . "'";
        }

        if (!empty($data['filter_model'])) {
            $sql .= " AND model_id = '" . (int) $data['filter_model'] . "'";
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getSeoPages($data)
    {
        $sql = "SELECT s.seo_id, s.category_id, s.model_id, cd.name, sd.meta_title, m.model_name 
                FROM " . DB_PREFIX . "auto_seo s 
                    LEFT JOIN " . DB_PREFIX . "auto_seo_description sd ON s.seo_id = sd.seo_id
                    LEFT JOIN " . DB_PREFIX . "category_description cd ON s.category_id = cd.category_id 
                        AND cd.language_id = " . (int) $this->config->get('config_language_id') . "
                    LEFT JOIN " .DB_PREFIX . "auto_models m ON s.model_id = m.model_id     
                WHERE sd.language_id = '" . (int) $this->config->get('config_language_id') . "'";

        if (!empty($data['filter_category'])) {
            $sql .= " AND s.category_id = '" . (int) $data['filter_category'] . "'";
        }

        if (!empty($data['filter_model'])) {
            $sql .= " AND s.model_id = '" . (int) $data['filter_model'] . "'";
        }

        $sort_data = array(
            'cd.name',
            'sd.meta_title',
            'm.model_name'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY s.seo_id";
        }

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

    public function addSeoPage($data)
    {
        $sql = "INSERT INTO " . DB_PREFIX . "auto_seo 
                    SET category_id = '" . (int) $data['category'] . "', model_id = '" . (int) $data['model'] . "'";

        $this->db->query($sql);

        $seo_id = $this->db->getLastId();

        foreach ($data['description'] as $language_id => $value) {
            $sql = "INSERT INTO " . DB_PREFIX . "auto_seo_description 
                        SET 
                            seo_id = '" . (int) $seo_id . "', 
                            language_id = '" . (int) $language_id . "', 
                            description = '" . $this->db->escape($value['description']) . "', 
                            meta_title = '" . $this->db->escape($value['meta_title']) . "', 
                            meta_description = '" . $this->db->escape($value['meta_description']) . "', 
                            meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', 
                            html_h1 = '" . $this->db->escape($value['html_h1']) . "'";
            $this->db->query($sql);
        }
    }

    public function editSeoPage($seo_id, $data)
    {
        $sql = "UPDATE " . DB_PREFIX . "auto_seo 
                    SET category_id = '" . (int) $data['category'] . "', model_id = '" . (int) $data['model'] . "' 
                WHERE seo_id = '" . (int) $seo_id . "'";

        $this->db->query($sql);

        $this->db->query("DELETE FROM " . DB_PREFIX . "auto_seo_description WHERE seo_id = '" . (int) $seo_id . "'");

        foreach ($data['description'] as $language_id => $value) {
            $sql = "INSERT INTO " . DB_PREFIX . "auto_seo_description 
                        SET 
                            seo_id = '" . (int) $seo_id . "', 
                            language_id = '" . (int) $language_id . "', 
                            description = '" . $this->db->escape($value['description']) . "', 
                            meta_title = '" . $this->db->escape($value['meta_title']) . "', 
                            meta_description = '" . $this->db->escape($value['meta_description']) . "', 
                            meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', 
                            html_h1 = '" . $this->db->escape($value['html_h1']) . "'";
            $this->db->query($sql);
        }
    }

    public function deleteSeoPage($seo_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "auto_seo WHERE seo_id = '" . (int) $seo_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "auto_seo_description WHERE seo_id = '" . (int) $seo_id . "'");
    }
}