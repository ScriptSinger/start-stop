<?php

class ControllerExtensionModuleFilterAutoExport extends Controller
{

    public function index()
    {
        $data = $this->load->language('extension/module/filter_auto');

        $this->document->setTitle($this->language->get('text_export'));
        $data['heading_title'] = $this->language->get('text_export');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/filter_auto/export', 'user_token=' . $this->session->data['user_token'], true);
        $data['user_token'] = $this->session->data['user_token'];

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $field_product = $this->request->post['product_field'];

            $this->load->model('extension/auto_exchange');
            $data = $this->model_extension_auto_exchange->export($field_product);

            header('Content-Description: File Transfer');
            header('Content-Type: application/csv');
            header("Content-Disposition: attachment; filename=export.csv");
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

            $handle = fopen('php://output', 'w');
            ob_clean();

            foreach ($data as $row) {
                fputcsv($handle, $row, ';');
            }

            ob_flush();
            fclose($handle);
            die();
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/filter_auto_export', $data));
    }
}