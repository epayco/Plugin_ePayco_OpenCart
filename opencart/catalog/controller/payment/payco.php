<?php
namespace Opencart\Catalog\Controller\Extension\Payco\Payment;

class Payco extends \Opencart\System\Engine\Controller
{
	private $error = [];
	private $extension_base_path = 'extension/payco/payment/payco';


	public function __construct($registry)
	{
		parent::__construct($registry);

		if (version_compare(phpversion(), '7.1', '>=')) {
			ini_set('precision', 14);
			ini_set('serialize_precision', 14);
		}
	}

	public function index(): string
	{
		if ($this->config->get('payment_payco_api_key')) {
			$this->load->language('extension/payco/payment/payco');
			$this->load->model('checkout/cart');
			$this->load->model('extension/payco/payment/payco');
			$this->load->model('localisation/country');
			$this->load->model('checkout/order');

			$country = $this->model_localisation_country->getCountry($this->config->get('config_country_id'));

			// Setting
			$_config = new \Opencart\System\Engine\Config();
			$_config->addPath(DIR_EXTENSION . 'payco/system/config/');
			$_config->load('payco');

			$config_setting = $_config->get('payco_setting');

			$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

			$setting = array_replace_recursive((array) $config_setting, (array) $this->config->get('payment_epayco_setting'));
			$data['payco_api_key_value'] = $this->config->get('payment_payco_api_key');
			$data['payment_payco_public_key'] = $this->config->get('payment_payco_public_key');
			$data['payment_payco_p_key'] = $this->config->get('payment_payco_p_key');
			$data['payment_payco_type_checkout'] = $this->config->get('payment_payco_type_checkout');
			$data['payco_dark_mode_value'] = $this->config->get('payment_payco_dark_mode_value');
			$data['payco_test_mode_value'] = $this->config->get('payment_payco_test_mode');
			$data['order_id'] = $this->session->data['order_id'];
			
			if ($data['payco_test_mode_value']=='1'){
				$data['p_test_mode'] = 'true';
			}else{
				$data['p_test_mode'] = 'false';
			}

			if ($data['payco_dark_mode_value']=='1'){
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


			return $this->load->view('extension/payco/payment/payco', $data);
		}

		return '';
	}


	public function confirm(): void {
		$this->load->language('extension/payco/payment/payco');

		$json = [];

		if (!isset($this->session->data['order_id'])) {
			$json['error'] = $this->language->get('error_order');
		}

		if (!isset($this->session->data['payment_method']) || $this->session->data['payment_method'] != 'payco') {
			$json['error'] = $this->language->get('error_payment_method');
		}

		if (!$json) {
			$comment  = $this->language->get('text_instruction') . "\n\n";
			$comment .= $this->language->get('text_payment');

			$this->load->model('checkout/order');

			$this->model_checkout_order->addHistory($this->session->data['order_id'],1, $comment, true);

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
			$url="https://secure.epayco.co/validation/v1/reference/".$_GET['ref_payco'];
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
		$merchan_id = $this->config->get('payment_payco_api_key');
		$p_key = $this->config->get('payment_payco_p_key');
		
		return hash('sha256',
			trim($merchan_id).'^'
			.trim($p_key).'^'
			.$x_ref_payco.'^'
			.$x_transaction_id.'^'
			.$x_amount.'^'
			.$x_currency_code
		);
	}
	
}