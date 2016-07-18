<?php
class ControllerPaymentPayco extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('payment/payco');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payco', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');

		$data['entry_merchant'] = $this->language->get('entry_merchant');
		$data['entry_key'] = $this->language->get('entry_key');
		$data['entry_callback'] = $this->language->get('entry_callback');
		//$data['entry_md5'] = $this->language->get('entry_md5');
		//$data['entry_total'] = $this->language->get('entry_total');
		//$data['entry_comision'] = $this->language->get('entry_comision');
		//$data['entry_valor_comision'] = $this->language->get('entry_valor_comision');
		
		$data['entry_test'] = $this->language->get('entry_test');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['help_callback'] = $this->language->get('help_callback');
		//$data['help_md5'] = $this->language->get('help_md5');
		//$data['help_total'] = $this->language->get('help_total');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['tab_general'] = $this->language->get('tab_general');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['merchant'])) {
			$data['error_merchant'] = $this->error['merchant'];
		} else {
			$data['error_merchant'] = '';
		}

		if (isset($this->error['key'])) {
			$data['error_key'] = $this->error['key'];
		} else {
			$data['error_key'] = '';
		}

		/*if (isset($this->error['comision'])) {
			$data['error_comision'] = $this->error['comision'];
		} else {
			$data['error_comision'] = '';
		}

		if (isset($this->error['valor_comision'])) {
			$data['error_valor_comision'] = $this->error['valor_comision'];
		} else {
			$data['error_valor_comision'] = '';
		}*/

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_payment'),
			'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('payment/payco', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['action'] = $this->url->link('payment/payco', 'token=' . $this->session->data['token'], 'SSL');

		$data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['payco_merchant'])) {
			$data['payco_merchant'] = $this->request->post['payco_merchant'];
		} else {
			$data['payco_merchant'] = $this->config->get('payco_merchant');
		}

		if (isset($this->request->post['payco_key'])) {
			$data['payco_key'] = $this->request->post['payco_key'];
		} else {
			$data['payco_key'] = $this->config->get('payco_key');
		}

		if (isset($this->request->post['payco_comision'])) {
			$data['payco_comision'] = $this->request->post['payco_comision'];
		} else {
			$data['payco_comision'] = $this->config->get('payco_comision')?$this->config->get('payco_comision'):2.99;
		}

		if (isset($this->request->post['payco_valor_comision'])) {
			$data['payco_valor_comision'] = $this->request->post['payco_valor_comision'];
		} else {
			$data['payco_valor_comision'] = $this->config->get('payco_valor_comision')?$this->config->get('payco_valor_comision'):600;
		}

		if (isset($this->request->post['payco_test'])) {
			$data['payco_test'] = $this->request->post['payco_test'];
		} else {
			$data['payco_test'] = $this->config->get('payco_test');
		}

		$data['payco_callback'] = HTTP_CATALOG . 'index.php?route=payment/payco/callback';

		/*if (isset($this->request->post['payco_md5'])) {
			$data['payco_md5'] = $this->request->post['payco_md5'];
		} else {
			$data['payco_md5'] = $this->config->get('payco_md5');
		}*/

		/*if (isset($this->request->post['payco_total'])) {
			$data['payco_total'] = $this->request->post['payco_total'];
		} else {
			$data['payco_total'] = $this->config->get('payco_total');
		}*/

		if (isset($this->request->post['payco_order_status_id'])) {
			$data['payco_order_status_id'] = $this->request->post['payco_order_status_id'];
		} else {
			$data['payco_order_status_id'] = $this->config->get('payco_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payco_geo_zone_id'])) {
			$data['payco_geo_zone_id'] = $this->request->post['payco_geo_zone_id'];
		} else {
			$data['payco_geo_zone_id'] = $this->config->get('payco_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payco_status'])) {
			$data['payco_status'] = $this->request->post['payco_status'];
		} else {
			$data['payco_status'] = $this->config->get('payco_status');
		}

		if (isset($this->request->post['payco_sort_order'])) {
			$data['payco_sort_order'] = $this->request->post['payco_sort_order'];
		} else {
			$data['payco_sort_order'] = $this->config->get('payco_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/payco.tpl', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/payco')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payco_merchant']) {
			$this->error['merchant'] = $this->language->get('error_merchant');
		}

		if (!$this->request->post['payco_key']) {
			$this->error['key'] = $this->language->get('error_key');
		}

		return !$this->error;
	}
}