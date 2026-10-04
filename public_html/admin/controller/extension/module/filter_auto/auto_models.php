<?php

class ControllerExtensionModuleFilterAutoAutoModels extends Controller
{
    private $error = [];

    public function index()
    {
        $data = $this->load->language('extension/module/auto');

        $this->load->model('extension/auto');

        if (isset($this->request->get['filter_brand'])) {
            $filter_brand = $this->request->get['filter_brand'];
        } else {
            $filter_brand = null;
        }

        $url = '';

        if (isset($this->request->get['filter_brand'])) {
            $url .= '&filter_brand=' . urlencode(html_entity_decode($this->request->get['filter_brand'], ENT_QUOTES, 'UTF-8'));
        }

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
            'text' => $this->language->get('heading_models'),
            'href' => $this->url->link('extension/module/filter_auto/auto_models', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        // Language var
        $data['add'] = $this->url->link('extension/module/filter_auto/auto_models/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('extension/module/filter_auto/auto_models/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->get['filter_brand'])) {
            $results = $this->model_extension_auto->getModels($filter_brand);

            foreach ($results as $result) {
                $data['models'][] = [
                    'model_id' => $result['model_id'],
                    'brand_id' => $result['brand_id'],
                    'model_name' => $result['model_name'],
                    'model_start' => $result['model_start'],
                    'model_end' => ($result['model_end'] == 0) ? $this->language->get('text_actual') : $result['model_end'],
                    'status' => ($result['active']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                    'edit' => $this->url->link('extension/module/filter_auto/auto_models/edit', 'user_token=' . $this->session->data['user_token'] . '&model_id=' . $result['model_id'] . $url, true)
                ];
            }
        } else {
            $data['models'] = [];
        }

        $data['brands'] = $this->model_extension_auto->getBrands();
        $data['filter_brand'] = $filter_brand;

        $this->document->setTitle($this->language->get('heading_title'));

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/auto_models_list', $data));
    }

    public function add()
    {
        $result = [];

        if ($this->request->post['model_name'] &&
            $this->request->post['brand_id']
        ) {
            $this->load->model('extension/auto');
            $insert_id = $this->model_extension_auto->addModel($this->request->post);

            $result['result'] = $insert_id;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));
    }

    public function edit()
    {
        $this->load->model('extension/auto');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {

            $this->model_extension_auto->editModel($this->request->get['model_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['filter_brand'])) {
                $url .= '&filter_brand=' . urlencode(html_entity_decode($this->request->get['filter_brand'], ENT_QUOTES, 'UTF-8'));
            }

            $this->response->redirect($this->url->link('extension/module/filter_auto/auto_models', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function delete()
    {
        $this->load->model('extension/auto');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {

            foreach ($this->request->post['selected'] as $model_id) {
                $this->model_extension_auto->deleteModel($model_id);
            }

            $url = '';

            if (isset($this->request->get['filter_brand'])) {
                $url .= '&filter_brand=' . urlencode(html_entity_decode($this->request->get['filter_brand'], ENT_QUOTES, 'UTF-8'));
            }

            $this->response->redirect($this->url->link('extension/module/filter_auto/auto_models', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }
    }

    protected function getForm()
    {
        $data = $this->load->language('extension/module/auto');

        $data['text_form'] = $this->language->get('model_edit');

        $url = '';

        if (isset($this->request->get['filter_brand'])) {
            $url .= '&filter_brand=' . urlencode(html_entity_decode($this->request->get['filter_brand'], ENT_QUOTES, 'UTF-8'));
        }

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

        if (isset($this->error['start'])) {
            $data['error_start'] = $this->error['start'];
        } else {
            $data['error_start'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/filter_auto/auto_models', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        $data['action'] = $this->url->link('extension/module/filter_auto/auto_models/edit', 'user_token=' . $this->session->data['user_token'] . '&model_id=' . $this->request->get['model_id'] . $url, true);

        $data['cancel'] = $this->url->link('extension/module/filter_auto/auto_models', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->get['model_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $model_info = $this->model_extension_auto->getModel($this->request->get['model_id']);
        }

        if (isset($this->request->post['model'])) {
            $data['model'] = $this->request->post['model'];
        } elseif (isset($this->request->get['model_id'])) {
            $data['model'] = $model_info;
        } else {
            $data['model'] = [];
        }

        $this->load->model('tool/image');

        if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
            $data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
        } elseif (!empty($model_info) && is_file(DIR_IMAGE . $model_info['model_img'])) {
            $data['thumb'] = $this->model_tool_image->resize($model_info['model_img'], 100, 100);
        } else {
            $data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        }

        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

        $this->document->setTitle($this->language->get('heading_models'));

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/auto_models_form', $data));
    }

    public function models()
    {
        $json = [];

        if (isset($this->request->post['brand_id'])) {

            $this->load->model('extension/auto');

            $json = $this->model_extension_auto->getModels($this->request->post['brand_id']);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function autocomplete()
    {
        $json = [];

        if (isset($this->request->get['filter_name'])) {
            $this->load->model('extension/auto');

            $filter_data = array(
                'filter_name' => $this->request->get['filter_name'],
                'order'       => 'ASC',
                'start'       => 0,
                'limit'       => 5
            );

            $results = $this->model_extension_auto->getModelsAutoCmpl($filter_data);

            foreach ($results as $result) {
                $json[] = array(
                    'model_id' => $result['model_id'],
                    'model_name'        => strip_tags(html_entity_decode($result['brand_name'] . ' ' . $result['model_name'], ENT_QUOTES, 'UTF-8'))
                );
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function validateForm() {

        $this->load->language('extension/module/auto');

        if (!$this->user->hasPermission('modify', 'extension/module/filter_auto/auto_models')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['model']['model_name']) {
            $this->error['name'] = $this->language->get('error_model_name');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'extension/module/filter_auto/auto_models')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}