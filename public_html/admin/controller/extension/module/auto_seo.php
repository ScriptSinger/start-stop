<?php

class ControllerExtensionModuleAutoSeo extends Controller {

    private $error = array();

    public function index()
    {
        $this->load->language('extension/module/auto_seo');
        $this->load->language('extension/module/filter_auto');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/auto_seo');

        $this->getList();
    }

    public function add()
    {
        $this->load->language('extension/module/auto_seo');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/auto_seo');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_auto_seo->addSeoPage($this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('extension/module/auto_seo', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function edit()
    {
        $this->load->language('extension/module/auto_seo');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/auto_seo');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_auto_seo->editSeoPage($this->request->get['seo_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('extension/module/auto_seo', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function delete()
    {
        $this->load->language('extension/module/auto_seo');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/auto_seo');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $seo_id) {
                $this->model_extension_auto_seo->deleteSeoPage($seo_id);
            }

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('extension/module/auto_seo', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getList();
    }

    public function getList()
    {
        if (isset($this->request->get['filter_category'])) {
            $filter_category = $this->request->get['filter_category'];
        } else {
            $filter_category = 0;
        }

        if (isset($this->request->get['filter_model'])) {
            $filter_model = $this->request->get['filter_model'];
        } else {
            $filter_model = 0;
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'DESC';
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 's.seo_id';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['filter_category'])) {
            $url .= '&filter_category=' . $this->request->get['filter_category'];
        }

        if (isset($this->request->get['filter_model'])) {
            $url .= '&filter_model=' . $this->request->get['filter_model'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/auto_seo', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        $data['add'] = $this->url->link('extension/module/auto_seo/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('extension/module/auto_seo/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $data['seo_pages'] = [];

        $filter_data = array(
            'filter_category'   => $filter_category,
            'filter_model'      => $filter_model,
            'sort'              => $sort,
            'order'             => $order,
            'start'             => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit'             => $this->config->get('config_limit_admin')
        );

        $results_total = $this->model_extension_auto_seo->getTotalSeoPages($filter_data);

        $results = $this->model_extension_auto_seo->getSeoPages($filter_data);

        foreach ($results as $result) {
            $data['seo_pages'][] = [
                'seo_id' => $result['seo_id'],
                'name'   => $result['name'],
                'meta_title' => $result['meta_title'],
                'model_name' => $result['model_name'],
                'edit'       => $this->url->link('extension/module/auto_seo/edit', 'user_token=' . $this->session->data['user_token'] . '&seo_id=' . $result['seo_id'] . $url, true)
            ];
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_list'] = $this->language->get('text_list');
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['text_confirm'] = $this->language->get('text_confirm');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['entry_category'] = $this->language->get('entry_category');
        $data['entry_model'] = $this->language->get('entry_model');
        $data['entry_slug'] = $this->language->get('entry_slug');
        $data['entry_generate_ok'] = $this->language->get('entry_generate_ok');
        $data['entry_generate'] = $this->language->get('entry_generate');
        $data['entry_slug_info'] = $this->language->get('entry_slug_info');

        $data['column_category'] = $this->language->get('column_category');
        $data['column_model'] = $this->language->get('column_model');
        $data['column_meta_title'] = $this->language->get('column_meta_title');
        $data['column_action'] = $this->language->get('column_action');

        $data['button_add'] = $this->language->get('button_add');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_filter'] = $this->language->get('button_filter');

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array)$this->request->post['selected'];
        } else {
            $data['selected'] = array();
        }

        $url = '';

        if (isset($this->request->get['filter_category'])) {
            $url .= '&filter_category=' . $this->request->get['filter_category'];
        }

        if (isset($this->request->get['filter_model'])) {
            $url .= '&filter_model=' . $this->request->get['filter_model'];
        }

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_category'] = $this->url->link('extension/module/auto_seo', 'user_token=' . $this->session->data['user_token'] . '&sort=cd.name' . $url, true);
        $data['sort_model'] = $this->url->link('extension/module/auto_seo', 'user_token=' . $this->session->data['user_token'] . '&sort=m.model_name' . $url, true);
        $data['sort_meta_title'] = $this->url->link('extension/module/auto_seo', 'user_token=' . $this->session->data['user_token'] . '&sort=sd.meta_title' . $url, true);

        $url = '';

        if (isset($this->request->get['filter_category'])) {
            $url .= '&filter_category=' . $this->request->get['filter_category'];
        }

        if (isset($this->request->get['filter_model'])) {
            $url .= '&filter_model=' . $this->request->get['filter_model'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $results_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('extension/module/auto_seo', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($results_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($results_total - $this->config->get('config_limit_admin'))) ? $results_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $results_total, ceil($results_total / $this->config->get('config_limit_admin')));

        $this->load->model('catalog/category');
        $data['filter_category'] = $this->model_catalog_category->getCategory($filter_category);

        $this->load->model('extension/auto');
        $data['filter_model'] = $this->model_extension_auto->getModel($filter_model);

        $data['model_count'] = $this->model_extension_auto->noSlugModelCount();

        $data['no_slugs'] = $data['model_count'] + $this->model_extension_auto->noSlugBrandCount() + $this->model_extension_auto->noSlugModule();

        $data['limit_model'] = 50;
       
        $data['sort'] = $sort;
        $data['order'] = $order;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/auto_seo_list', $data));
    }

    public function getForm()
    {
        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_form'] = !isset($this->request->get['seo_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['entry_category'] = $this->language->get('entry_category');
        $data['entry_brand'] = $this->language->get('entry_brand');
        $data['entry_model'] = $this->language->get('entry_model');
        $data['entry_description'] = $this->language->get('entry_description');
        $data['entry_meta_title'] = $this->language->get('entry_meta_title');
        $data['entry_meta_description'] = $this->language->get('entry_meta_description');
        $data['entry_meta_keywords'] = $this->language->get('entry_meta_keywords');
        $data['entry_html_h1'] = $this->language->get('entry_html_h1');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['category'])) {
            $data['error_category'] = $this->error['category'];
        } else {
            $data['error_category'] = '';
        }

        if (isset($this->error['model'])) {
            $data['error_model'] = $this->error['model'];
        } else {
            $data['error_model'] = '';
        }

        if (isset($this->error['meta_title'])) {
            $data['error_meta_title'] = $this->error['meta_title'];
        } else {
            $data['error_meta_title'] = array();
        }

        if (isset($this->error['meta_description'])) {
            $data['error_meta_description'] = $this->error['meta_description'];
        } else {
            $data['error_meta_description'] = '';
        }

        $url = '';

        if (isset($this->request->get['filter_category'])) {
            $url .= '&filter_category=' . $this->request->get['filter_category'];
        }

        if (isset($this->request->get['filter_model'])) {
            $url .= '&filter_model=' . $this->request->get['filter_model'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/auto_seo', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        if (!isset($this->request->get['seo_id'])) {
            $data['action'] = $this->url->link('extension/module/auto_seo/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        } else {
            $data['action'] = $this->url->link('extension/module/auto_seo/edit', 'user_token=' . $this->session->data['user_token'] . '&seo_id=' . $this->request->get['seo_id'] . $url, true);
        }

        $data['cancel'] = $this->url->link('extension/module/auto_seo', 'user_token=' . $this->session->data['user_token'] . $url, true);

        if (isset($this->request->get['seo_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $seo_info = $this->model_extension_auto_seo->getSeoPage($this->request->get['seo_id']);
        }

        $data['user_token'] = $this->session->data['user_token'];

        $this->load->model('localisation/language');

        $data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->post['category'])) {
            $category = $this->request->post['category'];
        } elseif (!empty($seo_info)) {
            $category = $seo_info['category'];
        } else {
            $category = '';
        }

        $data['category'] = [];

        $this->load->model('catalog/category');
        $category_info = $this->model_catalog_category->getCategory($category);

        if ($category_info) {
            $data['category'] = array(
                'category_id' => $category_info['category_id'],
                'name'        => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
            );
        }

        if (isset($this->request->post['model'])) {
            $model = $this->request->post['model'];
        } elseif (!empty($seo_info)) {
            $model = $seo_info['model'];
        } else {
            $model = '';
        }

        $data['model'] = [];

        $this->load->model('extension/auto');
        $model_info = $this->model_extension_auto->getModel($model);

        if ($model_info) {
            $data['model'] = array(
                'model_id' => $model_info['model_id'],
                'model_name'  => $model_info['model_name']
            );
        }

        if (isset($this->request->post['description'])) {
            $data['description'] = $this->request->post['description'];
        } elseif (!empty($seo_info)) {
            $data['description'] = $seo_info['description'];
        } else {
            $data['description'] = '';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/auto_seo_form', $data));
    }

    public function generate_slug()
    {
        $json['success'] = false;

        if (($this->request->server['REQUEST_METHOD'] == 'POST')
            && isset($this->request->post['start'])
            && $this->request->post['limit']
            && isset($this->request->post['iteration'])
        ) {
            $start = $this->request->post['start'];
            $limit = $this->request->post['limit'];
            $iteration = $this->request->post['iteration'];

            $this->load->model('extension/auto');

            $this->model_extension_auto->generateModelSlug($start, $limit);

            $json['success'] = true;
            $json['iteration'] = ++$iteration;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function generate_brandmodule_slug()
    {
        $this->load->model('extension/auto');

        $noSlugBrands = $this->model_extension_auto->noSlugBrandCount();

        if ($noSlugBrands) {
            $this->model_extension_auto->generateBrandSlug();
        }

        $noSlugModule = $this->model_extension_auto->noSlugModule();

        if ($noSlugModule) {
            $this->model_extension_auto->generateModuleSlug();
        }
    }

    protected function validateForm() {
        if (!$this->user->hasPermission('modify', 'catalog/product')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['category']) {
            $this->error['category'] = $this->language->get('error_category');
        }

        if (!$this->request->post['model']) {
            $this->error['model'] = $this->language->get('error_model');
        }

        foreach ($this->request->post['description'] as $language_id => $value) {
            if ((utf8_strlen($value['meta_title']) < 3) || (utf8_strlen($value['meta_title']) > 255)) {
                $this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
            }

            if ((utf8_strlen($value['meta_title']) < 3) || (utf8_strlen($value['meta_title']) > 255)) {
                $this->error['meta_description'][$language_id] = $this->language->get('error_meta_description');
            }
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'extension/module/auto_seo')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}