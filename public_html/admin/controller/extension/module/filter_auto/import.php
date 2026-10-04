<?php

class ControllerExtensionModuleFilterAutoImport extends Controller
{
    public function index()
    {
        $data = $this->load->language('extension/module/filter_auto');

        $this->document->setTitle($this->language->get('text_import'));
        $data['heading_title'] = $this->language->get('text_import');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/filter_auto/import', 'user_token=' . $this->session->data['user_token'], true);
        $data['user_token'] = $this->session->data['user_token'];
        $data['link_csv'] = $this->url->link('extension/module/filter_auto/import/generateCSV',
                                             'user_token=' . $this->session->data['user_token'], true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/filter_auto_import', $data));
    }

    public function generateCSV()
    {
        $data = ['BMW', 'X5', 'E90', 'Sku/Model'];

        header('Content-Description: File Transfer');
        header('Content-Type: application/csv');
        header("Content-Disposition: attachment; filename=export.csv");
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        $handle = fopen('php://output', 'w');
        ob_clean();

        fputcsv($handle, $data, ';');

        ob_flush();
        fclose($handle);
        die();
    }

    public function import()
    {
        $json['success'] = false;

        $this->load->model('extension/auto_exchange');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $field_product = $this->request->post['product_field'];
            $sart_row = $this->request->post['start_row'];
            $start = $this->request->get['start'];
            $limit = $this->request->get['limit'];
            $iteration = $this->request->get['iteration'];

            if (is_uploaded_file($this->request->files['file']['tmp_name'])) {
                $file = $this->request->files['file']['tmp_name'];

                if (($handle = fopen($file, "r")) !== FALSE) {
                    $total = $this->csvCountRows($handle);
                    rewind($handle);

                    $row = 0;
                    $count = 0;

                    $json['success'] = true;

                    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                        $row++;

                        if ($row < $sart_row) {
                            continue;
                        }

                        if (($row < $start) && $start != 0) {
                            continue;
                        }

                        $insert = $this->model_extension_auto_exchange->import($data, $field_product);

                        if ($insert) {
                            $count++;
                        }

                        if ($row >= ($start + $limit)) {
                            break;
                        }
                    }

                    if (empty($data)) {
                        $json['success'] = false;
                    }

                    $json['iteration'] = ++$iteration;
                    $json['count'] = $count;
                    $json['total'] = $total;

                    fclose($handle);
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function csvCountRows($handle)
    {
        $linecount = 0;

        while (fgets($handle) !== false) {
            $linecount++;
        }

        return $linecount;
    }
}