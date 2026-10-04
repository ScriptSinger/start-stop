<?php

class ControllerExtensionModuleFilterAutoAutoBrands extends Controller
{
    private $error = [];

    public function index()
    {
        $data = $this->load->language('extension/module/auto');

        $this->load->model('extension/auto');

        $url = '';

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        // Breadcrumbs
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/auto_brands', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        // Language var
        $data['add'] = $this->url->link('extension/module/filter_auto/auto_brands/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('extension/module/filter_auto/auto_brands/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $data['user_token'] = $this->session->data['user_token'];

        //Auto brands
        $results = $this->model_extension_auto->getBrands();

        foreach ($results as $result) {
            $data['brands'][] = [
                'brand_id' => $result['brand_id'],
                'brand_name' => $result['brand_name'],
                'status' => ($result['active']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                'edit' => $this->url->link('extension/module/filter_auto/auto_brands/edit', 'user_token=' . $this->session->data['user_token'] . '&brand_id=' . $result['brand_id'] . $url, true)
            ];
        }

        $this->document->setTitle($this->language->get('heading_title'));

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/auto_brands_list', $data));
    }

    public function add()
    {
        $this->load->model('extension/auto');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_auto->addBrand($this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/module/filter_auto/auto_brands', 'user_token=' . $this->session->data['user_token'], true));
        }

        $this->getForm();
    }

    public function edit()
    {
        $this->load->model('extension/auto');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {

            $this->model_extension_auto->editBrand($this->request->get['brand_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/module/filter_auto/auto_brands', 'user_token=' . $this->session->data['user_token'], true));
        }

        $this->getForm();
    }

    public function delete()
    {
        $this->load->model('extension/auto');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {

            foreach ($this->request->post['selected'] as $brand_id) {
                $this->model_extension_auto->deleteBrands($brand_id);
            }

            $this->response->redirect($this->url->link('extension/module/filter_auto/auto_brands', 'user_token=' . $this->session->data['user_token'], true));
        }
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'extension/module/filter_auto/auto_brands')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    protected function getForm()
    {
        $data = $this->load->language('extension/module/auto');

        $data['text_form'] = !isset($this->request->get['brand_id']) ? $this->language->get('brand_add') : $this->language->get('brand_edit');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/filter_auto/auto_brands', 'user_token=' . $this->session->data['user_token'], true)
        );

        if (!isset($this->request->get['brand_id'])) {
            $data['action'] = $this->url->link('extension/module/filter_auto/auto_brands/add', 'user_token=' . $this->session->data['user_token'], true);
        } else {
            $data['action'] = $this->url->link('extension/module/filter_auto/auto_brands/edit', 'user_token=' . $this->session->data['user_token'] . '&brand_id=' . $this->request->get['brand_id'], true);
        }

        if (isset($this->request->get['brand_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $brand_info = $this->model_extension_auto->getBrand($this->request->get['brand_id']);
        }

        $data['cancel'] = $this->url->link('extension/module/filter_auto/auto_brands', 'user_token=' . $this->session->data['user_token'], true);
        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->post['brand'])) {
            $data['brand'] = $this->request->post['brand'];
        } elseif (!empty($brand_info)) {
            $data['brand'] = $brand_info;
        } else {
            $data['brand'] = [];
        }

        $this->load->model('tool/image');

        if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
            $data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
        } elseif (!empty($brand_info) && is_file(DIR_IMAGE . $brand_info['brand_img'])) {
            $data['thumb'] = $this->model_tool_image->resize($brand_info['brand_img'], 100, 100);
        } else {
            $data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        }

        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

        $this->document->setTitle($this->language->get('heading_title'));

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/auto_brands_form', $data));
    }

    protected function validateForm() {

        $this->load->language('extension/module/auto');

        if (!$this->user->hasPermission('modify', 'extension/module/filter_auto/auto_brands')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['brand']['brand_name']) {
                $this->error['name'] = $this->language->get('error_brand_name');
        }


        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }
}