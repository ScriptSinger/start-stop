<?php
class ControllerProductGofilterscripts extends Controller {
	public function index() {
		
		$data = array();
		
		$this->load->language('extension/module/gofilter');
		
		$data['text_reset_all'] = $this->language->get('text_reset_all');

		if (isset($this->request->get['nofilter'])) {
			$data['nofilter'] = $this->request->get['nofilter'];
		} else {
			$data['nofilter'] = false;
		}
		
		return $this->load->view('product/gofilterscripts', $data);
	}
}
