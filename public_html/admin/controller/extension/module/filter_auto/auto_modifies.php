<?php

class ControllerExtensionModuleFilterAutoAutoModifies extends Controller
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

        if (isset($this->request->get['filter_model'])) {
            $filter_model = $this->request->get['filter_model'];
        } else {
            $filter_model = null;
        }

        $url = '';

        if (isset($this->request->get['filter_brand'])) {
            $url .= '&filter_brand=' . urlencode(html_entity_decode($this->request->get['filter_brand'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_model'])) {
            $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
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
            'text' => $this->language->get('heading_modif'),
            'href' => $this->url->link('extension/module/filter_auto/auto_modifies', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        $data['add'] = $this->url->link('extension/module/filter_auto/auto_modifies/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('extension/module/filter_auto/auto_modifies/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->get['filter_model'])) {
            $results = $this->model_extension_auto->getModifies($filter_model);

            foreach ($results as $result) {
                $modif_name = ($result['modif_name'] == 'all') ? $this->language->get('modif_any') : $result['modif_name'];

                $data['modifies'][] = [
                    'modif_id' => $result['modif_id'],
                    'model_id' => $result['model_id'],
                    'modif_name' => $modif_name,
                    'modif_dvs' => $result['modif_dvs'],
                    'modif_hp' => $result['modif_hp'],
                    'modif_start' => $result['modif_start'],
                    'modif_end' => ($result['modif_end'] == 0) ? $this->language->get('text_actual') : $result['modif_end'],
                    'modif_body' => $result['modif_body'],
                    'status' => ($result['active']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                    'edit' => $this->url->link('extension/module/filter_auto/auto_modifies/edit', 'user_token=' . $this->session->data['user_token'] . '&modif_id=' . $result['modif_id'] . $url, true)
                ];
            }
        } else {
            $data['modifies'] = [];
        }

        $data['brands'] = $this->model_extension_auto->getBrands();

        if ($filter_brand) {
            $data['models'] = $this->model_extension_auto->getModels($filter_brand);
        } else {
            $data['models'] = [];
        }

        $data['filter_brand'] = $filter_brand;
        $data['filter_model'] = $filter_model;

        $this->document->setTitle($this->language->get('heading_modif'));

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/auto_modif_list', $data));
    }

    public function add()
    {
        $result = [];

        if ($this->request->post['modif_name'] &&
            $this->request->post['model_id']
        ) {
            $this->load->model('extension/auto');
            $insert_id = $this->model_extension_auto->addModify($this->request->post);

            $result['result'] = $insert_id;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));
    }

    public function delete()
    {
        $this->load->model('extension/auto');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {

            foreach ($this->request->post['selected'] as $modif_id) {
                $this->model_extension_auto->deleteModify($modif_id);
            }

            $url = '';

            if (isset($this->request->get['filter_brand'])) {
                $url .= '&filter_brand=' . urlencode(html_entity_decode($this->request->get['filter_brand'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_model'])) {
                $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
            }

            $this->response->redirect($this->url->link('extension/module/filter_auto/auto_modifies', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }
    }

    public function edit()
    {
        $this->load->model('extension/auto');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {

            $this->model_extension_auto->editModify($this->request->get['modif_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['filter_brand'])) {
                $url .= '&filter_brand=' . urlencode(html_entity_decode($this->request->get['filter_brand'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_model'])) {
                $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
            }

            $this->response->redirect($this->url->link('extension/module/filter_auto/auto_modifies', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    protected function getForm()
    {
        $data = $this->load->language('extension/module/auto');

        $data['text_form'] = $this->language->get('modif_edit');

        $url = '';

        if (isset($this->request->get['filter_brand'])) {
            $url .= '&filter_brand=' . urlencode(html_entity_decode($this->request->get['filter_brand'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_model'])) {
            $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
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

        if (isset($this->error['dvs'])) {
            $data['error_dvs'] = $this->error['dvs'];
        } else {
            $data['error_dvs'] = '';
        }

        if (isset($this->error['hp'])) {
            $data['error_hp'] = $this->error['hp'];
        } else {
            $data['error_hp'] = '';
        }

        if (isset($this->error['body'])) {
            $data['error_body'] = $this->error['body'];
        } else {
            $data['error_body'] = '';
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
            'href' => $this->url->link('extension/module/filter_auto/auto_modifies', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        $data['action'] = $this->url->link('extension/module/filter_auto/auto_modifies/edit', 'user_token=' . $this->session->data['user_token'] . '&modif_id=' . $this->request->get['modif_id'] . $url, true);

        $data['cancel'] = $this->url->link('extension/module/filter_auto/auto_modifies', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->post['modif'])) {
            $data['modif'] = $this->request->post['modif'];
        } elseif (isset($this->request->get['modif_id'])) {
            $data['modif'] = $this->model_extension_auto->getModify($this->request->get['modif_id']);
        } else {
            $data['modif'] = [];
        }

        $this->document->setTitle($this->language->get('heading_modif'));

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/auto_modif_form', $data));
    }

    protected function validateForm() {

        $this->load->language('extension/module/auto');

        if (!$this->user->hasPermission('modify', 'extension/module/filter_auto/auto_models')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['modif']['modif_name']) {
            $this->error['name'] = $this->language->get('error_modif_name');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'extension/module/filter_auto/auto_modifies')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function modifies()
    {
        $json = [];

        if (isset($this->request->post['model_id'])) {

            $this->load->language('extension/module/auto');
            $this->load->model('extension/auto');

            $results = $this->model_extension_auto->getModifies($this->request->post['model_id']);

            foreach ($results as $result) {

                $json[] = [
                    'modif_id' => $result['modif_id'],
                    'model_id' => $result['model_id'],
                    'modif_name' => $result['modif_name'],
                    'modif_dvs' => $result['modif_dvs'],
                    'modif_hp' => $result['modif_hp'],
                    'modif_start' => $result['modif_start'],
                    'modif_end' => ($result['modif_end'] == 0) ? $this->language->get('text_actual') : $result['modif_end'],
                    'modif_body' => $result['modif_body']
                ];
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function add_link()
    {
        $json = [];

        if ($this->request->post) {
            $product_id = $this->request->post['product_id'];
            $modif_array = $this->request->post['modif_id'];

            if ($product_id && !empty($modif_array)) {
                $this->load->model('extension/auto');

                $this->model_extension_auto->addLinks($product_id, $modif_array);

                $json['result'] = true;
                $json['links'] = $this->model_extension_auto->getModifiesInfo($modif_array);
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function del_link()
    {
        $json = [];

        if ($this->request->post) {
            $product_id = $this->request->post['product_id'];
            $modif_id = $this->request->post['modif_id'];

            if ($product_id && $modif_id) {
                $this->load->model('extension/auto');

                $this->model_extension_auto->delLinks($product_id, $modif_id);

                $json['result'] = true;
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}