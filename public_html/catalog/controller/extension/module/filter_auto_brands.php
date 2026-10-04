<?php

class ControllerExtensionModuleFilterAutoBrands extends Controller {

    public function index($setting) {
        $this->load->model('extension/module/filter_auto');

        $data = $this->load->language('extension/module/filter_auto_brands');

        $data['brands'] = [];

        $brands = $this->model_extension_module_filter_auto->getAllBrands();
        $this->load->model('tool/image');

        foreach ($brands as $brand) {
            if ($brand['brand_img']) {
                $image = $this->model_tool_image->resize($brand['brand_img'], $this->config->get('module_filter_auto_brands_width'), $this->config->get('module_filter_auto_brands_height'));
            } else {
                $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('module_filter_auto_brands_width'), $this->config->get('module_filter_auto_brands_height'));
            }

            $data['brands'][] = [
                'id' => $brand['brand_id'],
                'name' => $brand['brand_name'],
                'image' => $image,
                'href' => $this->url->link('extension/module/brand_auto', 'auto_brand=' . $brand['brand_id'])
            ];
        }

        return $this->load->view('extension/module/filter_auto_brands', $data);
    }
}