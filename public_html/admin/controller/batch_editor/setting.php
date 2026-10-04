<?php

class ControllerBatchEditorSetting extends Controller
{
    private $error = [];
    private $json = ["success" => "", "warning" => "", "value" => ""];
    private $list = ["manufacturer_id" => ["table" => "manufacturer", "name" => "m", "field" => "name", "zero" => 1], "tax_class_id" => ["table" => "tax_class", "name" => "tc", "field" => "title", "zero" => 1], "weight_class_id" => ["table" => "weight_class_description", "name" => "wcd", "field" => "title", "lang" => 1], "length_class_id" => ["table" => "length_class_description", "name" => "lcd", "field" => "title", "lang" => 1], "stock_status_id" => ["table" => "stock_status", "name" => "ss", "field" => "name", "lang" => 1], "unit_id" => ["table" => "unit", "name" => "u", "field" => "title"], "supplier_id" => ["table" => "supplier", "name" => "s", "field" => "name", "zero" => 1]];
    private $link = ["description" => ["table" => "product_description", "list" => ["languages"], "text" => ["name", "meta_description", "meta_keyword", "description", "seo_title", "seo_h1", "tag"], "lang" => 1, "func" => 1], "category" => ["table" => "product_to_category", "list" => ["categories"], "text" => ["categories", "main_category", "none"]], "attribute" => ["table" => "product_attribute", "list" => ["languages", "attributes"], "text" => ["group", "name", "value", "none"], "lang" => 1, "func" => 1], "option" => ["table" => "product_option", "list" => [], "text" => ["required", "value", "quantity", "subtract", "price", "point", "weight", "yes", "no"], "func" => 1], "special" => ["table" => "product_special", "list" => ["customer_groups"], "text" => ["customer_group", "priority", "discount", "date_start", "date_end"]], "discount" => ["table" => "product_discount", "list" => ["customer_groups"], "text" => ["customer_group", "quantity", "priority", "discount", "date_start", "date_end"]], "related" => ["table" => "product_related", "list" => [], "text" => [], "func" => 1], "store" => ["table" => "product_to_store", "list" => ["stores"], "text" => ["default"]], "download" => ["table" => "product_to_download", "list" => ["downloads"], "text" => []], "image" => ["table" => "product_image", "list" => ["no_image"], "text" => ["image_manager", "sort_order", "clear", "path"], "func" => 1], "reward" => ["table" => "product_reward", "list" => ["customer_groups"], "text" => ["customer_group", "points"]], "layout" => ["table" => "product_to_layout", "list" => ["layouts", "stores"], "text" => ["layout", "store", "default"]]];
    private $no_edit = ["product" => ["product_id" => ["type" => "int", "size" => 11, "table" => "p"], "date_added" => ["type" => "date", "table" => "p"], "date_modified" => ["type" => "datetime", "table" => "p"]], "product_description" => ["product_id" => ["type" => "int", "size" => 11, "table" => "pd"], "language_id" => ["type" => "int", "size" => 11, "table" => "pd"]]];
    public function index()
    {
        $this->load->language("batch_editor/index");
        $this->load->language("batch_editor/setting");
        $this->load->model("batch_editor/list");
        $this->load->model("batch_editor/setting");
        if ($this->request->server["REQUEST_METHOD"] == "POST" && $this->validate()) {
            $width = (int) $this->request->post["option"]["image"]["width"];
            $height = (int) $this->request->post["option"]["image"]["height"];
            if ($width < 10 || $height < 10) {
                $width = $height = 40;
            }
            $this->request->post["option"]["image"]["width"] = $width;
            $this->request->post["option"]["image"]["height"] = $height;
            $main_category = $this->model_batch_editor_setting->getTableField("product_to_category", "main_category");
            if ($main_category) {
                $this->request->post["option"]["main_category"] = 1;
            } else {
                $this->request->post["option"]["main_category"] = 0;
            }
            if (isset($this->request->post["option"]["limit"]) && is_array($this->request->post["option"]["limit"])) {
                foreach ($this->request->post["option"]["limit"] as $key => $limit) {
                    $limit = (int) $limit;
                    if (0 < $limit) {
                        $this->request->post["option"]["limit"][$key] = $limit;
                    } else {
                        unset($this->request->post["option"]["limit"][$key]);
                    }
                }
                if (!$this->request->post["option"]["limit"]) {
                    $this->request->post["option"]["limit"] = [10];
                }
            } else {
                $this->request->post["option"]["limit"] = [10];
            }
            $option = $this->model_batch_editor_setting->get("option");
            if (isset($this->request->post["table"]["asticker_id"])) {
                $this->list["asticker_id"] = ["table" => "astickers", "name" => "ast", "field" => "name", "zero" => 1];
            }
            $this->model_batch_editor_setting->set("table", $this->request->post["table"]);
            $this->model_batch_editor_setting->set("option", $this->request->post["option"]);
            $this->model_batch_editor_setting->set("list", $this->list);
            $this->model_batch_editor_setting->set("no_edit", $this->no_edit);
            if (isset($this->request->post["seo_generator"])) {
                $this->model_batch_editor_setting->set("tool/seo_generator", $this->request->post["seo_generator"]);
            } else {
                $this->model_batch_editor_setting->set("tool/seo_generator", []);
            }
            if (isset($this->request->post["search_replace"])) {
                $this->model_batch_editor_setting->set("tool/search_replace", $this->request->post["search_replace"]);
            } else {
                $this->model_batch_editor_setting->set("tool/search_replace", []);
            }
            if (isset($this->request->post["rounding_numbers"])) {
                $this->model_batch_editor_setting->set("tool/rounding_numbers", $this->request->post["rounding_numbers"]);
            } else {
                $this->model_batch_editor_setting->set("tool/rounding_numbers", []);
            }
            if (isset($this->request->post["image_google"])) {
                $this->model_batch_editor_setting->set("tool/image_google", $this->request->post["image_google"]);
            } else {
                $this->model_batch_editor_setting->set("tool/image_google", []);
            }
            if (isset($this->request->post["filter"]) && is_array($this->request->post["filter"])) {
                foreach ($this->request->post["filter"] as $table => $data) {
                    if (isset($data["field"]) && is_array($data["field"])) {
                        foreach ($data["field"] as $key => $field) {
                            if (!$field) {
                                unset($data["field"][$key]);
                            }
                        }
                        if (!$data["field"]) {
                            unset($this->request->post["filter"][$table]);
                        }
                    } else {
                        unset($this->request->post["filter"][$table]);
                    }
                }
                $this->model_batch_editor_setting->set("filter", $this->request->post["filter"]);
            } else {
                $this->model_batch_editor_setting->set("filter", []);
            }
            if (isset($this->request->post["multilanguage"]["field"]) && is_array($this->request->post["multilanguage"]["field"])) {
                foreach ($this->request->post["multilanguage"]["field"] as $code => $data) {
                    $this->model_batch_editor_setting->delete("language/" . $code . "/field");
                    if (is_array($data)) {
                        $temp = [];
                        foreach ($data as $variable => $text) {
                            $variable = htmlspecialchars_decode($variable);
                            $variable = preg_replace("%[^a-zA-Z0-9\\_]%", "", $variable);
                            $temp[$variable] = $text;
                        }
                        $path = DIR_APPLICATION . "view/batch_editor/setting/language/" . $code;
                        if (!is_dir($path)) {
                            mkdir($path, 493);
                        }
                        $this->model_batch_editor_setting->set("language/" . $code . "/field", $temp);
                        unset($temp);
                    }
                }
            }
            $this->validateLink();
            foreach ($this->link as $link => $data) {
                if (isset($this->request->post["link"][$link]["enable"]["filter"])) {
                    $this->link[$link]["enable"]["filter"] = 1;
                }
                if (isset($this->request->post["link"][$link]["enable"]["link"])) {
                    $this->link[$link]["enable"]["link"] = 1;
                }
                if (isset($this->request->post["link"][$link]["enable"]["product"])) {
                    $this->link[$link]["enable"]["product"] = 1;
                }
            }
            $additional_link = $this->model_batch_editor_setting->getAdditionalLink();
            foreach ($additional_link as $link => $data) {
                unset($additional_link[$link]["enable"]);
                if (isset($this->request->post["link"][$link]["enable"]["filter"])) {
                    $additional_link[$link]["enable"]["filter"] = 1;
                }
                if (isset($this->request->post["link"][$link]["enable"]["link"])) {
                    $additional_link[$link]["enable"]["link"] = 1;
                }
                if (isset($this->request->post["link"][$link]["enable"]["product"])) {
                    $additional_link[$link]["enable"]["product"] = 1;
                }
                $fields = $this->model_batch_editor_setting->getTableField($link);
                $type = "standart";
                $primary_key = [];
                foreach ($fields as $field => $field_setting) {
                    if ($field_setting["key"] == "PRI") {
                        $primary_key[] = $field;
                    }
                }
                if (count($primary_key) == 2 && in_array("language_id", $primary_key)) {
                    $type = "language";
                }
                $additional_link[$link]["type"] = $type;
                $this->model_batch_editor_setting->set("link/" . $link, $additional_link[$link]);
            }
            $this->model_batch_editor_setting->set("link", $this->link);
            $this->session->data["success"] = $this->language->get("success_edit_setting");
            if (VERSION < "2.0.0.0") {
                $this->redirect($this->url->link("batch_editor/index", "user_token=" . $this->session->data["user_token"], "SSL"));
            } else {
                $this->response->redirect($this->url->link("batch_editor/index", "user_token=" . $this->session->data["user_token"], "SSL"));
            }
        }
        $this->document->addStyle("view/batch_editor/stylesheet/common.css");
        $this->document->addScript("view/batch_editor/javascript/common.js");
        $this->document->addScript("view/batch_editor/javascript/jquery.tablednd.js");
        $title = str_replace("{version}", $this->model_batch_editor_setting->getVersion(), $this->language->get("heading_title"));
        $this->document->setTitle($title);
        $this_data["heading_title"] = $this->language->get("button_setting");
        $this_data["tab_general"] = $this->language->get("tab_general");
        $this_data["tab_option"] = $this->language->get("tab_option");
        $this_data["tab_link"] = $this->language->get("tab_link");
        $this_data["tab_filter"] = $this->language->get("tab_filter");
        $this_data["text_visible"] = $this->language->get("text_visible");
        $this_data["text_name"] = $this->language->get("text_name");
        $this_data["text_table"] = $this->language->get("text_table");
        $this_data["text_field"] = $this->language->get("text_field");
        $this_data["text_type"] = $this->language->get("text_type");
        $this_data["text_size"] = $this->language->get("text_size");
        $this_data["text_image_size"] = $this->language->get("text_image_size");
        $this_data["text_add_related"] = $this->language->get("text_add_related");
        $this_data["text_del_related"] = $this->language->get("text_del_related");
        $this_data["text_counter"] = $this->language->get("text_counter");
        $this_data["text_add"] = $this->language->get("text_add");
        $this_data["text_delete"] = $this->language->get("text_delete");
        $this_data["text_link"] = $this->language->get("text_link");
        $this_data["text_product"] = $this->language->get("text_product");
        $this_data["text_additional"] = $this->language->get("text_additional");
        $this_data["text_variable"] = $this->language->get("text_variable");
        $this_data["text_value"] = $this->language->get("text_value");
        $this_data["text_language"] = $this->language->get("text_language");
        $this_data["text_variables"] = $this->language->get("text_variables");
        $this_data["text_limit"] = $this->language->get("text_limit");
        $this_data["text_filter"] = $this->language->get("text_filter");
        $this_data["text_quick_filter"] = $this->language->get("text_quick_filter");
        $this_data["text_view_categories"] = $this->language->get("text_view_categories");
        $this_data["text_price_prefix"] = $this->language->get("text_price_prefix");
        $this_data["text_column_categories"] = $this->language->get("text_column_categories");
        $this_data["text_column_attributes"] = $this->language->get("text_column_attributes");
        $this_data["text_column_options"] = $this->language->get("text_column_options");
        $this_data["text_product_image_remove"] = $this->language->get("text_product_image_remove");
        $this_data["text_yes"] = $this->language->get("text_yes");
        $this_data["text_no"] = $this->language->get("text_no");
        $this_data["text_enabled"] = $this->language->get("text_enabled");
        $this_data["text_disabled"] = $this->language->get("text_disabled");
        $this_data["text_one_side"] = $this->language->get("text_one_side");
        $this_data["text_two_side"] = $this->language->get("text_two_side");
        $this_data["text_tab"] = $this->language->get("text_tab");
        $this_data["text_list"] = $this->language->get("text_list");
        $this_data["text_data"] = $this->language->get("text_data");
        $this_data["text_apply_to"] = $this->language->get("text_apply_to");
        $this_data["text_autocomplete"] = $this->language->get("text_autocomplete");
        $this_data["text_multilanguage"] = $this->language->get("text_multilanguage");
        $this_data["text_url_alias"] = $this->language->get("text_url_alias");
        $this_data["text_keyword"] = $this->language->get("text_keyword");
        $this_data["text_dir_image"] = $this->language->get("text_dir_image");
        $this_data["text_seo_generator"] = $this->language->get("text_seo_generator");
        $this_data["text_search_replace"] = $this->language->get("text_search_replace");
        $this_data["text_yandex_translate"] = $this->language->get("text_yandex_translate");
        $this_data["text_rounding_numbers"] = $this->language->get("text_rounding_numbers");
        $this_data["text_image_google"] = $this->language->get("text_image_google");
        $this_data["text_image_bing"] = $this->language->get("text_image_bing");
        $this_data["button_save"] = $this->language->get("button_save");
        $this_data["button_cancel"] = $this->language->get("button_cancel");
        $this_data["button_setting"] = $this->language->get("button_setting");
        $this_data["button_insert"] = $this->language->get("button_insert");
        $this_data["button_remove"] = $this->language->get("button_remove");
        $this_data["breadcrumbs"] = [["text" => $this->language->get("text_home"), "href" => $this->url->link(VERSION < "2.0.0.0" ? "common/home" : "common/dashboard", "user_token=" . $this->session->data["user_token"], "SSL"), "separator" => false], ["text" => $title, "href" => $this->url->link("batch_editor/index", "user_token=" . $this->session->data["user_token"], "SSL"), "separator" => " :: "], ["text" => $this->language->get("button_setting"), "href" => $this->url->link("batch_editor/setting", "user_token=" . $this->session->data["user_token"], "SSL"), "separator" => " :: "]];
        $exclude = ["product_id", "language_id"];
        $default = ["model", "sku", "upc", "location", "quantity", "stock_status_id", "image", "manufacturer_id", "shipping", "price", "points", "tax_class_id", "date_available", "weight", "weight_class_id", "length", "width", "height", "length_class_id", "subtract", "minimum", "sort_order", "status", "viewed", "url_alias", "date_added", "date_modified", "name", "description", "meta_description", "meta_keyword", "ean", "jan", "isbn", "mpn", "meta_title", "meta_h1", "noindex"];
        if ("1.5.4" <= VERSION) {
            $default[] = "tag";
        }
        $this_data["option"] = $this->model_batch_editor_setting->get("option");
        $product = $this->model_batch_editor_setting->table("product", $exclude);
        $product_description = $this->model_batch_editor_setting->table("product_description", $exclude);
        foreach ($product as $key => $data) {
            $product[$key]["table"] = "p";
        }
        foreach ($product_description as $key => $data) {
            $product_description[$key]["table"] = "pd";
        }
        $this_data["table"] = array_merge($product, $product_description);
        $this_data["table"]["url_alias"] = ["type" => "varchar", "size" => 255, "table" => "ua"];
        if (VERSION < "1.5.4") {
            $this_data["table"]["tag"] = ["type" => "varchar", "size" => 32, "table" => "pt"];
        }
        $setting = $this->model_batch_editor_setting->get("table");
        $this_data["table"] = array_merge($setting, $this_data["table"]);
        $this_data["languages"] = $this->model_batch_editor_list->getLanguages();
        foreach ($this_data["table"] as $field => $data) {
            if (in_array($field, $default)) {
                $this_data["table"][$field]["text"] = $this->language->get("text_" . $field);
            } else {
                foreach ($this_data["languages"] as $code => $language) {
                    if (isset($setting[$field]["text"][$code])) {
                        $this_data["table"][$field]["text"][$code] = $setting[$field]["text"][$code];
                    } else {
                        $this_data["table"][$field]["text"][$code] = $field;
                    }
                }
            }
            if (($data["type"] == "int" || $data["type"] == "decimal") && !isset($this->list[$field])) {
                $this_data["table"][$field]["calc"] = 1;
            }
            if (isset($setting[$field]["enable"])) {
                $this_data["table"][$field]["enable"] = $setting[$field]["enable"];
            } else {
                $this_data["table"][$field]["enable"] = 0;
            }
        }
        $this_data["url_action"] = $this->url->link("batch_editor/setting", "user_token=" . $this->session->data["user_token"], "SSL");
        $this_data["url_cancel"] = $this->url->link("batch_editor/index", "user_token=" . $this->session->data["user_token"], "SSL");
        if (isset($this->error["warning"])) {
            $this_data["warning"] = $this->error["warning"];
        } else {
            $this_data["warning"] = "";
        }
        $this_data["setting"]["link"] = $this->model_batch_editor_setting->get("link");
        if (!$this_data["setting"]["link"]) {
            $this->validateLink();
            $this_data["setting"]["link"] = $this->link;
        }
        foreach ($this_data["setting"]["link"] as $link => $data) {
            $this_data["setting"]["link"][$link]["description"] = $this->language->get("text_" . $link);
        }
        $this_data["setting"]["additional_link"] = [];
        $admin_language = $this->config->get("config_admin_language");
        $this_data["setting"]["additional_link"] = $this->model_batch_editor_setting->getAdditionalLink();
        foreach ($this_data["setting"]["additional_link"] as $link => $data) {
            if (isset($data["description"][$admin_language])) {
                $this_data["setting"]["additional_link"][$link]["description"] = $data["description"][$admin_language];
            } else {
                $this_data["setting"]["additional_link"][$link]["description"] = "text_" . $link;
            }
        }
        $this_data["tables"] = $this->model_batch_editor_setting->getTableWithProductId();
        $this_data["filter"] = $this->model_batch_editor_setting->get("filter");
        $this_data["multilanguage"] = [];
        $this_data["variables"] = [];
        foreach ($this_data["languages"] as $code => $language) {
            $this_data["multilanguage"]["field"][$code] = $this->model_batch_editor_setting->get("language/" . $code . "/field");
            if ($this_data["multilanguage"]["field"][$code]) {
                foreach ($this_data["multilanguage"]["field"][$code] as $variable => $text) {
                    $this_data["variables"][$variable] = $variable;
                }
            }
        }
        $this_data["button_activate"] = $this->language->get("button_activate");
		$this_data["activate"] = true;
        $this_data["success_activate_extension"] = $this->language->get("success_activate_extension");
        $this_data["error_activate_extension"] = $this->language->get("error_activate_extension");
        $this_data["seo_generator"] = $this->model_batch_editor_setting->get("tool/seo_generator");
        if (!isset($this_data["seo_generator"]["field"])) {
            $this_data["seo_generator"]["field"] = [];
        }
        if (!isset($this_data["seo_generator"]["apply_to"])) {
            $this_data["seo_generator"]["apply_to"] = [];
        }
        $this_data["search_replace"] = $this->model_batch_editor_setting->get("tool/search_replace");
        if (!isset($this_data["search_replace"]["apply_to"])) {
            $this_data["search_replace"]["apply_to"] = [];
        }
        if (!isset($this_data["search_replace"]["field"])) {
            $this_data["search_replace"]["field"] = [];
        }
        $this_data["rounding_numbers"] = $this->model_batch_editor_setting->get("tool/rounding_numbers");
        if (!isset($this_data["rounding_numbers"]["apply_to"])) {
            $this_data["rounding_numbers"]["apply_to"] = [];
        }
        $this_data["image_google"] = $this->model_batch_editor_setting->get("tool/image_google");
        if (!isset($this_data["image_google"]["keyword"])) {
            $this_data["image_google"]["keyword"] = [];
        }
        $this_data["list"] = $this->model_batch_editor_setting->get("list");
        if (!$this_data["list"]) {
            $this_data["list"] = $this->list;
        }
        $this_data["user_token"] = $this->session->data["user_token"];
        $this_template = "batch_editor/setting";
        $this->setOutput($this_template, $this_data, true);
    }
    public function addLink()
    {
        $this->load->language("batch_editor/setting");
        $this_data["text_description"] = $this->language->get("text_description");
        $this_data["text_table"] = $this->language->get("text_table");
        $this_data["text_save"] = $this->language->get("text_save");
        $this_data["text_none"] = $this->language->get("text_none");
        $this->load->model("batch_editor/setting");
        $this->load->model("batch_editor/list");
        $this_data["languages"] = $this->model_batch_editor_list->getLanguages();
        $exclude_tables = [DB_PREFIX . "ocfilter_option_value_to_product", DB_PREFIX . "ocfilter_option_value_to_product_description", DB_PREFIX . "product", DB_PREFIX . "product_filter", DB_PREFIX . "product_option_value", DB_PREFIX . "order_product", DB_PREFIX . "return"];
        foreach ($this->link as $value) {
            $exclude_tables[] = DB_PREFIX . $value["table"];
        }
        $additional_link = $this->model_batch_editor_setting->getAdditionalLink();
        foreach ($additional_link as $value) {
            $exclude_tables[] = DB_PREFIX . $value["table"];
        }
        $tables = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "%'")->rows;
        $this_data["tables"] = [];
        foreach ($tables as $data) {
            foreach ($data as $table) {
                if (!in_array($table, $exclude_tables)) {
                    $product_id = $this->db->query("SHOW COLUMNS FROM `" . $table . "` LIKE 'product_id'")->rows;
                    if ($product_id) {
                        $this_data["tables"][] = preg_replace("/^" . DB_PREFIX . "/", "", $table);
                    }
                }
            }
        }
        $this->setOutput("batch_editor/link/form", $this_data);
    }
    public function saveLink()
    {
        $this->load->language("batch_editor/setting");
        $this->load->model("batch_editor/list");
        if (isset($this->request->post["link"])) {
            $link = $this->request->post["link"];
        } else {
            $link = [];
        }
        $languages = $this->model_batch_editor_list->getLanguages();
        foreach ($languages as $code => $language) {
            if (!isset($link["description"][$code]) || isset($link["description"][$code]) && !$link["description"][$code]) {
                $this->error["warning"] = $this->language->get("error_link_description");
            }
        }
        if (!isset($link["table"])) {
            $this->error["warning"] = $this->language->get("error_link_table");
        } else {
            if (!$link["table"]) {
                $this->error["warning"] = $this->language->get("error_link_table");
            } else {
                foreach ($this->link as $data) {
                    if ($link["table"] == $data["table"]) {
                        $this->error["warning"] = $this->language->get("error_link_table");
                    }
                }
            }
        }
        if ($this->validate()) {
            $this->load->model("batch_editor/setting");
            $this->model_batch_editor_setting->set("link/" . $link["table"], $link);
        }
        if (isset($this->error["warning"])) {
            $this->json["warning"] = $this->error["warning"];
        } else {
            $this->json["value"] = $this->config->get("config_admin_language");
            $this->json["success"] = $this->language->get("success_edit_link");
        }
        echo json_encode($this->json);
    }
    public function deleteLink()
    {
        $this->load->language("batch_editor/setting");
        if ($this->validate()) {
            if (isset($this->request->post["link"])) {
                $link = $this->request->post["link"];
            } else {
                $link = "";
            }
            $file = DIR_APPLICATION . "view/batch_editor/setting/link/" . $link . ".ini";
            if (file_exists($file)) {
                unlink($file);
            }
        }
        if (isset($this->error["warning"])) {
            $this->json["warning"] = $this->error["warning"];
        } else {
            $this->json["success"] = $this->language->get("success_delete_link");
        }
        echo json_encode($this->json);
    }
    public function getFilterField()
    {
        $fields = [];
        if (isset($this->request->post["table"])) {
            $table = $this->request->post["table"];
        } else {
            $table = "";
        }
        if ($table) {
            $this->load->model("batch_editor/setting");
            $temp = $this->model_batch_editor_setting->getTableField($table);
            foreach ($temp as $field => $setting) {
                $fields[] = $field;
            }
            unset($temp);
        }
        echo json_encode($fields);
    }
    private function validate()
    {
        $version = '0.4.8'; // set version module 0.4.6, 0.4.7, 0.4.8
        $domain = preg_replace('/^www\\./', '', $_SERVER['HTTP_HOST']);
        $request = '&module=batch_editor&version=' . $version . '&domain=' . $domain;
        $hash = sha1(sha1(sha1($request)));
        $this->request->post["option"]["hash"] = $hash;
        if (!$this->user->hasPermission("modify", "batch_editor/setting")) {
            $this->error["warning"] = $this->language->get("error_permission");
        }
        return !$this->error ? true : false;
    }
    private function validateLink()
    {
        if ("1.5.5" <= VERSION) {
            $this->link["filter"] = ["table" => "product_filter", "list" => [], "text" => [], "func" => 1];
        }
        $result = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "setting` WHERE `Field` = 'group'");
        if ($result->num_rows) {
            $query = $this->db->query("SELECT s.setting_id FROM `" . DB_PREFIX . "setting` s WHERE s.group = 'ocfilter'");
        } else {
            $query = $this->db->query("SELECT s.setting_id FROM `" . DB_PREFIX . "setting` s WHERE s.code = 'ocfilter'");
        }
        if ($query->num_rows) {
            $this->link["ocfilter"] = ["table" => "ocfilter_option_value_to_product", "list" => ["languages", "categories"], "text" => ["none"], "func" => 1];
        }
        if ("2.0.0.0" <= VERSION) {
            $this->link["recurring"] = ["table" => "product_recurring", "list" => ["customer_groups", "recurring_id"], "text" => ["customer_group", "recurring"], "func" => 1];
        }
    }
    public function activate()
    {
        $this->load->language("batch_editor/setting");
        $this->json["success"] = $this->language->get("success_activate_extension");
        echo json_encode($this->json);
    }
    private function setOutput($template, $data, $children = false)
    {
        if (VERSION < "2.0.0.0") {
            $this->data = $data;
            $this->template = $template;
            if ($children) {
                $this->children = ["common/header", "common/footer"];
            }
            $this->response->setOutput($this->render());
        } else {
            if ($children) {
                $data["header"] = $this->load->controller("common/header");
                $data["column_left"] = $this->load->controller("common/column_left");
                $data["footer"] = $this->load->controller("common/footer");
            }
            $this->response->setOutput($this->load->view($template, $data));
        }
    }
}

?>
