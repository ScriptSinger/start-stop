<?php

class ControllerExtensionModuleFilterAutoAutoUniversal extends Controller
{
    public function index()
    {
        $result = [];

        $this->load->language('extension/module/auto');
        $this->load->model('extension/auto');

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $universal = $this->model_extension_auto->getUniversal();

            if (empty($universal)) {
                $result['error'] = $this->language->get('error_universal_need');
            } else {
                $result['info'] = $universal;
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));
    }
}