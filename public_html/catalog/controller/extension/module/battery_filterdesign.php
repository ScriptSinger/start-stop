<?php 
class ControllerExtensionModuleBatteryFilter extends Controller { 
    public function index() { 
        $this->load->model('tool/image');
        $query = $this->db->query("SELECT DISTINCT brand FROM oc_battery_base WHERE brand != '' ORDER BY brand ASC"); 
        
        $popular_list = ['LADA', 'Toyota', 'Hyundai', 'KIA', 'Renault', 'Volkswagen', 'Skoda', 'Nissan', 'Ford', 'Chevrolet', 'BMW', 'Mercedes-Benz'];
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
        $data['brands'] = $query->rows; 
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
    
    // Получаем уникальные поколения для данной модели
    $query = $this->db->query("SELECT DISTINCT generation FROM oc_battery_base WHERE brand = '" . $this->db->escape($brand) . "' AND model = '" . $this->db->escape($model) . "' ORDER BY generation ASC"); 
    
    $results = [];
    foreach ($query->rows as $row) {
        $gen_name = trim($row['generation']);
        
        // --- БЛОК 1: ОЧИСТКА И ФОРМИРОВАНИЕ ИМЕНИ ---
        // 1. Превращаем пробелы и тире в подчеркивания
        $base_name = str_replace([' - ', ' '], '_', $gen_name);
        // 2. Убираем скобки и точки (они часто ломают пути)
        $base_name = str_replace(['(', ')', '.', ','], '', $base_name);
        // 3. Убираем двойные подчеркивания
        $base_name = preg_replace('/__+/', '_', $base_name);
        // 4. Очищаем края от подчеркиваний, чтобы получить "чистое" имя
        $f_name_clean = trim($base_name, '_');
        
        // --- БЛОК 2: ПОИСК ФАЙЛА ПО ВАРИАНТАМ ---
        $image_found = '';
        
        // Варианты имен, которые мы проверим (именно в таком порядке!)
        $variants = [
            'catalog/cars/' . $f_name_clean . '_.jpg',   // С подчеркиванием в конце (как на скрине)
            'catalog/cars/' . $f_name_clean . '.jpg',    // Нормальное имя
            'catalog/cars/_' . $f_name_clean . '.jpg',   // С подчеркиванием впереди
            'catalog/cars/_' . $f_name_clean . '_.jpg',  // С подчеркиванием и там и там
            // Добавляем нижний регистр для самых "упрямых" файлов
            'catalog/cars/' . mb_strtolower($f_name_clean, 'UTF-8') . '.jpg',
            'catalog/cars/' . mb_strtolower($f_name_clean, 'UTF-8') . '_.jpg'
        ];

        // Проверяем физическое наличие каждого варианта
        foreach ($variants as $path) {
            if (file_exists(DIR_IMAGE . $path)) {
                $image_found = $path;
                break; // Нашли файл — прерываем цикл проверки
            }
        }

        // --- БЛОК 3: ИТОГОВЫЙ РЕСАЙЗ ИЛИ ЗАГЛУШКА ---
        if ($image_found) {
            $image = $this->model_tool_image->resize($image_found, 250, 150);
        } else {
            // Если совсем ничего не нашли — выводим no_image.jpg
            // (Проверь, что этот файл есть в папке!)
            $image = $this->model_tool_image->resize('catalog/cars/no_image.jpg', 250, 150);
        }

        $results[] = [
            'name'  => $gen_name ?: 'Стандарт',
            'image' => $image
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
        if ($gen && $gen != 'Стандарт') $sql .= " AND generation = '".$this->db->escape($gen)."'";
        $res = $this->db->query($sql . " LIMIT 1")->row;
        $json = [];
        if ($res) { 
            $params = [];
            $is_reverse = preg_match('/(0|l\+|обрат|лев)/ui', $res['polarity']); 
            $pol_val_id = $is_reverse ? '1698094399' : '254229664'; 
            $params[] = 'F13S2V' . $pol_val_id; 
            $cap_ids = []; $caps = explode(',', $res['capacity']); foreach ($caps as $c) { if (preg_match('/(\d+)/', $c, $m)) { $sid = $this->getValid(15, $m[0]); if ($sid) $cap_ids[] = $sid; } } if ($cap_ids) $params[] = 'F15S2V' . implode('V', array_unique($cap_ids)); 
            $dim_ids = []; $dims = explode(',', $res['dims']); foreach ($dims as $d) { $d_clean = preg_replace('/[^0-9]/', '', explode('x', strtolower($d))[0]); if ($d_clean) { $sid = $this->getValid(16, $d_clean); if ($sid) $dim_ids[] = $sid; } } if ($dim_ids) $params[] = 'F16S2V' . implode('V', array_unique($dim_ids)); 
            $domain = (isset($this->request->server['HTTPS']) && ($this->request->server['HTTPS'] == 'on')) ? HTTPS_SERVER : HTTP_SERVER; 
            $json['redirect'] = rtrim($domain, '/') . '/akkumulyatori/?ocf=' . implode('', $params); 
        } 
        $this->response->addHeader('Content-Type: application/json'); $this->response->setOutput(json_encode($json)); 
    } 

    private function getValid($f_id, $text) { if (!$text) return false; $query = $this->db->query("SELECT value_id FROM oc_ocfilter_filter_value_description WHERE filter_id = '" . (int)$f_id . "' AND name = '" . $this->db->escape(trim($text)) . "' LIMIT 1"); return ($query->num_rows) ? $query->row['value_id'] : false; } 
}
