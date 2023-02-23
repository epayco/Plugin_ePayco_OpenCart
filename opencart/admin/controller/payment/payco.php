<?php
namespace Opencart\Admin\Controller\Extension\Payco\Payment;

class Payco extends \Opencart\System\Engine\Controller
{
	private $error = [];

	public function index(): void
	{
		$this->load->language('extension/payco/payment/payco');

		$this->load->model('extension/payco/payment/payco');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extensions'),
			'href' => $this->url->link('marketplace/opencart/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payco/payment/payco', 'user_token=' . $this->session->data['user_token'])
		];

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$data['save'] = $this->url->link('extension/payco/payment/payco|save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');

		$data['partner_url'] = str_replace('&amp;', '%26', $this->url->link('extension/payco/payment/payco', 'user_token=' . $this->session->data['user_token']));
		$data['callback_url'] = str_replace('&amp;', '&', $this->url->link('extension/payco/payment/payco|callback', 'user_token=' . $this->session->data['user_token']));
		$data['disconnect_url'] = str_replace('&amp;', '&', $this->url->link('extension/payco/payment/payco|disconnect', 'user_token=' . $this->session->data['user_token']));

		$data['server'] = HTTP_SERVER;
		$data['catalog'] = HTTP_CATALOG;

		// Setting
		$_config = new \Opencart\System\Engine\Config();
		$_config->addPath(DIR_EXTENSION . 'payco/system/config/');
		$_config->load('payco');

		$data['setting'] = $_config->get('payco_setting');

		if ($this->config->get('payment_payco_api_key')) {
			$data['payco_api_key_value'] = $this->config->get('payment_payco_api_key');
		}

		if ($this->config->get('payment_payco_public_key')) {
			$data['payco_public_key_value'] = $this->config->get('payment_payco_public_key');
		}

		if ($this->config->get('payment_payco_p_key')) {
			$data['payco_p_key_value'] = $this->config->get('payment_payco_p_key');
		}

		if ($this->config->get('payment_payco_dark_mode_value')) {
			$data['payco_dark_mode_value'] = $this->config->get('payment_payco_dark_mode_value');
		}

		if ($this->config->get('payment_payco_test_mode')) {
			$data['payco_test_mode_value'] = $this->config->get('payment_payco_test_mode');
		}

		if (isset($data['payco_api_key_value'])) {
			$payco_info = [
				'payment_payco_api_key' => $data['payco_api_key_value'],
				'payment_payco_public_key' => $data['payco_public_key_value'],
				'payment_payco_p_key' => $data['payco_p_key_value'],
				'payment_payco_dark_mode_value' => array_key_exists('payco_dark_mode_value', $data) ? true : false,
				'payment_payco_test_mode' => array_key_exists('payco_test_mode_value', $data) ? true : false
			];
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payco/payment/payco', $data));
	}

	public function save(): void
	{
		$this->load->language('extension/payco/payment/payco');

		$this->load->model('extension/payco/payment/payco');

		if (!$this->user->hasPermission('modify', 'extension/payco/payment/payco')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		// Setting
		$_config = new \Opencart\System\Engine\Config();
		$_config->addPath(DIR_EXTENSION . 'payco/system/config/');
		$_config->load('payco');

		$setting = $_config->get('payco_setting');
		require_once DIR_EXTENSION . 'payco/system/library/payco.php';
		$epayco_info = [
			'payment_payco_api_key' => $this->request->post['payment_payco_api_key'],
			'payment_payco_public_key' => $this->request->post['payment_payco_public_key'],
			'payment_payco_p_key' => $this->request->post['payment_payco_p_key'],
			'payment_payco_dark_mode_value' => array_key_exists('payco_dark_mode_value', $this->request->post) ? true : false,
			'payment_payco_test_mode' => array_key_exists('payco_test_mode', $this->request->post) ? true : false
		];

		if (!$this->error) {
			$this->load->model('setting/setting');
			$epayco_info['payment_payco_status'] = true;

			$this->model_setting_setting->editSetting('payment_payco', $epayco_info);
			$data['success'] = $this->language->get('success_save');
		}
		$data['error'] = $this->error;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}

	public function disconnect(): void
	{
		$this->load->model('setting/setting');

		$setting = $this->model_setting_setting->getSetting('payment_payco');
		$setting['payment_payco_api_key'] = '';
		$setting['payment_payco_public_key'] = '';
		$setting['payment_payco_p_key'] = '';
		$setting['payment_payco_dark_mode_value'] = '';
		$setting['payment_payco_test_mode'] = '';

		$this->model_setting_setting->editSetting('payment_payco', $setting);

		$data['error'] = $this->error;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}

	public function callback(): void
	{
		if (isset($this->request->post['environment']) && isset($this->request->post['authorization_code']) && isset($this->request->post['shared_id']) && isset($this->request->post['seller_nonce'])) {
			$this->session->data['environment'] = $this->request->post['environment'];
			$this->session->data['authorization_code'] = $this->request->post['authorization_code'];
			$this->session->data['shared_id'] = $this->request->post['shared_id'];
			$this->session->data['seller_nonce'] = $this->request->post['seller_nonce'];
		}

		$data['error'] = $this->error;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}

	public function install()
	{
		$this->load->model('setting/setting');
		$this->load->model('extension/payco/module/payco_smart_button');
		$this->load->model('extension/payco/payment/payco');

		$this->model_extension_payco_module_payco_smart_button->install();

		$setting['module_payco_smart_button_status'] = 0;

		$this->model_setting_setting->editSetting('module_payco_smart_button', $setting);
		$this->model_setting_setting->editValue('config', 'config_session_samesite', 'Lax');
	}

	public function order(): string
	{
		$this->load->model('extension/payco/payment/payco');
		$order_id = $this->request->get['order_id'];
		$order_info = $this->model_extension_payco_payment_payco->getOrder($order_id);
		$_config = new \Opencart\System\Engine\Config();
		$_config->addPath(DIR_EXTENSION . 'payco/system/config/');
		$_config->load('payco');
		$this->load->language('extension/payco/payment/payco');

		$data['setting'] = $_config->get('payco_setting');

		$data['column_order_id'] = $this->language->get('column_order_id');
		if (isset($order_info['transaction_id'])) {
			$data['transaction_id'] = $order_info['transaction_id'];
		}
		return $this->load->view('extension/payco/payment/order', $data);
	}
}