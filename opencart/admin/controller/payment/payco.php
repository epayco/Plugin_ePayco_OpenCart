<?php
namespace Opencart\Admin\Controller\Extension\payco\Payment;
class Payco extends \Opencart\System\Engine\Controller {
  private $error = array();
 
  public function index() {
    $this->load->language('extension/payco/payment/payco');
    $this->load->model('setting/setting');
    $prefix = 'payment_payco';
    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
      $config_store = array();
      foreach($this->request->post as $field_id => $field_val){
        $config_store[$prefix.'_'.$field_id] = $field_val;
      }
      $this->model_setting_setting->editSetting($prefix, $config_store);
      $this->session->data['success'] = $this->language->get('text_success');;
      $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
    }

    if (isset($this->error['error_service_key'])) {
			$data['error_service_key'] = $this->error['error_service_key'];
		} else {
			$data['error_service_key'] = '';
		}

		if (isset($this->error['error_client_key'])) {
			$data['error_client_key'] = $this->error['error_client_key'];
		} else {
			$data['error_client_key'] = '';
		}

    if (isset($this->error['error_client_public_key'])) {
			$data['error_client_public_key'] = $this->error['error_client_public_key'];
		} else {
			$data['error_client_public_key'] = '';
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
			'href' => $this->url->link('extension/payco/payment/payco', 'user_token=' . $this->session->data['user_token'], true)
		);
    $data['heading_title']          = $this->language->get('heading_title');
    $data['button_save']            = $this->language->get('text_button_save');
    $data['button_cancel']          = $this->language->get('text_button_cancel');
    $data['entry_order_status']     = $this->language->get('entry_order_status');
    $data['text_enabled']           = $this->language->get('text_enabled');
    $data['text_disabled']          = $this->language->get('text_disabled');
    $data['entry_status']           = $this->language->get('entry_status');
    $data['entry_sort_order']       = $this->language->get('entry_sort_order');
    $data['text_extension']         = $this->language->get('text_extension');
    $data['entry_xpay_sort_order']  = $this->language->get('entry_xpay_sort_order');
    $data['entry_payment_title']    = $this->language->get('entry_payment_title');
    $data['entry_merchant_id']      = $this->language->get('entry_merchant_id');
    $data['entry_merchant_key']     = $this->language->get('entry_merchant_key');
    $data['entry_public_key']       = $this->language->get('entry_public_key');
    $data['entry_type_checkout']    = $this->language->get('entry_type_checkout');
    $data['text_message']           = $this->language->get('text_message');
    $data['help_merchant_id']       = $this->language->get('help_merchant_id');
    $data['help_public_key']        = $this->language->get('help_public_key');
    $data['help_type_checkout']     = $this->language->get('help_type_checkout');
    $data['action']                 = $this->url->link('extension/payco/payment/payco', 'user_token=' . $this->session->data['user_token'], true);
    $data['cancel']                 = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);
 
    if (isset($this->request->post['payment_title'])) {
      $data['payment_title'] = $this->request->post['payment_title'];
    } else {
      $data['payment_title'] = $this->config->get('payment_payco_payment_title');
    }
    
    if (isset($this->request->post['merchant_id'])) {
      $data['merchant_id'] = $this->request->post['merchant_id'];
    } else {
      $data['merchant_id'] = $this->config->get('payment_payco_merchant_id');
    }
        
    if (isset($this->request->post['merchant_key'])) {
      $data['merchant_key'] = $this->request->post['merchant_key'];
    } else {
      $data['merchant_key'] = $this->config->get('payment_payco_merchant_key');
    }
    
    if (isset($this->request->post['public_key'])) {
      $data['public_key'] = $this->request->post['public_key'];
    } else {
      $data['public_key'] = $this->config->get('payment_payco_public_key');
    }
            
    
    if (isset($this->request->post['status'])) {
      $data['status'] = $this->request->post['status'];
    } else {
      $data['status'] = $this->config->get('payment_payco_status');
    }

    if (isset($this->request->post['order_status_id'])) {
      $data['order_status_id'] = $this->request->post['order_status_id'];
    } else {
      $data['order_status_id'] = $this->config->get('payment_payco_order_status_id');
    }
        
    if (isset($this->request->post['sort_order'])) {
      $data['sort_order'] = $this->request->post['sort_order'];
    } else {
      $data['sort_order'] = $this->config->get('payment_payco_sort_order');
    }

    if (isset($this->request->post['test_mode'])) {
      $data['test_mode'] = $this->request->post['test_mode'];
    } else {
      $data['test_mode'] = $this->config->get('payment_payco_test_mode');
    }

    if (isset($this->request->post['type_checkout'])) {
      $data['type_checkout'] = $this->request->post['type_checkout'];
    } else {
      $data['type_checkout'] = $this->config->get('payment_payco_type_checkout');
    }

 
    $this->load->model('localisation/order_status');
    $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
            
    $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
 
    $this->response->setOutput($this->load->view('extension/payco/payment/payco', $data));
  }

  protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payco/payment/payco')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['merchant_id']) {
			$this->error['error_service_key'] = $this->language->get('error_service_key');
		}

		if (!$this->request->post['merchant_key']) {
			$this->error['error_client_key'] = $this->language->get('error_client_key');
		}
		if (!$this->request->post['public_key']) {
			$this->error['error_client_public_key'] = $this->language->get('error_client_public_key');
		}

		return !$this->error;
	}
}