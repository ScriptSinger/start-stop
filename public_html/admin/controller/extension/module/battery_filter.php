<?php
class ControllerExtensionModuleBatteryFilter extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/module/battery_filter');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        // Обработка сохранения
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_battery_filter', $this->request->post);
            $this->session->data['success'] = 'Настройки модуля обновлены!';
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        $data['heading_title'] = $this->language->get('heading_title');

        // Текст для кнопок
        $data['button_save'] = 'Сохранить и Включить';
        $data['button_cancel'] = 'Отмена';

        // Статус модуля
        if (isset($this->request->post['module_battery_filter_status'])) {
            $data['module_battery_filter_status'] = $this->request->post['module_battery_filter_status'];
        } else {
            $data['module_battery_filter_status'] = $this->config->get('module_battery_filter_status');
        }

        // Ссылки
        $data['action'] = $this->url->link('extension/module/battery_filter', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/battery_filter', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/battery_filter')) {
            $this->error['warning'] = 'У вас нет прав для управления этим модулем!';
        }
        return !$this->error;
    }
}