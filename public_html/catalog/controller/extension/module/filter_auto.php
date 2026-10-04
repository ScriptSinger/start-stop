<?php

class ControllerExtensionModuleFilterAuto extends Controller
{
    public function index($setting)
    {
        $this->load->model('catalog/category');
        $this->load->model('extension/module/filter_auto');
        $this->load->model('tool/image');

        $this->load->language('extension/module/filter_auto');

        if (isset($this->request->get['path'])) {
            $parts = explode('_', (string)$this->request->get['path']);
            $data['category_url'] = htmlspecialchars_decode($this->url->link('product/category', 'path=' . $this->request->get['path']));
        } else {
            $parts = array();
            $data['category_url'] = '';
        }

        $category_id = end($parts);

        // Search
        if (isset($this->request->get['search'])) {
            $search = $this->request->get['search'];
        } else {
            $search = '';
        }

        if (isset($this->request->get['description'])) {
            $description = $this->request->get['description'];
        } else {
            $description = '';
        }

        if (isset($this->request->get['category_id'])) {
            $search_category_id = $this->request->get['category_id'];
        } else {
            $search_category_id = 0;
        }

        if (isset($this->request->get['sub_category'])) {
            $sub_category = $this->request->get['sub_category'];
        } else {
            $sub_category = '';
        }

        $data['auto_search'] = json_encode([
            'search' => $search,
            'description' => $description,
            'category_id' => $search_category_id,
            'sub_category' => $sub_category
        ]);

        $data['filter_auto_type'] = (int) $setting['filter_auto_type'];
        $data['show_year'] = $this->config->get('filter_auto_show_year');

        if ($setting['filter_auto_type'] == 1) {
            $design_filter = '_h';
        } else {
            $design_filter = '_v';
        }

        $filter_auto_attr = $this->config->get('filter_auto_attr') ?: [];

        $data['filter_manufacturers'] = '';
        $data['filter_attributes'] = '';
        $data['filter_data'] = [];
        $data['path'] = '';

        $category_info = $this->model_catalog_category->getCategory($category_id);

        $data['brands'] = [];

        if (isset($this->request->get['auto_filter'])) {
            $filter_modif = $this->request->get['auto_filter'];
            $data['filter_data']['modif'] = $filter_modif;
        } elseif (isset($this->session->data['auto_filter'])) {
            $filter_modif = $this->session->data['auto_filter'];
            $data['filter_data']['modif'] = $filter_modif;
        } else {
            $filter_modif = '';
        }

        if (isset($this->request->get['auto_model'])) {
            $filter_model = $this->request->get['auto_model'];
            $data['filter_data']['model'] = $filter_model;
        } elseif (isset($this->session->data['auto_model'])) {
            $filter_model = $this->session->data['auto_model'];
            $data['filter_data']['model'] = $filter_model;
        } else {
            $filter_model = '';
        }

        if ($category_info) {
            $params = '';

            if (isset($this->request->get['manufacturer'])) {
                $filter_manufacturer = $this->request->get['manufacturer'];
            } else {
                $filter_manufacturer = '';
            }

            if (isset($this->request->get['attribute'])) {
                $filter_attribute = $this->request->get['attribute'];
            } else {
                $filter_attribute = '';
            }

            if (isset($this->request->get['sort'])) {
                $data['filter_data']['sort'] = $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $data['filter_data']['order'] = $this->request->get['order'];
            }

            if (isset($this->request->get['limit'])) {
                $data['filter_data']['limit'] = $this->request->get['limit'];
            }

            $data['path'] = $this->request->get['path'];

            $filter_data = [
                'filter_category_id' => $category_info['category_id'],
                'filter_modif' => $filter_modif,
                'filter_model' => $filter_model,
                'filter_manufacturer' => $filter_manufacturer,
                'filter_attribute' => $filter_attribute,
                'filter_attr_show' => $filter_auto_attr
            ];

            $data['auto_path_category'] = $this->request->get['path'];
            $data['auto_url_parameters'] = $params;

            $data['category_id'] = $category_id;

            $data['filter_manufacturers'] = [];
            $data['filter_attributes'] = [];

            if (($filter_modif || $filter_model) || empty($data['brands'])) {
                $manufacturers = $this->model_extension_module_filter_auto->getManufacturers($filter_data);
                $manufacturers_total = $this->model_extension_module_filter_auto->getManufacturersTotal($filter_data);

                foreach ($manufacturers as $manufacturer) {
                    $data['filter_manufacturers'][] = [
                        'manufacturer_id' => $manufacturer['manufacturer_id'],
                        'name' => $manufacturer['name'],
                        'total' => (array_key_exists($manufacturer['manufacturer_id'], $manufacturers_total)) ? $manufacturers_total[$manufacturer['manufacturer_id']] : 0,
                    ];
                }

                if (count($filter_auto_attr)) {
                    $data['filter_attributes'] = $this->model_extension_module_filter_auto->getAttributes($filter_data);
                }

                if (isset($this->request->get['manufacturer']) && !empty($this->request->get['manufacturer'])) {
                    $data['filter_data']['manufacturer'] = $this->request->get['manufacturer'];

                    $data['manufacturer_selected'] = explode(',', $this->request->get['manufacturer']);
                } else {
                    $data['manufacturer_selected'] = [];
                }

                if (isset($this->request->get['attribute']) && !empty($this->request->get['attribute'])) {
                    $data['filter_data']['attribute'] = $this->request->get['attribute'];

                    $data['attribute_selected'] = explode(',', $this->request->get['attribute']);
                    $data['attr_group_selected'] = $this->selectAttrId($data['attribute_selected'], $data['filter_attributes']);

                } else {
                    $data['attribute_selected'] = [];
                    $data['attr_group_selected'] = [];
                }
            }

            $resultBrands = $this->model_extension_module_filter_auto->getBrands($category_id);

        } else {
            $data['category_id'] = null;
            $data['auto_path_category'] = null;
            $data['auto_url_parameters'] = null;

            $resultBrands = $this->model_extension_module_filter_auto->getBrands();
        }

        foreach ($resultBrands as $brand) {
            $data['brands'][] = [
                'brand_id' => $brand['brand_id'],
                'brand_name' => $brand['brand_name'],
                'brand_img' => $this->model_tool_image->resize($brand['brand_img'], 24, 24)
            ];
        }

        if (isset($this->request->get['auto_filter'])) {
            $modify_info = $this->model_extension_module_filter_auto->getModifyInfo($this->request->get['auto_filter']);
        }  elseif (isset($this->session->data['auto_filter'])) {
            $modify_info = $this->model_extension_module_filter_auto->getModifyInfo($this->session->data['auto_filter']);
        } elseif (isset($this->request->get['auto_model'])) {
            $model_info = $this->model_extension_module_filter_auto->getModelInfo($this->request->get['auto_model']);
        } elseif (isset($this->session->data['auto_model'])) {
            $model_info = $this->model_extension_module_filter_auto->getModelInfo($this->session->data['auto_model']);
        }

        if (!empty($modify_info)) {
            $data['current_brand'] = $modify_info['brand_id'];
            $data['current_model'] = $modify_info['model_id'];
            $data['current_modify'] = (int) $filter_modif;
            $data['current_year'] = isset($this->request->get['auto_year'])
                ? (int) $this->request->get['auto_year']
                : '';
        } elseif (!empty($model_info)) {
            $data['current_brand'] = $model_info['brand_id'];
            $data['current_model'] = (int) $filter_model;
            $data['current_modify'] = 'all';
        } else {
            $data['current_brand'] = '';
            $data['current_model'] = '';
            $data['current_modify'] = '';
        }

        $data['filter_active'] = $this->isFilterActive();

        $this->load->library('mobiledetect');
        $data['is_mobile'] = $this->mobiledetect->isMobile();

        $this->document->addStyle('catalog/view/theme/default/stylesheet/filter_auto.css');
        $this->document->addStyle('catalog/view/theme/default/stylesheet/jquery.mCustomScrollbar.css');
        $this->document->addStyle('catalog/view/theme/default/stylesheet/select2.min.css');
        $this->document->addScript('catalog/view/javascript/jquery.mCustomScrollbar.min.js');
        $this->document->addScript('catalog/view/javascript/select2.min.js');

        $data['design_filter'] = $setting['filter_auto_type'];

        if ($data['is_mobile']) {
            $data['filter_include'] = $this->load->view('extension/module/filter_auto_v', $data);
            return $this->load->view('extension/module/filter_auto_mobile', $data);
        }

        return $this->load->view('extension/module/filter_auto' . $design_filter, $data);
    }

    public function models()
    {
        $result = [];

        if (isset($this->request->post['brand_id'])) {
            $brand_id = $this->request->post['brand_id'];
        } else {
            $brand_id = null;
        }

        if (isset($this->request->post['cat_id'])) {
            $category_id = $this->request->post['cat_id'];
        } else {
            $category_id = null;
        }

        if ($brand_id) {
            $this->load->model('extension/module/filter_auto');

            $result['models'] = $this->model_extension_module_filter_auto->getModels($brand_id, $category_id);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));
    }

    public function years()
    {
        $result = [];

        $model_id = null;

        if (isset($this->request->post['model_id'])) {
            $model_id = (int) $this->request->post['model_id'];
        }

        $category_id = null;

        if (isset($this->request->post['cat_id'])) {
            $category_id = (int) $this->request->post['cat_id'];
        }

        if ($model_id) {
            $this->load->model('extension/module/filter_auto');
            $result['years'] = $this->model_extension_module_filter_auto->getYears($model_id, $category_id);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));
    }

    public function modifies()
    {
        $result = [];
        $filter = [
            'model_id' => null,
            'year' => null,
            'category_id' => null,
        ];

        if (isset($this->request->post['model_id'])) {
            $filter['model_id'] = $this->request->post['model_id'];
        }

        if (isset($this->request->post['year'])) {
            $filter['year'] = $this->request->post['year'];
        }

        if (isset($this->request->post['cat_id'])) {
            $filter['category_id'] = $this->request->post['cat_id'];
        }

        if ($filter['model_id']) {
            $this->load->model('extension/module/filter_auto');

            $result['modifies'] = $this->model_extension_module_filter_auto->getModifies($filter);
            $result['num_fileds'] = $this->numberFields($result['modifies']);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));
    }

    public function filter()
    {
        $result = ['filter' => false];

        $url = '';
        $search_url = '';

        if (!empty($this->request->post['modif_id']) && $this->request->post['modif_id'] !== 'all') {
            $modif_id = (int) $this->request->post['modif_id'];

            if (!empty($this->config->get('filter_auto_remember'))) {
                unset($this->session->data['auto_filter']);
                $this->session->data['auto_filter'] = $modif_id;
            }

            $url = '&auto_filter=' . $modif_id;
        }

        if (!empty($this->request->post['year'])) {
            $year = (int) $this->request->post['year'];

            $url .= '&auto_year=' . $year;
        }

        if (isset($this->request->post['model_id']) && empty($this->request->post['modif_id'])) {
            $model_id = (int) $this->request->post['model_id'];

            if (!empty($this->config->get('filter_auto_remember'))) {
                unset($this->session->data['auto_filter']);
                unset($this->session->data['auto_model']);
                $this->session->data['auto_model'] = $model_id;
            }

            $url = '&auto_model=' . $model_id;
        }

        if (isset($this->request->post['path'])) {
            $path = $this->request->post['path'];
        } else {
            $path = null;
        }

        if (isset($this->request->post['search']['search'])) {
            $search = $this->request->post['search']['search'];
        } else {
            $search = null;
        }

        if (isset($this->request->post['search']['description'])) {
            $search_url .= '&description=' . $this->request->post['search']['description'];
        }

        if (isset($this->request->post['search']['category_id'])) {
            $search_url .= '&category_id=' . $this->request->post['search']['category_id'];
        }

        if (isset($this->request->post['search']['sub_category'])) {
            $search_url .= '&sub_category=' . $this->request->post['search']['sub_category'];
        }

        if (!empty($this->request->post['sort'])) {
            $url .= '&sort=' . $this->request->post['sort'];
        }

        if (!empty($this->request->post['order'])) {
            $url .= '&order=' . $this->request->post['order'];
        }

        if (!empty($this->request->post['limit'])) {
            $url .= '&limit=' . $this->request->post['limit'];
        }

        if ($url && $path) {
            $url = str_replace('&amp;', '&', $url);
            $result['filter'] = htmlspecialchars_decode($this->url->link('product/category', 'path=' . $path . $url));
        } elseif ($url && $search) {
            $result['filter'] = htmlspecialchars_decode($this->url->link('product/search', 'search=' . $search . $url . $search_url));
        } else {
            $result['filter'] = htmlspecialchars_decode($this->url->link('extension/module/model_auto', $url, true));
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));
    }

    public function location()
    {
        $result = ['filter' => false];
        $params_c = '';
        $params_m = '';
        $params_a = '';
        $params = '';

        if ($this->request->server['REQUEST_METHOD'] === 'POST') {

            if(isset($this->request->post['filter'])) {
                $filter_data = $this->request->post['filter'];
            } else {
                $filter_data = [];
            }

            if (array_key_exists('modif', $filter_data)) {
                $params_c .= '&auto_filter=' . $filter_data['modif'];
            }

            if (array_key_exists('model', $filter_data)) {
                $params_c .= '&auto_model=' . $filter_data['model'];
            }

            if ($this->request->post['param'] === 'manuf' && !empty($this->request->post['value'])) {
                $params_m .= '&manufacturer=' . $this->request->post['value'];
            } elseif ($this->request->post['param'] !== 'manuf' && array_key_exists('manufacturer', $filter_data)) {
                $params_m .= '&manufacturer=' . $filter_data['manufacturer'];
            } else {
                $params_m = '';
            }

            if ($this->request->post['param'] === 'attribute' && !empty($this->request->post['value'])) {
                $params_a .= '&attribute=' . $this->request->post['value'];
            } elseif ($this->request->post['param'] !== 'attribute' && array_key_exists('attribute', $filter_data)) {
                $params_a .= '&attribute=' . $filter_data['attribute'];
            } else {
                $params_a = '';
            }

            if (array_key_exists('sort', $filter_data)) {
                $params .= '&sort=' . $filter_data['sort'];
            }

            if (array_key_exists('order', $filter_data)) {
                $params .= '&order=' . $filter_data['order'];
            }

            if (array_key_exists('limit', $filter_data)) {
                $params .= '&limit=' . $filter_data['limit'];
            }

            $result['filter'] = str_replace(['&amp;', '%2C'], ['&', ','], $this->url->link('product/category', 'path=' . $this->request->post['path'] . $params_c . $params_m . $params_a . $params));
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));
    }

    private function keyCustomExist($key, $string)
    {
        $arr = explode(',', $string);

        return array_key_exists($key, $arr);
    }

    public function clear()
    {
        $result = ['redirect' => false];

        if ($this->request->server['REQUEST_METHOD'] === 'POST') {
            $category_url = str_replace('&amp;', '&', $this->request->post['category']);

            if (isset($this->session->data['auto_filter']) || isset($this->session->data['auto_model'])) {
                unset($this->session->data['auto_filter'], $this->session->data['auto_model']);
            }

            if ($category_url) {
                $result = ['redirect' => $category_url];
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));
    }

    private function selectAttrId(array $atr_values, array $data)
    {
        $result = [];

        foreach ($atr_values as $atr_value) {
            foreach ($data as $key=>$value) {
                if (in_array($atr_value, array_column($value['values'], 'atr_value_id'))) {
                    $result[] = $key;
                }
            }
        }

        return $result;
    }

    private function numberFields(array $data)
    {
        $key = array_search('all', array_column($data, 'modif_name'));

        if ($key === false || ($key !== false && count($data) > 1)) {
            return 3;
        }

        return 2;
    }

    private function isFilterActive()
    {
        return (isset($this->request->get['auto_filter'])
            || isset($this->session->data['auto_filter'])
            || isset($this->request->get['auto_model'])
            || isset($this->session->data['auto_model'])
            || isset($this->request->get['manufacturer'])
            || isset($this->request->get['attribute']));
    }
}