<?php
namespace Opencart\Catalog\Controller\Extension\Epayco\Payment;

class Epayco extends \Opencart\System\Engine\Controller
{
	private $error = [];
	private $extension_base_path = 'extension/epayco/payment/epayco';


	public function index(): string
	{
		if ($this->config->get('payment_epayco_api_key')) {
			$this->load->language('extension/epayco/payment/epayco');
			$this->load->model('checkout/cart');
			$this->load->model('extension/epayco/payment/epayco');
			$this->load->model('localisation/country');
			$this->load->model('checkout/order');

			$country = $this->model_localisation_country->getCountry($this->config->get('config_country_id'));

			// Setting
			$_config = new \Opencart\System\Engine\Config();
			$_config->addPath(DIR_EXTENSION . 'epayco/system/config/');
			$_config->load('epayco');

			$config_setting = $_config->get('epayco_setting');

			$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

			$setting = array_replace_recursive((array) $config_setting, (array) $this->config->get('payment_epayco_setting'));
			$data['epayco_api_key_value'] = $this->config->get('payment_epayco_api_key');
			$data['payment_epayco_private_key'] = $this->config->get('payment_epayco_private_key');
			$data['payment_epayco_public_key'] = $this->config->get('payment_epayco_public_key');
			$data['payment_epayco_p_key'] = $this->config->get('payment_epayco_p_key');
			$data['payment_epayco_type_checkout'] = $this->config->get('payment_epayco_type_mode_value');
			$data['epayco_test_mode_value'] = $this->config->get('payment_epayco_test_mode');
			$data['order_id'] = $this->session->data['order_id'];
			$data['ip'] = $this->getCustomerIp();
			
			if ($data['epayco_test_mode_value']=='1'){
				$data['p_test_mode'] = 'true';
			}else{
				$data['p_test_mode'] = 'false';
			}

			if ($data['payment_epayco_type_checkout']=='1'){
				$data['p_payco_checkout_type'] = 'false';
			}else{
				$data['p_payco_checkout_type'] = 'true';
			}

			if (isset($this->session->data['customer']['telephone'])) {
				$data['customer_telephone'] = ($this->session->data['customer']['telephone']);
			}

			$data['p_itemname']="";

			foreach ($this->cart->getProducts() as $product) {
				if(trim($product['name'])!=""){
					if($data['p_itemname']==""){
						$data['p_itemname']=$product['name'];
					}else{
						$data['p_itemname'] = $data['p_itemname'].",".$product['name'];
					}
				}
			}

			$data['p_id_invoice'] = $this->session->data['order_id'];

			$data['p_currency_code'] = $order_info['currency_code'];;

			$data['p_amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

			$queryOrderEpayco = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_info['order_id'] . "'");
			if(count($queryOrderEpayco->row)>0){
				$queryOrder = $queryOrderEpayco;
			}else{
				$queryOrder = null;
			}
			$p_tax=0;
			$p_amount_ = 0;
			foreach ($queryOrder->rows as $orderData){
				if($orderData['code'] == "tax"){
					$p_tax +=  floatval($orderData['value']);
				}
		
				if($orderData['code'] == "total"){
					$p_amount_ +=  floatval($orderData['value']);
				}
			}
			$p_amount_base = $p_amount_ - $p_tax;
			$data['p_tax'] = $p_tax;
			$data['p_amount_base']=$p_amount_base;

			$countryCode = html_entity_decode($order_info['shipping_iso_code_2'], ENT_QUOTES, 'UTF-8')?html_entity_decode($order_info['shipping_iso_code_2'], ENT_QUOTES, 'UTF-8'): "CO";
			$data['p_shiping_country'] = $countryCode;

			$data['p_lang'] = ( $this->config->get('config_language') === "en-gb" ) ? "en" : 'es';

			$data['p_url_confirmation'] = $this->url->link($this->extension_base_path.'|callback&comfirmation=1');

			$data['p_url_response'] = $this->url->link($this->extension_base_path.'|callback');


			$data['customer_email'] = ($this->session->data['customer']['email']);

			$data['lang'] = $this->language->get('code');
			$totals = [];
			$taxes = $this->cart->getTaxes();
			$total = 0;
			($this->model_checkout_cart->getTotals)($totals, $taxes, $total);

			$data['taxes'] = 0;
			$data['discount'] = 0;
			$sub_total = 0;
			foreach ($totals as $totalItem) {
				if ($totalItem['code'] === 'sub_total') {
					$sub_total = $totalItem['value'];
				}
				if ($totalItem['code'] === 'tax') {
					$data['taxes'] = $totalItem['value'];
				}
				if ($totalItem['code'] === 'coupon') {
					$data['discount'] = $totalItem['value'] * -1;
				}
				if ($totalItem['code'] === 'voucher') {
					$data['discount'] += $totalItem['value'] * -1;
				}
				if ($totalItem['code'] === 'shipping') {
					$data['shippingMethods'] = array(
						[

							'price' => $totalItem['value'],
							'id' => $totalItem['code'],
							'label' => $totalItem['title'],

						]
					);
				}
			}

			$data['currency_value'] = 'USD';
			$data['decimal_place'] = '';
			$data['message_amount'] = number_format($sub_total * 2, 2, '.', '');


			return $this->load->view('extension/epayco/payment/epayco', $data);
		}

		return '';
	}

	public function approveOrder(): void
	{
		$this->load->language('extension/epayco/payment/epayco');

		$this->load->model('extension/epayco/payment/epayco');
		$this->load->model('checkout/order');

		// Setting
		$_config = new \Opencart\System\Engine\Config();
		$_config->addPath(DIR_EXTENSION . 'epayco/system/config/');
		$_config->load('epayco');

		$config_setting = $_config->get('epayco_setting');

		$setting = array_replace_recursive((array) $config_setting, (array) $this->config->get('payment_epayco_setting'));
		$epayco_api_key_value = $this->config->get('payment_epayco_api_key');
		$epayco_info = [
			'payment_epayco_api_key' => $epayco_api_key_value
		];
		require_once DIR_EXTENSION . 'epayco/system/library/epayco.php';

		$epayco = new \Opencart\System\Library\Epayco($epayco_info);
		if (isset($this->request->post['orderId'])) {
			$order_status = $epayco->checkOrderStatus($this->request->post['orderId']);
			$order_data = [];

			// Totals
			$totals = [];
			$taxes = $this->cart->getTaxes();
			$total = 0;

			$sort_order = [];

			$results = $this->model_setting_extension->getExtensionsByType('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get('total_' . $result['code'] . '_status')) {
					$this->load->model('extension/' . $result['extension'] . '/total/' . $result['code']);
					($this->{'model_extension_' . $result['extension'] . '_total_' . $result['code']}->getTotal)($totals, $taxes, $total);
				}
			}

			$sort_order = [];

			foreach ($totals as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $totals);
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
				$order_data['custom_field'] = $this->session->data['customer']['custom_field'];
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
					$order_data['payment_custom_field'] = (isset($this->session->data['payment_address']['custom_field']) ? $this->session->data['payment_address']['custom_field'] : []);
				}
				if (isset($this->session->data['payment_methods'][$this->session->data['payment_method']])) {
					$payment_method_info = $this->session->data['payment_methods'][$this->session->data['payment_method']];
				}

				if (isset($payment_method_info['title'])) {
					$order_data['payment_method'] = $payment_method_info['title'];
				} else {
					$order_data['payment_method'] = '';
				}

				if (isset($payment_method_info['code'])) {
					$order_data['payment_code'] = $payment_method_info['code'];
				} else {
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
					$order_data['shipping_custom_field'] = (isset($this->session->data['shipping_address']['custom_field']) ? $this->session->data['shipping_address']['custom_field'] : []);

					if (isset($this->session->data['shipping_method'])) {
						$shipping = explode('.', $this->session->data['shipping_method']);

						if (isset($shipping[0]) && isset($shipping[1]) && isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
							$shipping_method_info = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
						}
					}

					if (isset($shipping_method_info['title'])) {
						$order_data['shipping_method'] = $shipping_method_info['title'];
					} else {
						$order_data['shipping_method'] = '';
					}

					if (isset($shipping_method_info['code'])) {
						$order_data['shipping_code'] = $shipping_method_info['code'];
					} else {
						$order_data['shipping_code'] = '';
					}
				} else {
					$order_data['shipping_firstname'] = '';
					$order_data['shipping_lastname'] = '';
					$order_data['shipping_company'] = '';
					$order_data['shipping_address_1'] = '';
					$order_data['shipping_address_2'] = '';
					$order_data['shipping_city'] = '';
					$order_data['shipping_postcode'] = '';
					$order_data['shipping_zone'] = '';
					$order_data['shipping_zone_id'] = '';
					$order_data['shipping_country'] = '';
					$order_data['shipping_country_id'] = '';
					$order_data['shipping_address_format'] = '';
					$order_data['shipping_custom_field'] = [];
					$order_data['shipping_method'] = '';
					$order_data['shipping_code'] = '';
				}

				$order_data['products'] = [];

				foreach ($this->cart->getProducts() as $product) {
					$option_data = [];

					foreach ($product['option'] as $option) {
						$option_data[] = [
							'product_option_id' => $option['product_option_id'],
							'product_option_value_id' => $option['product_option_value_id'],
							'option_id' => $option['option_id'],
							'option_value_id' => $option['option_value_id'],
							'name' => $option['name'],
							'value' => $option['value'],
							'type' => $option['type']
						];
					}

					$order_data['products'][] = [
						'product_id' => $product['product_id'],
						'master_id' => $product['master_id'],
						'name' => $product['name'],
						'model' => $product['model'],
						'option' => $option_data,
						'subscription' => $product['subscription'],
						'download' => $product['download'],
						'quantity' => $product['quantity'],
						'subtract' => $product['subtract'],
						'price' => $product['price'],
						'total' => $product['total'],
						'tax' => $this->tax->getTax($product['price'], $product['tax_class_id']),
						'reward' => $product['reward']
					];
				}

				// Gift Voucher
				$order_data['vouchers'] = [];

				if (!empty($this->session->data['vouchers'])) {
					foreach ($this->session->data['vouchers'] as $voucher) {
						$order_data['vouchers'][] = [
							'description' => $voucher['description'],
							'code' => token(10),
							'to_name' => $voucher['to_name'],
							'to_email' => $voucher['to_email'],
							'from_name' => $voucher['from_name'],
							'from_email' => $voucher['from_email'],
							'voucher_theme_id' => $voucher['voucher_theme_id'],
							'message' => $voucher['message'],
							'amount' => $voucher['amount']
						];
					}
				}

				$order_data['comment'] = (isset($this->session->data['comment']) ? $this->session->data['comment'] : '');
				$order_data['total'] = $total;

				$order_data['affiliate_id'] = 0;
				$order_data['commission'] = 0;
				$order_data['marketing_id'] = 0;
				$order_data['tracking'] = '';

				if ($this->config->get('config_affiliate_status') && isset($this->session->data['tracking'])) {
					$subtotal = $this->cart->getSubTotal();

					// Affiliate
					$this->load->model('account/affiliate');

					$affiliate_info = $this->model_account_affiliate->getAffiliateByTracking($this->session->data['tracking']);

					if ($affiliate_info) {
						$order_data['affiliate_id'] = $affiliate_info['customer_id'];
						$order_data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
						$order_data['tracking'] = $this->session->data['tracking'];
					}
				}

				$order_data['language_id'] = $this->config->get('config_language_id');
				$order_data['language_code'] = $this->config->get('config_language');

				$order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
				//$order_data['p_cust_id'] = $this->session->data['p_cust_id'];
				$order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);

				$order_data['ip'] = $this->request->server['REMOTE_ADDR'];

				if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
					$order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
				} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
					$order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
				} else {
					$order_data['forwarded_ip'] = '';
				}

				if (isset($this->request->server['HTTP_USER_AGENT'])) {
					$order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
				} else {
					$order_data['user_agent'] = '';
				}

				if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
					$order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
				} else {
					$order_data['accept_language'] = '';
				}
				$this->load->model('checkout/order');
				if ($order_status['status'] == 'success') {
					$order_status_id = $setting['order_status']['processing']['id'];

					$this->session->data['order_id'] = $this->model_checkout_order->addOrder($order_data);
					$this->model_checkout_order->editTransactionId($this->session->data['order_id'], $order_status['id']);
					$this->model_checkout_order->addHistory($this->session->data['order_id'], $order_status_id, 'success');
					$data['redirect'] = $this->url->link('checkout/success', 'language=' . $this->config->get('config_language'));
				} else {
					$order_status_id = $setting['order_status']['failed']['id'];
					$this->session->data['order_id'] = $this->model_checkout_order->addOrder($order_data);
					$this->model_checkout_order->editTransactionId($this->session->data['order_id'], $order_status['id']);
					$this->model_checkout_order->addHistory($this->session->data['order_id'], $order_status_id, 'fail');
					$data['redirect'] = $this->url->link('checkout/fail', 'language=' . $this->config->get('config_language'));
				}
				$order_details = [
					'order_url' => $data['redirect'],
					'order_position' => 'checkout_page',
					'order_id' => $this->session->data['order_id'],
					'plugin_version' => 'opencart_1.10',
					'order_status' => $order_status['status'] == 'success' ? $setting['order_status']['processing']['code'] : $setting['order_status']['failed']['code'],
					'order_content' => json_encode($order_data),
				];
				$epayco->addOrderDetails($order_data['transaction_id'], $order_details);
				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($data));
			}
		}

		$data['language'] = $this->config->get('config_language');

		$data['error'] = $this->error;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}

	public function getShippingMethods(): void
	{
		$this->load->model('localisation/country');
		$this->load->model('localisation/zone');
		$this->load->model('extension/opencart/total/shipping');
		$country_id = (int) $this->model_localisation_country->getCountryByIsoCode2($this->request->post['country'])['country_id'];
		$zones = $this->model_localisation_zone->getZonesByCountryId($country_id);
		$zone_id = 1;
		if (isset($this->request->post['stateCode'])) {
			$zone_id = $this->request->post['stateCode'];
		} else {
			$city = str_replace('-', ' ', $this->request->post['city']);
			foreach ($zones as $zoneItem) {
				if (strtolower($zoneItem['name']) == strtolower($city)) {
					$zone_id = (int) $zoneItem['zone_id'];
					break;
				}
			}
		}


		$data['zone_id'] = $zone_id;
		$data['country_id'] = $country_id;
		$_config = new \Opencart\System\Engine\Config();
		$_config->addPath(DIR_EXTENSION . 'epayco/system/config/');
		$_config->load('epayco');

		$epayco_setting = $_config->get('epayco_setting');

		$_config = new \Opencart\System\Engine\Config();
		$_config->addPath(DIR_EXTENSION . 'epayco/system/config/');
		$_config->load('epayco_smart_button');

		$config_setting = $_config->get('epayco_smart_button_setting');

		$setting = array_replace_recursive((array) $config_setting, (array) $this->config->get('module_epayco_smart_button_setting'));

		$this->load->model('checkout/cart');

		$item_total = 0;
		$totals = [];
		$taxes = $this->cart->getTaxes();
		$total = 0;
		($this->model_checkout_cart->getTotals)($totals, $taxes, $total);

		$data['taxes'] = 0;
		$sub_total = 0;
		$data['discount'] = 0;
		foreach ($totals as $totalItem) {
			if ($totalItem['code'] === 'sub_total') {
				$sub_total = $totalItem['value'];
			}
			if ($totalItem['code'] === 'tax') {
				$data['taxes'] = $totalItem['value'];
			}
			if ($totalItem['code'] === 'coupon') {
				$data['discount'] = $totalItem['value'] * -1;
			}
			if ($totalItem['code'] === 'voucher') {
				$data['discount'] += $totalItem['value'] * -1;
			}
			if ($totalItem['code'] === 'shipping' && $this->cart->hasShipping()) {
				$data['shippingMethods'] = array(
					[

						'price' => $totalItem['value'],
						'id' => $totalItem['code'],
						'label' => $totalItem['title'],

					]
				);
			}
		}
		$data['amount'] = $sub_total;
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($data));
	}

	public function confirm(): void {
		$this->load->language('extension/epayco/payment/epayco');

		$json = [];

		if (!isset($this->session->data['order_id'])) {
			$json['error'] = $this->language->get('error_order');
		}

		if (!isset($this->session->data['payment_method']) || $this->session->data['payment_method'] != 'epayco') {
			$json['error'] = $this->language->get('error_payment_method');
		}

		if (!$json) {
			$this->load->model('checkout/order');

			$this->model_checkout_order->addHistory($this->session->data['order_id'], $this->config->get('payment_epayco_order_status_id'));

			$json['redirect'] = $this->url->link('checkout/success', 'language=' . $this->config->get('config_language'), true);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function callback() {
		$this->load->model('checkout/order');
		$comfirmation = false;
		if(isset($_GET['ref_payco'])){
			$ref_payco = $_GET['ref_payco'];
			$url="https://secure.epayco.io/validation/v1/reference/".$_GET['ref_payco'];
			$response=json_decode(file_get_contents($url));
			$data = (array)$response->data;
		}
		if(isset($_POST['x_ref_payco'])){
			$data = $_REQUEST;
			$comfirmation = true;
		}
		if($data){
			$x_ref_payco = $data['x_ref_payco'];
			$x_transaction_id = $data['x_transaction_id'];
			$x_amount = $data['x_amount'];
			$x_currency_code = $data['x_currency_code'];
			$x_signature = $data['x_signature'];
			$signature = $this->validateSignature($x_ref_payco,$x_transaction_id,$x_amount,$x_currency_code);
			$x_cod_response=$data['x_cod_response'];
			$isTest=$data['x_test_request'];
			$x_test_request = $data['x_test_request'];
			$x_approval_code = $data['x_approval_code'];
			$x_cod_transaction_state = $data['x_cod_transaction_state'];
			if($isTest == "TRUE"){
				$isTest_= 1;
			}else{
				$isTest_= 2;
			}
			$order_id = (int)$data['x_extra1'];
			$order_info = $this->model_checkout_order->getOrder($order_id);
			$orderStatus = $order_info['order_status'];
			$data_p_amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
	
			if($x_signature==$signature){
				switch ((int)$x_cod_response) {
					case 1:{
						if ($orderStatus != 'Pending' ||
							$orderStatus != 'Complete'||
							$orderStatus != 'Processing'||
							$orderStatus != 'Processed'){
							$orderStatusFinal = 5;
							if($orderStatus == 'Processed' ||$orderStatus == 'Complete' ){}else{
								 $this->model_checkout_order->addHistory($order_id, $orderStatusFinal);
							}
						}
					}break;
					case 2:{
						if ($orderStatus != 'Canceled'){
							$this->model_checkout_order->addHistory($order_id, 7);
						}
					}break;
					case 3:{
						if ($orderStatus != 'Pending'){
							$this->model_checkout_order->addHistory($order_id, 1);
						}
					}break;
					default:{
						$this->model_checkout_order->addHistory($order_id, 10);
					}break;
				}
				if(!$comfirmation){
					$this->response->redirect($this->url->link('checkout/success'));
				}else{
					echo 'success';
				}
	
			}else{
				$this->model_checkout_order->addHistory($order_id, 10);
				$this->response->redirect($this->url->link('checkout/failure'));
			}
		}else{
			$this->response->redirect($this->url->link('checkout/failure'));
		}
	
	}

	public function validateSignature($x_ref_payco,$x_transaction_id,$x_amount,$x_currency_code)
	{
		$merchan_id = $this->config->get('payment_epayco_api_key');
		$p_key = $this->config->get('payment_epayco_p_key');
		
		return hash('sha256',
			trim($merchan_id).'^'
			.trim($p_key).'^'
			.$x_ref_payco.'^'
			.$x_transaction_id.'^'
			.$x_amount.'^'
			.$x_currency_code
		);
	}

	public function getCustomerIp(){
		$ipaddress = '';
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_X_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		else if(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
		else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		else if(isset($_SERVER['REMOTE_ADDR']))
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;
	}

}