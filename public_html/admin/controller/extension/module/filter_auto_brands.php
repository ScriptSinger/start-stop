<?php

class ControllerExtensionModuleFilterAutoBrands extends Controller
{
    private $error = [];

    public function index()
    {
        $data = $this->load->language('extension/module/filter_auto_brands');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model('extension/auto');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_filter_auto_brands', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['error_image'])) {
            $data['error_image'] = $this->error['error_image'];
        } else {
            $data['error_image'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/filter_auto_brands', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/filter_auto_brands', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->post['module_filter_auto_brands_status'])) {
            $data['status'] = $this->request->post['module_filter_auto_brands_status'];
        } else {
            $data['status'] = $this->config->get('module_filter_auto_brands_status');
        }

        if (isset($this->request->post['module_filter_auto_brands_width'])) {
            $data['module_filter_auto_brands_width'] = $this->request->post['module_filter_auto_brands_width'];
        } elseif (!empty($this->config->get('module_filter_auto_brands_width'))) {
            $data['module_filter_auto_brands_width'] = $this->config->get('module_filter_auto_brands_width');
        } else {
            $data['module_filter_auto_brands_width'] = 36;
        }

        if (isset($this->request->post['module_filter_auto_brands_height'])) {
            $data['module_filter_auto_brands_height'] = $this->request->post['module_filter_auto_brands_height'];
        } elseif (!empty($this->config->get('module_filter_auto_brands_height'))) {
            $data['module_filter_auto_brands_height'] = $this->config->get('module_filter_auto_brands_height');
        } else {
            $data['module_filter_auto_brands_height'] = 36;
        }

        $data['user_token'] = $this->session->data['user_token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/filter_auto_brands', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/filter_auto_brands')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['module_filter_auto_brands_width'] || !$this->request->post['module_filter_auto_brands_height']) {
            $this->error['error_image'] = $this->language->get('error_image');
        }

        return !$this->error;
    }
}