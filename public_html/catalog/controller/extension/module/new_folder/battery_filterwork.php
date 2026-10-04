<?php
class ControllerExtensionModuleBatteryFilter extends Controller {
    public function index() {
        $this->load->model('tool/image');
        $query = $this->db->query("SELECT DISTINCT brand FROM oc_battery_base WHERE brand != '' ORDER BY brand ASC");
        
        $popular_list = ['LADA', 'Toyota', 'Hyundai', 'Kia', 'Renault', 'Volkswagen', 'Skoda', 'Nissan', 'Ford', 'Chevrolet', 'BMW', 'Mercedes-Benz'];
        $data['popular_brands'] = [];
        $data['other_brands'] = [];

        foreach ($query->rows as $brand) {
            $brand_name = $brand['brand'];
            $brand_lower = mb_strtolower($brand_name, 'UTF-8');
            $logo_path = 'catalog/carslogo/' . $brand_lower . '.png';
            
            if (file_exists(DIR_IMAGE . $logo_path)) {
                $image = $this->model_tool_image->resize($logo_path, 100, 100);
            } else {
                $image = $this->model_tool_image->resize('catalog/carslogo/default.png', 100, 100);
            }

            $brand_info = ['name' => $brand_name, 'image' => $image];
            if (in_array($brand_name, $popular_list)) {
                $data['popular_brands'][] = $brand_info;
            } else {
                $data['other_brands'][] = $brand_info;
            }
        }
        return $this->load->view('extension/module/battery_filter', $data);
    }

    public function getModels() {
        $brand = $this->request->get['brand'] ?? '';
        $query = $this->db->query("SELECT DISTINCT model FROM oc_battery_base WHERE brand = '" . $this->db->escape($brand) . "' ORDER BY model ASC");
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($query->rows));
    }

    public function getGenerations() {
        $this->load->model('tool/image');
        $brand = $this->request->get['brand'] ?? '';
        $model = $this->request->get['model'] ?? '';
        $query = $this->db->query("SELECT DISTINCT generation FROM oc_battery_base WHERE brand = '" . $this->db->escape($brand) . "' AND model = '" . $this->db->escape($model) . "' ORDER BY generation ASC");
        
        $results = [];
        foreach ($query->rows as $row) {
            $gen_name = trim($row['generation']);
            $f_name = str_replace([' - ', ' '], '_', $gen_name);
            $f_name = str_replace(['(', ')', '.', ','], '', $f_name);
            $f_name = preg_replace('/__+/', '_', $f_name);
            $f_name_clean = trim($f_name, '_');

            $image_found = '';
            $variants = [
                'catalog/cars/' . $f_name_clean . '_.jpg',
                'catalog/cars/' . $f_name_clean . '.jpg',
                'catalog/cars/_' . $f_name_clean . '.jpg',
                'catalog/cars/_' . $f_name_clean . '_.jpg',
                'catalog/cars/' . mb_strtolower($f_name_clean, 'UTF-8') . '.jpg'
            ];

            foreach ($variants as $path) {
                if (file_exists(DIR_IMAGE . $path)) {
                    $image_found = $path;
                    break;
                }
            }

            $results[] = [
                'name'  => $gen_name ?: 'Стандарт',
                'image' => $this->model_tool_image->resize($image_found ?: 'catalog/cars/no_image.jpg', 250, 150)
            ];
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($results));
    }

    public function getResult() {
        $brand = $this->request->get['brand'] ?? '';
        $model = $this->request->get['model'] ?? '';
        $gen   = $this->request->get['gen'] ?? '';
        
        $sql = "SELECT * FROM oc_battery_base WHERE brand = '".$this->db->escape($brand)."' AND model = '".$this->db->escape($model)."'";
        if ($gen) $sql .= " AND generation = '".$this->db->escape($gen)."'";
        
        $res = $this->db->query($sql . " LIMIT 1")->row;

        if ($res) {
            $params = [];

            // 1. ПОЛЯРНОСТЬ (ID 13)
            $pol_raw = mb_strtolower(trim($res['polarity']), 'UTF-8');
            if (preg_match('/(0|l\+|обрат|лев|left)/ui', $pol_raw)) {
                $params[] = 'F13S2V1698094399'; // Обратная
            } else {
                $params[] = 'F13S2V254229664';  // Прямая
            }

            // 2. ЕМКОСТЬ (ID 15)
            $cap_ids = [];
            $caps = explode(',', $res['capacity']);
            foreach ($caps as $c) {
                $val = preg_replace('/[^0-9]/', '', $c); 
                if ($val) {
                    $vids = $this->getValIdsSmart(15, $val);
                    if ($vids) $cap_ids = array_merge($cap_ids, $vids);
                }
            }
            if ($cap_ids) $params[] = 'F15S2V' . implode('V', array_unique($cap_ids));

            // 3. ГАБАРИТЫ (ID 16)
            $dim_ids = [];
            $dims = explode(',', $res['dims']);
            foreach ($dims as $d) {
                if (preg_match('/\d+/', $d, $m)) {
                    $length = $m[0];
                    $vids = $this->getValIdsSmart(16, $length, 2); // Допуск 2мм как в бэкапе
                    if ($vids) $dim_ids = array_merge($dim_ids, $vids);
                }
            }
            if ($dim_ids) $params[] = 'F16S2V' . implode('V', array_unique($dim_ids));

            $domain = (isset($this->request->server['HTTPS']) && ($this->request->server['HTTPS'] == 'on')) ? HTTPS_SERVER : HTTP_SERVER;
            $ocf = implode('', $params);
            
            $json['redirect'] = rtrim($domain, '/') . '/akkumulyatori/' . ($ocf ? '?ocf=' . $ocf : '');
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
        }
    }

    private function getValIdsSmart($f_id, $target_val, $tolerance = 0) {
        $found_ids = [];
        $target_val = (int)$target_val;
        $query = $this->db->query("SELECT value_id, name FROM oc_ocfilter_filter_value_description WHERE filter_id = " . (int)$f_id);
        
        foreach ($query->rows as $row) {
            if (preg_match_all('/\d+/', $row['name'], $matches)) {
                foreach ($matches[0] as $num) {
                    $db_num = (int)$num;
                    if ($tolerance > 0) {
                        if ($db_num >= ($target_val - $tolerance) && $db_num <= ($target_val + $tolerance)) {
                            $found_ids[] = $row['value_id'];
                            break; 
                        }
                    } else {
                        if ($db_num == $target_val) {
                            $found_ids[] = $row['value_id'];
                            break;
                        }
                    }
                }
            }
        }
        return $found_ids;
    }
}
