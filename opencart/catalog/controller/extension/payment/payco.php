<?php
class ControllerExtensionPaymentPayco extends Controller {
	public function index() {
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $data['p_cust_id_cliente'] = $this->config->get('payment_payco_merchant');
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
        if((int) $this->config->get('payment_payco_language') == 0){
            $lang = "en";
        }else{
            $lang = "es";
        }
        $countryCode = html_entity_decode($order_info['shipping_iso_code_2'], ENT_QUOTES, 'UTF-8')?html_entity_decode($order_info['shipping_iso_code_2'], ENT_QUOTES, 'UTF-8'): "CO";
        $data['p_lang'] = $lang;
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
        $data['p_url_response'] = !empty( $this->config->get('payment_payco_callback') ) ? $this->config->get('payment_payco_callback') : $this->url->link('extension/payment/payco/callback');
        $data['p_url_confirmation'] = !empty( $this->config->get('payment_payco_confirmation') ) ? $this->config->get('payment_payco_confirmation') : $this->url->link('extension/payment/payco/callback&comfirmation=1');
        $data['p_public_key'] = $this->config->get('payment_payco_public_key');
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
        if ((int) $this->config->get('payment_payco_test') == 1) {
            $data['p_test_mode'] = 'true';
        } else {
            $data['p_test_mode'] = 'false';
        }
        $data['p_payco_checkout_type'] = $this->config->get('payment_payco_checkout_type');

		return $this->load->view('extension/payment/payco', $data);
	}

	public function confirm() {
		$json = array();
		$orderStatusId = $this->config->get('payment_payco_order_status_id');
		if (isset($this->session->data['payment_method']['code']) && $this->session->data['payment_method']['code'] == 'payco') {
			$this->load->model('checkout/order');

			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_payco_order_status_id'));
		
			$json['redirect'] = $this->url->link('checkout/success');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

    public function callback(){
        $this->load->model('checkout/order');
        $comfirmation = false;
        if(isset($_GET['ref_payco'])){
            $ref_payco = $_GET['ref_payco'];
            $url="https://secure-green.payco.co/validation/v1/reference/".$_GET['ref_payco'];
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

            if ((int)$this->config->get('payment_payco_test') == 1) {
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
                                 $this->model_checkout_order->addOrderHistory($order_id, $orderStatusFinal);
                            }
                        }
                    }break;
                    case 2:{
                        if ($orderStatus != 'Canceled'){
                            $this->model_checkout_order->addOrderHistory($order_id, 7);
                        }
                    }break;
                    case 3:{
                        if ($orderStatus != 'Pending'){
                            $this->model_checkout_order->addOrderHistory($order_id, 1);
                        }
                    }break;
                    default:{
                        $this->model_checkout_order->addOrderHistory($order_id, 10);
                    }break;
                }
                if(!$comfirmation){
                    $this->response->redirect($this->url->link('checkout/success'));
                }else{
                    echo 'success';
                }

            }else{
                $this->model_checkout_order->addOrderHistory($order_id, 10);
                $this->response->redirect($this->url->link('checkout/failure'));
            }
        }else{
            $this->response->redirect($this->url->link('checkout/failure'));
        }

    }

    public function validateSignature($x_ref_payco,$x_transaction_id,$x_amount,$x_currency_code)
    {
        return hash('sha256',
            trim($this->config->get('payment_payco_merchant')).'^'
            .trim($this->config->get('payment_payco_key')).'^'
            .$x_ref_payco.'^'
            .$x_transaction_id.'^'
            .$x_amount.'^'
            .$x_currency_code
        );
    }
}
