<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(3600);
ini_set('memory_limit', '512M');

require_once('config.php');
require_once(DIR_SYSTEM . 'startup.php');

$registry = new Registry();
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
$registry->set('db', $db);

echo "<pre>Запуск генерации (Синхронизация с логикой контроллера)...\n";

// Функция подбора ID значений как в контроллере (getValIdsSmart)
function getValIdsSmart($db, $f_id, $target_val, $tolerance = 0) {
    $found_ids = [];
    $target_val = (int)$target_val;
    $query = $db->query("SELECT value_id, name FROM " . DB_PREFIX . "ocfilter_filter_value_description WHERE filter_id = " . (int)$f_id);
    
    foreach ($query->rows as $row) {
        if (preg_match_all('/\d+/', $row['name'], $matches)) {
            foreach ($matches[0] as $num) {
                $db_num = (int)$num;
                if ($tolerance > 0) {
                    if ($db_num >= ($target_val - $tolerance) && $db_num <= ($target_val + $tolerance)) {
                        $found_ids[] = (string)$row['value_id'];
                        break; 
                    }
                } else {
                    if ($db_num == $target_val) {
                        $found_ids[] = (string)$row['value_id'];
                        break;
                    }
                }
            }
        }
    }
    return array_unique($found_ids);
}

// Транслит для ЧПУ
function rus2translit($string) {
    $converter = array(
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'sch','ь'=>'','ы'=>'y','ъ'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
    );
    $string = mb_strtolower($string, 'UTF-8');
    $string = strtr($string, $converter);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}

// 1. Находим ID категории
$cat_query = $db->query("SELECT category_id FROM " . DB_PREFIX . "category_description WHERE name LIKE 'Аккумуляторы%' LIMIT 1");
$category_id = $cat_query->row['category_id'] ?? 1;

$cars = $db->query("SELECT * FROM oc_battery_base");
$count = 0;

foreach ($cars->rows as $car) {
    $params_data = [];

    // --- 1. ПОЛЯРНОСТЬ (ID 13) ---
    $pol_raw = mb_strtolower(trim($car['polarity']), 'UTF-8');
    $is_rev = preg_match('/(0|l\+|обрат|лев|left)/ui', $pol_raw);
    $pol_val = ($is_rev ? '1698094399' : '254229664');
    $params_data["13.2"] = [(string)$pol_val];

    // --- 2. ЕМКОСТЬ (ID 15) ---
    $cap_ids = [];
    $caps = explode(',', $car['capacity']);
    foreach ($caps as $c) {
        $val = preg_replace('/[^0-9]/', '', $c);
        if ($val) {
            $vids = getValIdsSmart($db, 15, $val);
            if ($vids) $cap_ids = array_merge($cap_ids, $vids);
        }
    }
    if ($cap_ids) {
        $cap_ids = array_unique($cap_ids);
        sort($cap_ids); // Сортируем ID внутри фильтра
        $params_data["15.2"] = $cap_ids;
    }

    // --- 3. ГАБАРИТЫ (ID 16) ---
    $dim_ids = [];
    $dims = explode(',', $car['dims']);
    foreach ($dims as $d) {
        if (preg_match('/\d+/', $d, $m)) {
            $vids = getValIdsSmart($db, 16, $m[0], 2); // Допуск 2мм как в контроллере
            if ($vids) $dim_ids = array_merge($dim_ids, $vids);
        }
    }
    if ($dim_ids) {
        $dim_ids = array_unique($dim_ids);
        sort($dim_ids);
        $params_data["16.2"] = $dim_ids;
    }

    if (empty($params_data)) continue;

    // Сортируем параметры по ключам (13.2, 15.2, 16.2), чтобы JSON был стабильным
    ksort($params_data);

    // Формируем JSON (как на фото 15748.jpg)
    $ocf_params = json_encode($params_data);
    $params_key = sprintf("%u", crc32($ocf_params));

    $full_car_name = trim($car['brand'] . ' ' . $car['model'] . ' ' . $car['generation']);

    // Запись страницы
    $db->query("INSERT INTO " . DB_PREFIX . "ocfilter_page SET 
        category_id = '" . (int)$category_id . "',
        params = '" . $db->escape($ocf_params) . "',
        params_key = '" . $db->escape($params_key) . "',
        params_count = '" . (int)count($params_data) . "',
        status = 1
        ON DUPLICATE KEY UPDATE params = '" . $db->escape($ocf_params) . "', params_count = '" . (int)count($params_data) . "', status = 1");

    $q_page = $db->query("SELECT page_id FROM " . DB_PREFIX . "ocfilter_page WHERE params_key = '" . $db->escape($params_key) . "' AND category_id = '" . (int)$category_id . "' LIMIT 1");
    $page_id = $q_page->row['page_id'] ?? 0;

    if ($page_id) {
        $h1 = "Аккумуляторы для " . $full_car_name;
        $title = "Купить аккумулятор на " . $full_car_name . " — цена в Уфе | Старт-Стоп";
        $meta_d = "⚡ Подбор АКБ для " . $full_car_name . ". Доставка по Уфе, бесплатная установка!";
        $desc = "<p>В магазине «Старт-Стоп» вы можете купить аккумулятор для <b>" . $full_car_name . "</b>.</p>";

        $db->query("DELETE FROM " . DB_PREFIX . "ocfilter_page_description WHERE page_id = '" . (int)$page_id . "'");
        $db->query("INSERT INTO " . DB_PREFIX . "ocfilter_page_description SET 
            page_id = '" . (int)$page_id . "',
            language_id = 1,
            name = '" . $db->escape($h1) . "',
            heading_title = '" . $db->escape($h1) . "',
            meta_title = '" . $db->escape($title) . "',
            meta_description = '" . $db->escape($meta_d) . "',
            description_bottom = '" . $db->escape($desc) . "'");

        // SEO URL
        $keyword = rus2translit("akkumulyator-dlya-" . $full_car_name);
        $db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'ocfilter_page_id=" . (int)$page_id . "'");
        $db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = 0, language_id = 1, query = 'ocfilter_page_id=" . (int)$page_id . "', keyword = '" . $db->escape($keyword) . "'");

        $count++;
    }
}

echo "\nГенерация завершена! Всего страниц: $count\n";
echo "</pre>";
