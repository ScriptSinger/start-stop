<?php

class ControllerExtensionModuleModelAuto extends Controller {

    public function index()
    {
        $this->load->language('product/category');
        $this->load->language('extension/module/filter_auto');

        $this->load->model('catalog/product');
        $this->load->model('tool/image');
        $this->load->model('extension/module/filter_auto');

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'p.sort_order';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {

            $page = 1;
        }

        if (isset($this->request->get['limit'])) {
            $limit = (int)$this->request->get['limit'];
        } else {
            $limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['limit'])) {
            $url .= '&limit=' . $this->request->get['limit'];
        }

        if (isset($this->request->get['auto_filter'])) {

            $modif_id = (int) $this->request->get['auto_filter'];

            $modif_info = $this->model_extension_module_filter_auto->getModifyInfo($modif_id);

            if ($modif_info) {
                $modif_name = $modif_info['brand_name'] . ' '
                    . $modif_info['model_name'] . ' '
                    . $modif_info['modif_name'];

                $data['breadcrumbs'][] = array(
                    'text' => $modif_name,
                    'href' => $this->url->link('extension/module/model_auto', 'auto_filter=' . $modif_id . $url)
                );
            }
        } else {
            $modif_id = '';
        }

        if (isset($this->request->get['auto_model'])) {

            $model_id = (int) $this->request->get['auto_model'];

            $model_info = $this->model_extension_module_filter_auto->getModelInfo($model_id);

            if ($model_info) {
                $data['breadcrumbs'][] = array(
                    'text' => $model_info['brand_name'],
                    'href' => $this->url->link('extension/module/brand_auto', 'auto_brand=' . $model_info['brand_id'] . $url)
                );

                $data['breadcrumbs'][] = array(
                    'text' => $model_info['model_name'],
                    'href' => ''
                );
            }
        } else {
            $model_id = '';
        }

        if (isset($this->request->get['auto_brand'])) {
            $brand_id = (int) $this->request->get['auto_brand'];

            $brand_info = $this->model_extension_module_filter_auto->getBrandInfo($brand_id);

            if ($brand_info) {
                $brand_name = $brand_info['brand_name'];

                $data['breadcrumbs'][] = array(
                    'text' => $brand_name,
                    'href' => $this->url->link('extension/module/model_auto', 'auto_brand=' . $brand_id . $url)
                );
            }
        } else {
            $brand_id = '';
        }

        if (isset($modif_info) || isset($model_info) || isset($brand_info)) {

            $description = '';
            $seo_title = '';
            $html_h1 = '';
            $seo_description = '';

            if (isset($this->request->get['auto_filter'])) {
                
                $auto_meta = $this->language->get('text_parts') .
                $this->language->get('text_for') . ' '
                . $modif_info['brand_name'] . ' '
                . $modif_info['model_name'] . ' '
                . $modif_info['modif_name'];        
            } elseif(isset($this->request->get['auto_model'])) {
                
                $auto_meta = $this->language->get('text_parts') .
                $this->language->get('text_for') . ' '
                . $model_info['brand_name'] . ' '
                . $model_info['model_name'];

                $this->load->model('setting/setting');

                $filter_auto_setting = $this->model_setting_setting->getSetting('filter_auto');
                $filter_auto_seo_setting = (isset($filter_auto_setting['filter_auto_model_description'][(int) $this->config->get('config_language_id')])) ? $filter_auto_setting['filter_auto_model_description'][(int) $this->config->get('config_language_id')] : [];

                if (isset($filter_auto_seo_setting)) {
                    if (!empty($filter_auto_seo_setting['description'])) {
                        $description = str_replace(
                            ['%brand%', '%model%'],
                            [$model_info['brand_name'], $model_info['model_name']],
                            $filter_auto_seo_setting['description']
                        );
                    }

                    if (!empty($filter_auto_seo_setting['meta_title'])) {
                        $seo_title = str_replace(
                            ['%brand%', '%model%'],
                            [$model_info['brand_name'], $model_info['model_name']],
                            $filter_auto_seo_setting['meta_title']
                        );
                    }

                    if (!empty($filter_auto_seo_setting['html_h1'])) {
                        $html_h1 = str_replace(
                            ['%brand%', '%model%'],
                            [$model_info['brand_name'], $model_info['model_name']],
                            $filter_auto_seo_setting['html_h1']
                        );
                    }

                    if (!empty($filter_auto_seo_setting['meta_description'])) {
                        $seo_description = str_replace(
                            ['%brand%', '%model%'],
                            [$model_info['brand_name'], $model_info['model_name']],
                            $filter_auto_seo_setting['meta_description']
                        );
                    }
                }    
            } elseif (isset($this->request->get['auto_brand'])) {
                
                $auto_meta = $this->language->get('text_parts') .
                $this->language->get('text_for') . ' '
                . $brand_info['brand_name'];    
            }

            $this->document->setTitle((isset($seo_title)) ? $seo_title : $auto_meta);
            $this->document->setDescription((isset($seo_description)) ? : $auto_meta);

            $data['description'] = html_entity_decode($description, ENT_QUOTES, 'UTF-8');

            $data['heading_title'] = (isset($html_h1)) ? $html_h1 : $auto_meta;

            $data['text_refine'] = $this->language->get('text_refine');
            $data['text_empty'] = $this->language->get('text_empty');
            $data['text_quantity'] = $this->language->get('text_quantity');
            $data['text_manufacturer'] = $this->language->get('text_manufacturer');
            $data['text_model'] = $this->language->get('text_model');
            $data['text_price'] = $this->language->get('text_price');
            $data['text_tax'] = $this->language->get('text_tax');
            $data['text_points'] = $this->language->get('text_points');
            $data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));
            $data['text_sort'] = $this->language->get('text_sort');
            $data['text_limit'] = $this->language->get('text_limit');

            $data['button_cart'] = $this->language->get('button_cart');
            $data['button_wishlist'] = $this->language->get('button_wishlist');
            $data['button_compare'] = $this->language->get('button_compare');
            $data['button_continue'] = $this->language->get('button_continue');
            $data['button_list'] = $this->language->get('button_list');
            $data['button_grid'] = $this->language->get('button_grid');

            $data['compare'] = $this->url->link('product/compare');

            // Categories
            $data['categories'] = [];

            if (isset($this->request->get['auto_filter'])) {
                $results = $this->model_extension_module_filter_auto->getCategories($modif_id);
            } elseif (isset($this->request->get['auto_model'])) {
                $results = $this->model_extension_module_filter_auto->getCategoriesModel($model_id);    
            } elseif (isset($this->request->get['auto_brand'])) {
                $results = $this->model_extension_module_filter_auto->getCategoriesBrand($brand_id);
            }

            foreach ($results as $result) {
                if (isset($this->request->get['auto_filter'])) {
                    $url_filter = '&auto_filter=' . $modif_id; 
                } elseif (isset($this->request->get['auto_model'])) {
                    $url_filter = '&auto_model=' . $model_id;    
                } elseif(isset($this->request->get['auto_brand'])) {
                    $url_filter = '&auto_brand=' . $brand_id;
                } else {
                    $url_filter = '';
                }

                $data['categories'][] = array(
                    'name' => $result['name'],
                    'href' => $this->url->link('product/category', 'path=' . $result['category_id'] . $url_filter),
                    'thumb' => ($result['image']) ? $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height')) : ''
                );
            }

            // Products
            $data['products'] = [];

            $filter_data = [
                'filter_auto'        => $modif_id,
                'filter_model'       => $model_id,
                'filter_brand'       => $brand_id,
                'sort'               => $sort,
                'order'              => $order,
                'start'              => ($page - 1) * $limit,
                'limit'              => $limit
            ];

            $product_total = $this->model_extension_module_filter_auto->getTotalProducts($filter_data);

            $results = $this->model_extension_module_filter_auto->getProducts($filter_data);

            foreach ($results as $result) {
                if ($result['image']) {
                    $image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                }

                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                } else {
                    $price = false;
                }

                if ((float)$result['special']) {
                    $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                } else {
                    $special = false;
                }

                if ($this->config->get('config_tax')) {
                    $tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
                } else {
                    $tax = false;
                }

                if ($this->config->get('config_review_status')) {
                    $rating = (int)$result['rating'];
                } else {
                    $rating = false;
                }

                $data['products'][] = array(
                    'product_id'  => $result['product_id'],
                    'thumb'       => $image,
                    'name'        => $result['name'],
                    'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                    'price'       => $price,
                    'special'     => $special,
                    'tax'         => $tax,
                    'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
                    'rating'      => $result['rating'],
                    'href'        => $this->url->link('product/product', '&product_id=' . $result['product_id'])
                );
            }

            // Sorting
            $url = '';

            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

            $data['sorts'] = array();

            if (isset($this->request->get['auto_filter'])) {
                $url_auto_sort = 'auto_filter=' . $modif_id;
            } elseif (isset($this->request->get['auto_model'])) {
                $url_auto_sort = 'auto_model=' . $model_id;
            } elseif (isset($this->request->get['auto_brand'])) {
                $url_auto_sort = 'auto_brand=' . $brand_id;
            }

            $data['sorts'][] = array(
                'text'  => $this->language->get('text_default'),
                'value' => 'p.sort_order-ASC',
                'href'  => $this->url->link('extension/module/model_auto', $url_auto_sort. '&sort=p.sort_order&order=ASC' . $url)
            );

            $data['sorts'][] = array(
                'text'  => $this->language->get('text_name_asc'),
                'value' => 'pd.name-ASC',
                'href'  => $this->url->link('extension/module/model_auto', $url_auto_sort. '&sort=pd.name&order=ASC' . $url)
            );

            $data['sorts'][] = array(
                'text'  => $this->language->get('text_name_desc'),
                'value' => 'pd.name-DESC',
                'href'  => $this->url->link('extension/module/model_auto', $url_auto_sort . '&sort=pd.name&order=DESC' . $url)
            );

            $data['sorts'][] = array(
                'text'  => $this->language->get('text_price_asc'),
                'value' => 'p.price-ASC',
                'href'  => $this->url->link('extension/module/model_auto', $url_auto_sort . '&sort=p.price&order=ASC' . $url)
            );

            $data['sorts'][] = array(
                'text'  => $this->language->get('text_price_desc'),
                'value' => 'p.price-DESC',
                'href'  => $this->url->link('extension/module/model_auto', $url_auto_sort . '&sort=p.price&order=DESC' . $url)
            );

            if ($this->config->get('config_review_status')) {
                $data['sorts'][] = array(
                    'text'  => $this->language->get('text_rating_desc'),
                    'value' => 'rating-DESC',
                    'href'  => $this->url->link('extension/module/model_auto', $url_auto_sort . '&sort=rating&order=DESC' . $url)
                );

                $data['sorts'][] = array(
                    'text'  => $this->language->get('text_rating_asc'),
                    'value' => 'rating-ASC',
                    'href'  => $this->url->link('extension/module/model_auto', $url_auto_sort . '&sort=rating&order=ASC' . $url)
                );
            }

            $data['sorts'][] = array(
                'text'  => $this->language->get('text_model_asc'),
                'value' => 'p.model-ASC',
                'href'  => $this->url->link('extension/module/model_auto', $url_auto_sort . '&sort=p.model&order=ASC' . $url)
            );

            $data['sorts'][] = array(
                'text'  => $this->language->get('text_model_desc'),
                'value' => 'p.model-DESC',
                'href'  => $this->url->link('extension/module/model_auto', $url_auto_sort . '&sort=p.model&order=DESC' . $url)
            );

            // Limits
            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            $data['limits'] = array();

            $limits = array_unique(array($this->config->get($this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));

            sort($limits);

            foreach($limits as $value) {
                $data['limits'][] = array(
                    'text'  => $value,
                    'value' => $value,
                    'href'  => $this->url->link('extension/module/model_auto', $url_auto_sort . $url . '&limit=' . $value)
                );
            }

            // Paginations
            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

            $pagination = new Pagination();
            $pagination->total = $product_total;
            $pagination->page = $page;
            $pagination->limit = $limit;
            $pagination->url = $this->url->link('extension/module/model_auto', $url_auto_sort . $url . '&page={page}');

            $data['pagination'] = $pagination->render();

            $data['results'] = sprintf(
                $this->language->get('text_pagination'),
                ($product_total) ? (($page - 1) * $limit) + 1 : 0,
                ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit)
            );

            // http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
            if ($page == 1) {
                $this->document->addLink($this->url->link('extension/module/model_auto', $url_auto_sort, true), 'canonical');
            } elseif ($page == 2) {
                $this->document->addLink($this->url->link('extension/module/model_auto', $url_auto_sort, true), 'prev');
            } else {
                $this->document->addLink($this->url->link('extension/module/model_auto', $url_auto_sort . '&page='. ($page - 1), true), 'prev');
            }

            if ($limit && ceil($product_total / $limit) > $page) {
                $this->document->addLink($this->url->link('extension/module/model_auto', $url_auto_sort . '&page='. ($page + 1), true), 'next');
            }

            $data['sort'] = $sort;
            $data['order'] = $order;
            $data['limit'] = $limit;

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('extension/module/model_auto', $data));
        } else {
            $url = '';

            if (isset($this->request->get['auto_filter'])) {
                $url .= '&auto_filter=' . $this->request->get['auto_filter'];
            } elseif (isset($this->request->get['auto_model'])) {
                $url .= '&auto_model=' . $this->request->get['auto_model'];
            } elseif (isset($this->request->get['auto_brand'])) {
                $url .= '&auto_brand=' . $this->request->get['auto_brand'];
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

            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_error'),
                'href' => $this->url->link('extension/module/filter_auto_main', $url)
            );

            $this->document->setTitle($this->language->get('text_error'));

            $data['heading_title'] = $this->language->get('text_error');

            $data['text_error'] = $this->language->get('text_error');

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

    private function categories()
    {
        $this->load->model('catalog/category');
        $this->load->model('extension/module/filter_auto');

        $data['categories'] = [];

        if (isset($this->request->get['auto_filter'])) {
            $results = $this->model_extension_module_filter_auto->getCategories($this->request->get['auto_filter']);
        } elseif (isset($this->request->get['auto_model'])) {
            $results = $this->model_extension_module_filter_auto->getCategoriesModel($this->request->get['auto_model']);
        } elseif (isset($this->request->get['auto_brand'])) {
            $results = $this->model_extension_module_filter_auto->getCategoriesBrand($this->request->get['auto_brand']);
        }

        foreach ($results as $result) {
            if (isset($this->request->get['auto_filter'])) {
                $url_filter = '&auto_filter=' . $this->request->get['auto_filter'];
            } elseif (isset($this->request->get['auto_model'])) {
                $url_filter = '&auto_model=' . $this->request->get['auto_model'];
            } elseif (isset($this->request->get['auto_brand'])) {
                $url_filter = '&auto_brand=' . $this->request->get['auto_brand'];
            } else {
                $url_filter = '';
            }

            $data['categories'][] = array(
                'name' => $result['name'],
                'href' => $this->url->link('product/category', 'path=' . $result['parent_id'] . '_' . $result['category_id'] . $url_filter),
                'thumb' => ($result['image']) ? $this->model_tool_image->resize($result['image'], $this->config->get($this->config->get('config_theme') . '_image_category_width'), $this->config->get($this->config->get('config_theme') . '_image_category_height')) : ''
            );
        }
    }
}