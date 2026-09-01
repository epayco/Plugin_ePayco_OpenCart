<?php
/**
 * Adaptado desde el release 4.2.0 (OpenCart 4.x, namespaces PHP)
 * a la convencion de OpenCart 3.0.x (sin namespace, clases procedurales,
 * rutas bajo extension/payment/) - ver context/Plugin_ePayco_OpenCart/CLAUDE.md
 * para el detalle de la incompatibilidad original (SDK-1342).
 */
class ControllerExtensionPaymentEpayco extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/epayco');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_epayco', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		foreach (array('apiKey', 'publicKey', 'privateKey', 'pKey') as $key) {
			$data['error_' . $key] = isset($this->error[$key]) ? $this->error[$key] : '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/epayco', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/payment/epayco', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		if (isset($this->request->post['payment_epayco_order_status_id'])) {
			$data['payment_epayco_order_status_id'] = $this->request->post['payment_epayco_order_status_id'];
		} else {
			$data['payment_epayco_order_status_id'] = $this->config->get('payment_epayco_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_epayco_geo_zone_id'])) {
			$data['payment_epayco_geo_zone_id'] = $this->request->post['payment_epayco_geo_zone_id'];
		} else {
			$data['payment_epayco_geo_zone_id'] = $this->config->get('payment_epayco_geo_zone_id');
		}

		// El release 4.2.0 original nunca populaba geo_zones ni el valor seleccionado
		// (el select de Geo Zone quedaba siempre vacio) - se corrige aqui.
		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		foreach (array('status', 'api_key', 'public_key', 'private_key', 'p_key', 'type_mode_value', 'test_mode', 'sort_order') as $field) {
			$key = 'payment_epayco_' . $field;

			if (isset($this->request->post[$key])) {
				$data[$key] = $this->request->post[$key];
			} else {
				$data[$key] = $this->config->get($key);
			}
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/epayco', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/epayco')) {
			$this->error['warning'] = $this->language->get('error_permission');
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

		return !$this->error;
	}
}
