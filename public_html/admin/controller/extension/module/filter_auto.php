<?php

class ControllerExtensionModuleFilterAuto extends Controller
{
    private $error = [];

    public function index()
    {
        $data = $this->load->language('extension/module/filter_auto');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/module');
        $this->load->model('extension/auto');
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] === 'POST') && $this->validate()) {

            if (!isset($this->request->get['module_id'])) {
                $this->model_setting_module->addModule('filter_auto', $this->request->post);
            } else {
                $this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
                $this->model_setting_setting->editSetting('filter_auto', $this->request->post);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
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
            'href' => $this->url->link('extension/module/filter_auto', 'user_token=' . $this->session->data['user_token'], true)
        );

        if (!isset($this->request->get['module_id'])) {
            $data['action'] = $this->url->link('extension/module/filter_auto', 'user_token=' . $this->session->data['user_token'], true);
        } else {
            $data['action'] = $this->url->link('extension/module/filter_auto', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
        }

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] !== 'POST')) {
            $module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
        }

        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($module_info)) {
            $data['name'] = $module_info['name'];
        } else {
            $data['name'] = '';
        }

        if (isset($this->request->post['filter_auto_applicability'])) {
            $data['filter_auto_applicability'] = $this->request->post['filter_auto_applicability'];
        } elseif ($this->config->get('filter_auto_applicability')) {
            $data['filter_auto_applicability'] = $this->config->get('filter_auto_applicability');
        } else {
            $data['filter_auto_applicability'] = false;
        }

        if (isset($this->request->post['filter_auto_remember'])) {
            $data['filter_auto_remember'] = $this->request->post['filter_auto_remember'];
        } elseif (!empty($this->config->get('filter_auto_remember'))) {
            $data['filter_auto_remember'] = $this->config->get('filter_auto_remember');
        } else {
            $data['filter_auto_remember'] = false;
        }

        if (isset($this->request->post['filter_auto_show_year'])) {
            $data['filter_auto_show_year'] = $this->request->post['filter_auto_show_year'];
        } elseif (!empty($this->config->get('filter_auto_show_year'))) {
            $data['filter_auto_show_year'] = $this->config->get('filter_auto_show_year');
        } else {
            $data['filter_auto_show_year'] = false;
        }

        if (isset($this->request->post['filter_auto_type'])) {
            $data['filter_auto_type'] = $this->request->post['filter_auto_type'];
        } elseif (!empty($module_info)) {
            $data['filter_auto_type'] = $module_info['filter_auto_type'];
        } else {
            $data['filter_auto_type'] = '';
        }

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($module_info)) {
            $data['status'] = $module_info['status'];
        } else {
            $data['status'] = '';
        }

        if (isset($this->request->post['filter_auto_attr'])) {
            $data['filter_auto_attr'] = $this->request->post['filter_auto_attr'];
        } elseif (!empty($module_info['filter_auto_attr'])) {
            $data['filter_auto_attr'] = $module_info['filter_auto_attr'];
        } else {
            $data['filter_auto_attr'] = [];
        }

        $seo_setting = $this->model_setting_setting->getSetting('filter_auto');

        if (isset($this->request->post['description'])) {
            $data['description'] = $this->request->post['description'];
        } elseif (isset($seo_setting['filter_auto_description'])) {
            $data['description'] = $seo_setting['filter_auto_description'];
        } else {
            $data['description'] = '';
        }

        if (isset($this->request->post['model_description'])) {
            $data['model_description'] = $this->request->post['model_description'];
        } elseif (isset($seo_setting['filter_auto_model_description'])) {
            $data['model_description'] = $seo_setting['filter_auto_model_description'];
        } else {
            $data['model_description'] = '';
        }

        $data['model_count'] = $this->model_extension_auto->noSlugModelCount();

        $data['no_slugs'] = $data['model_count'] + $this->model_extension_auto->noSlugBrandCount() + $this->model_extension_auto->noSlugModule();

        $data['limit_model'] = 50;

        $attr_count = $this->model_extension_auto->countProductAttribute();
        $data['attr_count'] = $attr_count;

        $data['attributes'] = $this->model_extension_auto->getAttributes();

        if ($attr_count < 100) {
            $data['limit'] = 10;
        } else {
            $data['limit'] = ceil($attr_count/100);
        }

        $data['start'] = 0;

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $data['user_token'] = $this->session->data['user_token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/filter_auto', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/filter_auto')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        }


        return !$this->error;
    }

    public function generate_slug()
    {
        $json['success'] = false;

        if (($this->request->server['REQUEST_METHOD'] === 'POST')
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

    public function copyattr()
    {
        $json['success'] = false;

        if (($this->request->server['REQUEST_METHOD'] === 'POST')
            && isset($this->request->post['start'])
            && $this->request->post['limit']
            && isset($this->request->post['iteration'])
        ) {
            $this->load->model('extension/auto');

            $start = $this->request->post['start'];
            $limit = $this->request->post['limit'];
            $iteration = $this->request->post['iteration'];

            $status = $this->model_extension_auto->refreshProductAttribute($start, $limit, $iteration);

            if ($status) {
                $json['success'] = true;
                $json['iteration'] = ++$iteration;
            } else {
                $json['success'] = false;
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function install() {
        $this->load->model('user/user_group');
        $this->load->model('extension/auto');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/auto_brands');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/auto_brands');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/auto_models');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/auto_models');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/auto_modifies');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/auto_modifies');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/filter_auto');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/filter_auto');

        $this->model_extension_auto->createTable();
    }

    public function uninstall() {
        $this->load->model('user/user_group');
        $this->load->model('extension/auto');

        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/module/auto_brands');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/module/auto_brands');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/module/auto_models');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/module/auto_models');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/module/auto_modifies');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/module/auto_modifies');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/module/filter_auto');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/module/filter_auto');

        $this->model_extension_auto->removeTable();
    }
}