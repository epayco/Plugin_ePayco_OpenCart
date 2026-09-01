<?php
/**
 * Adaptado desde el release 4.2.0 (OpenCart 4.x, namespaces PHP) a la
 * convencion de OpenCart 3.0.x - ver context/Plugin_ePayco_OpenCart/CLAUDE.md
 * (hallazgo de compatibilidad, ticket SDK-1342) para el detalle completo.
 *
 * NOTA: el release 4.2.0 original ya referenciaba un archivo
 * system/library/epayco.php (cliente real de la API de ePayco, usado en
 * approveOrder()) que NO existe en ningun release publicado de este repo.
 * Esa ausencia es un bug independiente de la version de OpenCart - no se
 * fabrica aqui su contenido (es logica de integracion con la API real de
 * ePayco); en su lugar, approveOrder() falla de forma controlada si el
 * archivo no esta presente en vez de un fatal error crudo.
 */
class ControllerExtensionPaymentEpayco extends Controller {
	private $error = array();
	private $extension_base_path = 'extension/payment/epayco';

	public function index() {
		if (!$this->config->get('payment_epayco_api_key')) {
			return '';
		}

		$this->load->language('extension/payment/epayco');
		$this->load->model('extension/payment/epayco');
		$this->load->model('localisation/country');
		$this->load->model('checkout/order');

		$country = $this->model_localisation_country->getCountry($this->config->get('config_country_id'));

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$data['epayco_api_key_value'] = $this->config->get('payment_epayco_api_key');
		$data['payment_epayco_private_key'] = $this->config->get('payment_epayco_private_key');
		$data['payment_epayco_public_key'] = $this->config->get('payment_epayco_public_key');
		$data['payment_epayco_p_key'] = $this->config->get('payment_epayco_p_key');
		$data['payment_epayco_type_checkout'] = $this->config->get('payment_epayco_type_mode_value');
		$data['epayco_test_mode_value'] = $this->config->get('payment_epayco_test_mode');
		$data['order_id'] = $this->session->data['order_id'];
		$data['ip'] = $this->getCustomerIp();

		$data['p_test_mode'] = ($data['epayco_test_mode_value'] == '1');
		$data['p_payco_checkout_type'] = ($data['payment_epayco_type_checkout'] == '1') ? 'onepage' : 'standard';

		if (isset($this->session->data['customer']['telephone'])) {
			$data['customer_telephone'] = $this->session->data['customer']['telephone'];
		}

		$data['p_itemname'] = '';

		foreach ($this->cart->getProducts() as $product) {
			if (trim($product['name']) != '') {
				if ($data['p_itemname'] == '') {
					$data['p_itemname'] = $product['name'];
				} else {
					$data['p_itemname'] = $data['p_itemname'] . ',' . $product['name'];
				}
			}
		}

		$data['p_id_invoice'] = (string)$this->session->data['order_id'] . '_op';
		$data['extra1'] = $this->session->data['order_id'];

		$data['p_currency_code'] = $order_info['currency_code'];
		$data['p_amount'] = (float)($this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false));

		$order_total_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_info['order_id'] . "'");

		$p_tax = 0;
		$p_amount_ = 0;

		if ($order_total_query->num_rows > 0) {
			foreach ($order_total_query->rows as $order_total_row) {
				if ($order_total_row['code'] == 'tax') {
					$p_tax += (float)$order_total_row['value'];
				}

				if ($order_total_row['code'] == 'total') {
					$p_amount_ += (float)$order_total_row['value'];
				}
			}
		}

		$data['p_tax'] = (float)$p_tax;
		$data['p_amount_base'] = (float)($p_amount_ - $p_tax);

		$countryCode = html_entity_decode($order_info['shipping_iso_code_2'], ENT_QUOTES, 'UTF-8');
		$data['p_shiping_country'] = $countryCode ? $countryCode : 'CO';

		$data['p_lang'] = ($this->config->get('config_language') === 'en-gb') ? 'en' : 'es';

		$data['p_url_confirmation'] = $this->url->link($this->extension_base_path . '/callback', 'confirmation=1', true);
		$data['p_url_response'] = $this->url->link($this->extension_base_path . '/callback', 'response=1', true);

		$data['customer_email'] = $this->session->data['customer']['email'];
		$data['lang'] = $this->language->get('code');
		// El release 4.2.0 original seteaba 'lang' pero el twig referenciaba
		// 'language' (nunca asignado) - se corrige agregando ambos.
		$data['language'] = $data['lang'];

		list($totals, $taxes, $total, $sub_total) = $this->getCartTotals();

		$data['taxes'] = 0;
		$data['discount'] = 0;

		foreach ($totals as $total_item) {
			if ($total_item['code'] === 'tax') {
				$data['taxes'] = $total_item['value'];
			}
			if ($total_item['code'] === 'coupon') {
				$data['discount'] = $total_item['value'] * -1;
			}
			if ($total_item['code'] === 'voucher') {
				$data['discount'] += $total_item['value'] * -1;
			}
			if ($total_item['code'] === 'shipping') {
				$data['shippingMethods'] = array(
					array(
						'price' => $total_item['value'],
						'id'    => $total_item['code'],
						'label' => $total_item['title'],
					)
				);
			}
		}

		$data['currency_value'] = 'USD';
		$data['decimal_place'] = '';
		$data['message_amount'] = number_format($sub_total * 2, 2, '.', '');

		return $this->load->view('extension/payment/epayco', $data);
	}

	public function approveOrder() {
		$this->load->language('extension/payment/epayco');
		$this->load->model('extension/payment/epayco');
		$this->load->model('checkout/order');

		$library = DIR_SYSTEM . 'library/epayco.php';

		if (!is_file($library)) {
			// Ver nota de cabecera: este archivo nunca existio en el repo publicado.
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode(array(
				'error' => 'ePayco: falta system/library/epayco.php (cliente de la API de ePayco). Contactar a desarrollo@epayco.com.'
			)));
			return;
		}

		require_once $library;

		$epayco_api_key_value = $this->config->get('payment_epayco_api_key');
		$epayco_info = array('payment_epayco_api_key' => $epayco_api_key_value);
		$epayco = new Epayco($epayco_info);

		if (isset($this->request->post['orderId'])) {
			$order_status = $epayco->checkOrderStatus($this->request->post['orderId']);

			list($totals, $taxes, $total) = $this->getCartTotals();

			$order_data = array();
			$order_data['totals'] = $totals;

			if ($order_status['total_amount'] === number_format($total, 2)) {
				$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
				$order_data['transaction_id'] = $order_status['id'];
				$order_data['store_id'] = $this->config->get('config_store_id');
				$order_data['store_name'] = $this->config->get('config_name');
				$order_data['store_url'] = $this->config->get('config_url');
				$order_data['customer_id'] = $this->session->data['customer']['customer_id'];
				$order_data['customer_group_id'] = $this->session->data['customer']['customer_group_id'];
				$order_data['firstname'] = $this->session->data['customer']['firstname'];
				$order_data['lastname'] = $this->session->data['customer']['lastname'];
				$order_data['email'] = $this->session->data['customer']['email'];
				$order_data['telephone'] = $this->session->data['customer']['telephone'];
				$order_data['custom_field'] = isset($this->session->data['customer']['custom_field']) ? $this->session->data['customer']['custom_field'] : array();

				if (array_key_exists('payment_address', $this->session->data)) {
					$order_data['payment_firstname'] = $this->session->data['payment_address']['firstname'];
					$order_data['payment_lastname'] = $this->session->data['payment_address']['lastname'];
					$order_data['payment_company'] = $this->session->data['payment_address']['company'];
					$order_data['payment_address_1'] = $this->session->data['payment_address']['address_1'];
					$order_data['payment_address_2'] = $this->session->data['payment_address']['address_2'];
					$order_data['payment_city'] = $this->session->data['payment_address']['city'];
					$order_data['payment_postcode'] = $this->session->data['payment_address']['postcode'];
					$order_data['payment_zone'] = $this->session->data['payment_address']['zone'];
					$order_data['payment_zone_id'] = $this->session->data['payment_address']['zone_id'];
					$order_data['payment_country'] = $this->session->data['payment_address']['country'];
					$order_data['payment_country_id'] = $this->session->data['payment_address']['country_id'];
					$order_data['payment_address_format'] = $this->session->data['payment_address']['address_format'];
					$order_data['payment_custom_field'] = isset($this->session->data['payment_address']['custom_field']) ? $this->session->data['payment_address']['custom_field'] : array();
				}

				if (isset($this->session->data['payment_method'])) {
					$order_data['payment_method'] = isset($this->session->data['payment_method']['title']) ? $this->session->data['payment_method']['title'] : '';
					$order_data['payment_code'] = isset($this->session->data['payment_method']['code']) ? $this->session->data['payment_method']['code'] : '';
				} else {
					$order_data['payment_method'] = '';
					$order_data['payment_code'] = '';
				}

				if ($this->cart->hasShipping()) {
					$order_data['shipping_firstname'] = $this->session->data['shipping_address']['firstname'];
					$order_data['shipping_lastname'] = $this->session->data['shipping_address']['lastname'];
					$order_data['shipping_company'] = $this->session->data['shipping_address']['company'];
					$order_data['shipping_address_1'] = $this->session->data['shipping_address']['address_1'];
					$order_data['shipping_address_2'] = $this->session->data['shipping_address']['address_2'];
					$order_data['shipping_city'] = $this->session->data['shipping_address']['city'];
					$order_data['shipping_postcode'] = $this->session->data['shipping_address']['postcode'];
					$order_data['shipping_zone'] = $this->session->data['shipping_address']['zone'];
					$order_data['shipping_zone_id'] = $this->session->data['shipping_address']['zone_id'];
					$order_data['shipping_country'] = $this->session->data['shipping_address']['country'];
					$order_data['shipping_country_id'] = $this->session->data['shipping_address']['country_id'];
					$order_data['shipping_address_format'] = $this->session->data['shipping_address']['address_format'];
					$order_data['shipping_custom_field'] = isset($this->session->data['shipping_address']['custom_field']) ? $this->session->data['shipping_address']['custom_field'] : array();
					$order_data['shipping_method'] = isset($this->session->data['shipping_method']['title']) ? $this->session->data['shipping_method']['title'] : '';
					$order_data['shipping_code'] = isset($this->session->data['shipping_method']['code']) ? $this->session->data['shipping_method']['code'] : '';
				} else {
					foreach (array('firstname', 'lastname', 'company', 'address_1', 'address_2', 'city', 'postcode', 'zone', 'zone_id', 'country', 'country_id', 'address_format', 'method', 'code') as $suffix) {
						$order_data['shipping_' . $suffix] = ($suffix === 'custom_field') ? array() : '';
					}
					$order_data['shipping_custom_field'] = array();
				}

				$order_data['products'] = array();

				foreach ($this->cart->getProducts() as $product) {
					$option_data = array();

					foreach ($product['option'] as $option) {
						$option_data[] = array(
							'product_option_id' => $option['product_option_id'],
							'product_option_value_id' => $option['product_option_value_id'],
							'option_id' => $option['option_id'],
							'option_value_id' => $option['option_value_id'],
							'name' => $option['name'],
							'value' => $option['value'],
							'type' => $option['type']
						);
					}

					$order_data['products'][] = array(
						'product_id' => $product['product_id'],
						'name' => $product['name'],
						'model' => $product['model'],
						'option' => $option_data,
						'download' => $product['download'],
						'quantity' => $product['quantity'],
						'subtract' => $product['subtract'],
						'price' => $product['price'],
						'total' => $product['total'],
						'tax' => $this->tax->getTax($product['price'], $product['tax_class_id']),
						'reward' => $product['reward']
					);
				}

				$order_data['comment'] = isset($this->session->data['comment']) ? $this->session->data['comment'] : '';
				$order_data['total'] = $total;
				$order_data['affiliate_id'] = 0;
				$order_data['commission'] = 0;
				$order_data['marketing_id'] = 0;
				$order_data['tracking'] = '';
				$order_data['language_id'] = $this->config->get('config_language_id');
				$order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
				$order_data['currency_code'] = $this->session->data['currency'];
				$order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
				$order_data['ip'] = $this->request->server['REMOTE_ADDR'];
				$order_data['forwarded_ip'] = !empty($this->request->server['HTTP_X_FORWARDED_FOR']) ? $this->request->server['HTTP_X_FORWARDED_FOR'] : '';
				$order_data['user_agent'] = isset($this->request->server['HTTP_USER_AGENT']) ? $this->request->server['HTTP_USER_AGENT'] : '';
				$order_data['accept_language'] = isset($this->request->server['HTTP_ACCEPT_LANGUAGE']) ? $this->request->server['HTTP_ACCEPT_LANGUAGE'] : '';

				// order_status_id: sin system/config/epayco.php (ver nota de cabecera) se
				// usa el order_status configurado en el admin como fallback razonable en
				// vez de una clave 'processing'/'failed' que no existe en OC3.
				$order_status_id = (int)$this->config->get('payment_epayco_order_status_id');

				if ($order_status['status'] == 'success') {
					$this->session->data['order_id'] = $this->model_checkout_order->addOrder($order_data);
					$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $order_status_id ?: 1, 'success');
					$data['redirect'] = $this->url->link('checkout/success');
				} else {
					$this->session->data['order_id'] = $this->model_checkout_order->addOrder($order_data);
					$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], 10, 'fail');
					$data['redirect'] = $this->url->link('checkout/fail');
				}

				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($data));

				return;
			}
		}

		$data['error'] = $this->error;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}

	public function getShippingMethods() {
		$this->load->model('localisation/country');
		$this->load->model('localisation/zone');

		$country_id = (int)$this->model_localisation_country->getCountryByIsoCode2($this->request->post['country'])['country_id'];
		$zones = $this->model_localisation_zone->getZonesByCountryId($country_id);
		$zone_id = 1;

		if (isset($this->request->post['stateCode'])) {
			$zone_id = $this->request->post['stateCode'];
		} else {
			$city = str_replace('-', ' ', $this->request->post['city']);

			foreach ($zones as $zone_item) {
				if (strtolower($zone_item['name']) == strtolower($city)) {
					$zone_id = (int)$zone_item['zone_id'];
					break;
				}
			}
		}

		$data['zone_id'] = $zone_id;
		$data['country_id'] = $country_id;

		list($totals, $taxes, $total, $sub_total) = $this->getCartTotals();

		$data['taxes'] = 0;
		$data['discount'] = 0;

		foreach ($totals as $total_item) {
			if ($total_item['code'] === 'tax') {
				$data['taxes'] = $total_item['value'];
			}
			if ($total_item['code'] === 'coupon') {
				$data['discount'] = $total_item['value'] * -1;
			}
			if ($total_item['code'] === 'voucher') {
				$data['discount'] += $total_item['value'] * -1;
			}
			if ($total_item['code'] === 'shipping' && $this->cart->hasShipping()) {
				$data['shippingMethods'] = array(
					array(
						'price' => $total_item['value'],
						'id'    => $total_item['code'],
						'label' => $total_item['title'],
					)
				);
			}
		}

		$data['amount'] = $sub_total;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}

	public function confirm() {
		$this->load->language('extension/payment/epayco');

		$json = array();

		if (!isset($this->session->data['order_id'])) {
			$json['error'] = $this->language->get('error_order');
		}

		if (!$json) {
			$this->load->model('checkout/order');

			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], 1);

			$json['redirect'] = $this->url->link('checkout/success', '', true);
			$json['orderId'] = $this->session->data['order_id'];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function callback() {
		try {
			$this->load->model('checkout/order');

			$confirmation = false;
			$data = '';

			if (isset($_GET['ref_payco'])) {
				$url = 'https://secure.epayco.co/validation/v1/reference/' . $_GET['ref_payco'];
				$response = json_decode(file_get_contents($url));

				$data = (array)$response->data;
				$success = $response->success;
			}

			if (isset($_POST['x_ref_payco'])) {
				$data = $_REQUEST;
				$confirmation = true;
				$success = true;
			}

			if ($data && isset($success)) {
				$x_ref_payco = trim($data['x_ref_payco']);
				$x_transaction_id = trim($data['x_transaction_id']);
				$x_amount = trim($data['x_amount']);
				$x_currency_code = trim($data['x_currency_code']);
				$x_signature = trim($data['x_signature']);
				$x_cod_response = trim($data['x_cod_response']);
				$signature = $this->validateSignature($x_ref_payco, $x_transaction_id, $x_amount, $x_currency_code);

				$order_id = (int)$data['x_extra1'];
				$order_info = $this->model_checkout_order->getOrder($order_id);
				$order_status = $order_info['order_status'];

				if ($x_signature == $signature) {
					switch ((int)$x_cod_response) {
						case 1:
							if ($order_status != 'Complete' || $order_status != 'Processing' || $order_status != 'Processed') {
								if ($order_status != 'Processed' && $order_status != 'Complete') {
									$this->model_checkout_order->addOrderHistory($order_id, 5);
								}
							}
							$this->respondCallback($confirmation, 'checkout/success');
							break;
						case 2:
						case 4:
						case 10:
						case 11:
							if ($order_status != 'Canceled') {
								$this->model_checkout_order->addOrderHistory($order_id, 7);
							}
							$this->respondCallback($confirmation, 'checkout/failure');
							break;
						case 3:
						case 7:
							if ($order_status != 'Pending') {
								$this->model_checkout_order->addOrderHistory($order_id, 1);
							}
							$this->respondCallback($confirmation, 'checkout/success');
							break;
						default:
							$this->model_checkout_order->addOrderHistory($order_id, 10);
							$this->respondCallback($confirmation, 'checkout/failure');
							break;
					}
				} else {
					$this->response->redirect($this->url->link('checkout/failure'));
				}
			} else {
				if ($confirmation) {
					$this->response->addHeader('Content-Type: application/json');
					$this->response->setOutput(json_encode(array('success' => false, 'message' => 'failed confirmation')));
				} else {
					$this->response->redirect($this->url->link('checkout/failure'));
				}
			}
		} catch (Exception $e) {
			$this->response->redirect($this->url->link('checkout/failure'));
		}
	}

	private function respondCallback($confirmation, $route) {
		if (!$confirmation) {
			$this->response->redirect($this->url->link($route));
		} else {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode(array('success' => true, 'message' => 'confirm successfull')));
		}
	}

	public function validateSignature($x_ref_payco, $x_transaction_id, $x_amount, $x_currency_code) {
		$merchant_id = $this->config->get('payment_epayco_api_key');
		$p_key = $this->config->get('payment_epayco_p_key');

		return hash('sha256', trim($merchant_id) . '^' . trim($p_key) . '^' . $x_ref_payco . '^' . $x_transaction_id . '^' . $x_amount . '^' . $x_currency_code);
	}

	public function getCustomerIp() {
		foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
			if (isset($_SERVER[$key])) {
				return $_SERVER[$key];
			}
		}

		return 'UNKNOWN';
	}

	/**
	 * Totales del carrito con la convencion nativa de OpenCart 3.x
	 * (system/library/cart no expone getTotals(); los modulos "total" se
	 * recorren manualmente - ver catalog/controller/extension/payment/pp_braintree.php
	 * como referencia real de este patron).
	 */
	private function getCartTotals() {
		$this->load->model('setting/extension');

		$totals = array();
		$taxes = $this->cart->getTaxes();
		$total = 0;

		$total_data = array(
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		);

		$results = $this->model_setting_extension->getExtensions('total');

		$sort_order = array();

		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
		}

		array_multisort($sort_order, SORT_ASC, $results);

		foreach ($results as $result) {
			if ($this->config->get('total_' . $result['code'] . '_status')) {
				$this->load->model('extension/total/' . $result['code']);
				$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
			}
		}

		$sort_order = array();

		foreach ($totals as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $totals);

		$sub_total = 0;

		foreach ($totals as $total_item) {
			if ($total_item['code'] === 'sub_total') {
				$sub_total = $total_item['value'];
			}
		}

		return array($totals, $taxes, $total, $sub_total);
	}
}
