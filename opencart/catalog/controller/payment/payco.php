<?php
namespace Opencart\Catalog\Controller\Extension\payco\Payment;
class Payco extends \Opencart\System\Engine\Controller {
  private $extension_base_path = 'extension/payco/payment/payco';
  
  public function index() {
    $this->language->load($this->extension_base_path);
    
		$data['xpay_client_key'] = $this->config->get('payment_payco_client_key');

		$data['form_submit'] = $this->url->link($this->extension_base_path.'/send', '', true);
  
    $this->load->model('checkout/order');
    $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
    $data['p_cust_id_cliente'] = $this->config->get('payment_payco_merchant_id');
    $data['p_timestamp'] = time();
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
    $data['p_public_key'] = $this->config->get('payment_payco_public_key');
    $data['p_itemname']="";
    if($this->config->get('payment_payco_type_checkout') == 1){
      $data['p_payco_checkout_type'] = 'true';
    }else{
      $data['p_payco_checkout_type'] = 'false';
    }
    
    foreach ($this->cart->getProducts() as $product) {
        if(trim($product['name'])!=""){
            if($data['p_itemname']==""){
                $data['p_itemname']=$product['name'];
            }else{
                $data['p_itemname'] = $data['p_itemname'].",".$product['name'];
            }
        }
    }
    if ((int)$this->config->get('payment_payco_test_mode') == 1) {
      $data['p_test_mode'] = 'true';
    } else {
        $data['p_test_mode'] = 'false';
    }
    $data['language'] = $this->config->get('config_language');
    $data['p_lang'] = ( $this->config->get('config_language') === "en-gb" ) ? "en" : 'es';
    $countryCode = html_entity_decode($order_info['shipping_iso_code_2'], ENT_QUOTES, 'UTF-8')?html_entity_decode($order_info['shipping_iso_code_2'], ENT_QUOTES, 'UTF-8'): "CO";
    $data['p_currency_code'] = $order_info['currency_code'];;
    $data['p_id_invoice'] = $this->session->data['order_id'];
    $data['p_description'] = html_entity_decode( $this->language->get('text_payment_description').$this->session->data['order_id']. ' '.$this->language->get('text_payment_description_in').' '.$this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
    $data['p_billing_first_name'] = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');
    $data['p_billing_last_name'] = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
    $data['p_billing_company'] = html_entity_decode($order_info['payment_company'], ENT_QUOTES, 'UTF-8');
    $data['p_billing_address'] = html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8') . ' ' . html_entity_decode($order_info['payment_address_2'], ENT_QUOTES, 'UTF-8');
    $data['p_billing_city'] = html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');
    $data['p_billing_state'] = html_entity_decode($order_info['payment_zone'], ENT_QUOTES, 'UTF-8');
    $data['p_billing_zip'] = html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');
    $data['p_billing_country'] = html_entity_decode($order_info['payment_country'], ENT_QUOTES, 'UTF-8');
    $data['p_billing_phone'] = $order_info['telephone'];
    $data['p_shiping_first_name'] = html_entity_decode($order_info['shipping_firstname'], ENT_QUOTES, 'UTF-8');
    $data['p_shiping_last_name'] = html_entity_decode($order_info['shipping_lastname'], ENT_QUOTES, 'UTF-8');
    $data['p_shiping_company'] = html_entity_decode($order_info['shipping_company'], ENT_QUOTES, 'UTF-8');
    $data['p_shiping_address'] = html_entity_decode($order_info['shipping_address_1'], ENT_QUOTES, 'UTF-8') . ' ' . html_entity_decode($order_info['shipping_address_2'], ENT_QUOTES, 'UTF-8');
    $data['p_shiping_city'] = html_entity_decode($order_info['shipping_city'], ENT_QUOTES, 'UTF-8');
    $data['p_shiping_state'] = html_entity_decode($order_info['shipping_zone'], ENT_QUOTES, 'UTF-8');
    $data['p_shiping_zip'] = html_entity_decode($order_info['shipping_postcode'], ENT_QUOTES, 'UTF-8');
    $data['p_shiping_country'] = $countryCode;
    $data['p_customer_ip'] = $this->request->server['REMOTE_ADDR'];
    $data['p_email'] = $order_info['email'];
    $data['p_extra1'] = 'OpenCart V 3.0.2';
    $data['p_url_response'] = $this->url->link($this->extension_base_path.'|callback');
    $data['p_url_confirmation'] = $this->url->link($this->extension_base_path.'|callback&comfirmation=1');
    $data['p_url_confirm'] = $this->url->link($this->extension_base_path.'/confirm');
    $data['button_confirm'] = $this->language->get('button_confirm');
    $data['success_message'] = $this->language->get('success_message');
    $data['scan_code'] = $this->language->get('scan_code');
    $data['enter_phone'] = $this->language->get('enter_phone');
    $data['enter_phone_two'] = $this->language->get('enter_phone_two');
    $data['pay'] = $this->language->get('pay');
    $data['time_out'] = $this->language->get('time_out');
    $data['retry'] = $this->language->get('retry');
    $data['phone_number'] = $this->language->get('phone_number');
    $data['status_codes'] = $this->language->get('status_codes');
    $data['settled_codes'] = $this->language->get('settled_codes');
    
    if ($order_info) {
      $data['orderid'] = date('His') . $this->session->data['order_id'];
      $data['callbackurl'] = $this->url->link($this->extension_base_path.'|callback');
      $data['orderdate'] = date('YmdHis');
      $data['currency'] = $order_info['currency_code'];
      $data['orderamount'] = $this->currency->format($order_info['total'], $data['currency'] , false, false);
      $data['billemail'] = $order_info['email'];
      $data['billphone'] = html_entity_decode($order_info['telephone'], ENT_QUOTES, 'UTF-8');
      $data['billaddress'] = html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8');
      $data['billcountry'] = html_entity_decode($order_info['payment_iso_code_2'], ENT_QUOTES, 'UTF-8');
      $data['billprovince'] = html_entity_decode($order_info['payment_zone'], ENT_QUOTES, 'UTF-8');
      $data['billcity'] = html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');
      $data['billpost'] = html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');
      $data['deliveryname'] = html_entity_decode($order_info['shipping_firstname'] . $order_info['shipping_lastname'], ENT_QUOTES, 'UTF-8');
      $data['deliveryaddress'] = html_entity_decode($order_info['shipping_address_1'], ENT_QUOTES, 'UTF-8');
      $data['deliverycity'] = html_entity_decode($order_info['shipping_city'], ENT_QUOTES, 'UTF-8');
      $data['deliverycountry'] = html_entity_decode($order_info['shipping_iso_code_2'], ENT_QUOTES, 'UTF-8');
      $data['deliveryprovince'] = html_entity_decode($order_info['shipping_zone'], ENT_QUOTES, 'UTF-8');
      $data['deliveryemail'] = $order_info['email'];
      $data['deliveryphone'] = html_entity_decode($order_info['telephone'], ENT_QUOTES, 'UTF-8');
      $data['deliverypost'] = html_entity_decode($order_info['shipping_postcode'], ENT_QUOTES, 'UTF-8');

      $this->load->model($this->extension_base_path);
      $data['purchasecode_id'] = '';
      $data['purchase_code_url'] = $this->url->link($this->extension_base_path);
      $data['code_table'] = '';
      
      return $this->load->view($this->extension_base_path, $data);
    }
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
    if(isset($_GET['comfirmation'])){
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

        if ((int)$this->config->get('payment_payco_test_mode') == 1) {
            $isTestPluginMode = 'yes';
        } else {
            $isTestPluginMode = 'no';
        }
        if(floatval($data_p_amount) == floatval($x_amount)){
            if("yes" == $isTestPluginMode){
                $validation = true;
            }else{
                if($x_approval_code != "000000" && $x_cod_transaction_state == 1){
                    $validation = true;
                }else{
                    if($x_cod_transaction_state != 1){
                        $validation = true;
                    }else{
                        $validation = false;
                    }
                }
            }
        }else{
            $validation = false;
        }
        if($x_signature==$signature && $validation){
            switch ((int)$x_cod_response) {
                case 1:{
                    if ($orderStatus != 'Pending' ||
                        $orderStatus != 'Complete'||
                        $orderStatus != 'Processing'||
                        $orderStatus != 'Processed'){
                        $orderStatusFinal = $this->config->get('payment_payco_final_order_status_id');
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

  public function createOrder() {

    $json = [];

		if (!isset($this->session->data['order_id'])) {
			$json['error'] = $this->language->get('error_order');
		}

		if (!isset($this->session->data['payment_method']) || $this->session->data['payment_method'] != 'payco') {
			$json['error'] = $this->language->get('error_payment_method');
		}

		if (!$json) {
			$this->load->model('checkout/order');

      $this->model_checkout_order->addHistory($this->session->data['order_id'], $this->config->get('payment_payco_order_status_id'), '', true);
      $json['redirect'] = $this->url->link('checkout/success', 'language=' . $this->config->get('config_language'), true);
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

  public function validateSignature($x_ref_payco,$x_transaction_id,$x_amount,$x_currency_code)
	{
		$merchan_id = $this->config->get('payment_payco_merchant_id');
		$p_key = $this->language->get('payment_payco_merchant_key');
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

?>