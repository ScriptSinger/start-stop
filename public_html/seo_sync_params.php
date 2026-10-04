<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);

require_once('config.php');
require_once(DIR_SYSTEM . 'startup.php');

$registry = new Registry();
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
$registry->set('db', $db);
$registry->set('load', new Loader($registry));

// Подключаем твой контроллер подбора
$path = DIR_APPLICATION . 'controller/extension/module/battery_filter.php';
if (!file_exists($path)) $path = str_replace('system/', 'catalog/', DIR_SYSTEM) . 'controller/extension/module/battery_filter.php';
require_once($path);
$battery_ctrl = new ControllerExtensionModuleBatteryFilter($registry);

echo "<pre>--- ЗАПУСК СИНХРОНИЗАЦИИ ФИЛЬТРОВ ---\n";

// 1. Берем созданные страницы, где еще нет параметров
$pages = $db->query("SELECT p.page_id, d.name FROM " . DB_PREFIX . "ocfilter_page p 
                     LEFT JOIN " . DB_PREFIX . "ocfilter_page_description d ON (p.page_id = d.page_id) 
                     WHERE p.category_id = 1 AND (p.params = '[]' OR p.params = '')");

echo "Найдено страниц для обработки: " . $pages->num_rows . "\n";

$updated = 0;
foreach ($pages->rows as $page) {
    // Вытаскиваем название машины из заголовка (например, "Аккумуляторы для Kia Rio")
    $car_name = str_replace("Аккумуляторы для ", "", $page['name']);
    
    // Ищем эту машину в базе АКБ
    // Разбиваем имя на бренд и модель (грубо, но для теста пойдет)
    $parts = explode(' ', $car_name);
    $brand = $parts[0];
    $model = isset($parts[1]) ? $parts[1] : '';
    
    $car_query = $db->query("SELECT * FROM oc_battery_base WHERE brand LIKE '" . $db->escape($brand) . "' AND model LIKE '" . $db->escape($model) . "' LIMIT 1");
    
    if ($car_query->num_rows) {
        $car = $car_query->row;
        
        // Получаем параметры через твой рабочий метод
        $params_data = $battery_ctrl->getParamsForSeo($car);
        
        if ($params_data) {
            ksort($params_data);
            $ocf_params = json_encode($params_data);
            $params_key = sprintf("%u", crc32($ocf_params . trim($car['brand'] . ' ' . $car['model'] . ' ' . $car['generation'])));

            // Обновляем страницу в базе
            $db->query("UPDATE " . DB_PREFIX . "ocfilter_page SET 
                params = '" . $db->escape($ocf_params) . "', 
                params_key = '" . $db->escape($params_key) . "',
                params_count = '" . (int)count($params_data) . "'
                WHERE page_id = '" . (int)$page['page_id'] . "'");
            
            $updated++;
        }
    }
}

echo "\nУспешно обновлено страниц: $updated\n";
echo "--- СИНХРОНИЗАЦИЯ ЗАВЕРШЕНА ---</pre>";
