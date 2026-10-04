<?php
// Путь к папке с картинками машин
$dir = __DIR__ . '/image/catalog/cars/';

if (!is_dir($dir)) {
    die("Ошибка: Директория $dir не найдена!");
}

$files = scandir($dir);
$count = 0;

echo "<pre>";
echo "Начинаю переименование файлов в: $dir\n\n";

foreach ($files as $file) {
    if ($file == '.' || $file == '..' || is_dir($dir . $file)) continue;

    // Исходное имя
    $old_name = $file;

    // Правило переименования (как в контроллере):
    // 1. Заменяем " - " на "_"
    $new_name = str_replace(' - ', '_', $old_name);
    // 2. Все оставшиеся пробелы на "_"
    $new_name = str_replace(' ', '_', $new_name);
    // 3. Убираем точки (кроме расширения)
    $ext = pathinfo($new_name, PATHINFO_EXTENSION);
    $name_part = pathinfo($new_name, PATHINFO_FILENAME);
    $name_part = str_replace(['.', ','], '', $name_part);
    // 4. Склеиваем и убираем двойные подчеркивания
    $new_name = $name_part . '.' . $ext;
    $new_name = preg_replace('/__+/', '_', $new_name);

    if ($old_name !== $new_name) {
        if (rename($dir . $old_name, $dir . $new_name)) {
            echo "Переименовано: $old_name  ===>  $new_name\n";
            $count++;
        } else {
            echo "Ошибка при переименовании: $old_name\n";
        }
    }
}

echo "\nГотово! Переименовано файлов: $count";
echo "</pre>";