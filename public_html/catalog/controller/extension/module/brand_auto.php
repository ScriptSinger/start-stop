<?php

class ControllerExtensionModuleBrandAuto extends Controller {

    public function index()
    {
        $data = $this->load->language('extension/module/filter_auto_brands');

        $this->load->model('extension/module/filter_auto');
        $this->load->model('tool/image');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        if (isset($this->request->get['auto_brand'])) {
            $brand_id = $this->request->get['auto_brand'];

            $brand_info = $this->model_extension_module_filter_auto->getBrandInfo($brand_id);
        }

        if (!empty($brand_info)) {

            $this->document->setTitle($this->language->get('auto_filter_partsfor') . $brand_info['brand_name']);
            $this->document->setDescription($brand_info['brand_name'] . $this->language->get('text_model_list'));

            $data['heading_title'] = $this->language->get('auto_filter_partsfor') . $brand_info['brand_name'];

            $data['breadcrumbs'][] = array(
                'text' => $brand_info['brand_name'],
                'href' => ''
            );

            $results = $this->model_extension_module_filter_auto->getModelsBrand($brand_id);

            $data['auto_models'] = [];

            foreach ($results as $result) {
                if ($result['model_img']) {
                    $image = $this->model_tool_image->resize($result['model_img'], 150, 150);
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', 150, 150);
                }

                $data['auto_models'][] = [
                    'name' => $result['model_name'],
                    'image' => $image,
                    'href' => $this->url->link('extension/module/model_auto', '&auto_model=' . $result['model_id'], true),
                ];
            }

            $this->document->addStyle('catalog/view/theme/default/stylesheet/filter_auto.css');
            $this->document->addScript('catalog/view/javascript/jquery-listnav.min.js');

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('extension/module/brand_auto', $data));

        } else {

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_error'),
                'href' => $this->url->link('error/not_found')
            );

            $this->document->setTitle($this->language->get('text_error'));

            $data['heading_title'] = $data['text_error'];

            $data['button_continue'] = $this->language->get('button_continue');

            $data['continue'] = $this->url->link('common/home');

            $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('error/not_found', $data));
        }
    }
}