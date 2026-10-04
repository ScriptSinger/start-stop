<?php
class ModelExtensionModuleExample extends Model {
  // Загрузка настроек из базы данных
  public function LoadSettings() {
    return $this->config->get('module_example_status');
  }
}
?>