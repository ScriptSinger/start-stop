<?php
class ControllerExtensionModuleExample extends Controller {
  public function index() {
    // Загружаем "модель"
    $this->load->model('extension/module/example');
    $data = array();
    // Загружаем настройки (для проверки включен модуль или нет)
    $data['module_example_status'] = $this->model_extension_module_example->LoadSettings();
    // Загружаем языковой файл
    $data += $this->load->language('extension/module/example');
    // Хлебные крошки
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/home')
    );
    $data['breadcrumbs'][] = array(
      'text' => $data['heading_title'],
      'href' => $this->url->link('extension/module/example')
    );
    // Загружаем остальное
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['column_right'] = $this->load->controller('common/column_right');
    $data['content_top'] = $this->load->controller('common/content_top');
    $data['content_bottom'] = $this->load->controller('common/content_bottom');
    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');
    // Выводим на экран
    $this->response->setOutput($this->load->view('extension/module/example', $data));
  }
}
?>