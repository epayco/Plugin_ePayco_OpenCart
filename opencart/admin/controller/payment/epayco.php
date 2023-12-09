<?php
namespace Opencart\Admin\Controller\Extension\Epayco\Payment;
class Epayco extends \Opencart\System\Engine\Controller
{
	private $error = [];

	public function index(): void
	{
		$this->load->language('extension/epayco/payment/epayco');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/opencart/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/epayco/payment/epayco', 'user_token=' . $this->session->data['user_token'])
		];

		$data['save'] = $this->url->link('extension/epayco/payment/epayco|save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');

		$data['payment_epayco_order_status_id'] = $this->config->get('payment_epayco_order_status_id');

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$data['partner_url'] = str_replace('&amp;', '%26', $this->url->link('extension/epayco/payment/epayco', 'user_token=' . $this->session->data['user_token']));
		$data['callback_url'] = str_replace('&amp;', '&', $this->url->link('extension/epayco/payment/epayco|callback', 'user_token=' . $this->session->data['user_token']));
		$data['disconnect_url'] = str_replace('&amp;', '&', $this->url->link('extension/epayco/payment/epayco|disconnect', 'user_token=' . $this->session->data['user_token']));

		if ($this->config->get('payment_epayco_api_key')) {
			$data['payment_epayco_api_key'] = $this->config->get('payment_epayco_api_key');
		}

		if ($this->config->get('payment_epayco_public_key')) {
			$data['payment_epayco_public_key'] = $this->config->get('payment_epayco_public_key');
		}

		if ($this->config->get('payment_epayco_private_key')) {
			$data['payment_epayco_private_key'] = $this->config->get('payment_epayco_private_key');
		}

		if ($this->config->get('payment_epayco_p_key')) {
			$data['payment_epayco_p_key'] = $this->config->get('payment_epayco_p_key');
		}

		$data['payment_epayco_type_mode_value'] = $this->config->get('payment_epayco_type_mode_value');

		if ($this->config->get('payment_epayco_test_mode')) {
			$data['payment_epayco_test_mode'] = $this->config->get('payment_epayco_test_mode');
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['payment_epayco_status'] = $this->config->get('payment_epayco_status');
		$data['payment_epayco_sort_order'] = $this->config->get('payment_epayco_sort_order');


		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/epayco/payment/epayco', $data));
	}

	public function save(): void
	{
		$this->load->language('extension/epayco/payment/epayco');

		if (!$this->user->hasPermission('modify', 'extension/epayco/payment/epayco')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_epayco_api_key']) {
			$this->error['apiKey'] = $this->language->get('error_apiKey');
		}
		
		if (!$this->request->post['payment_epayco_api_key']) {
			$this->error['apiKey'] = $this->language->get('error_apiKey');
		}

		if (!$this->request->post['payment_epayco_public_key']) {
			$this->error['publicKey'] = $this->language->get('error_publicKey');
		}

		if (!$this->request->post['payment_epayco_private_key']) {
			$this->error['privateKey'] = $this->language->get('error_privateKey');
		}

		if (!$this->request->post['payment_epayco_p_key']) {
			$this->error['pKey'] = $this->language->get('error_pKey');
		}

		if (!$this->error) {
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('payment_epayco', $this->request->post);
			$data['success'] = $this->language->get('text_success');
		}
		$data['error'] = $this->error;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}
	
}
