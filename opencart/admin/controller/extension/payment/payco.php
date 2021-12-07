<?php
class ControllerExtensionPaymentPayco extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/payco');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_payco', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));

		}



		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "epayco_order` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `order_id` INT(11),
            `is_test` INT(11) NOT NULL DEFAULT '0',
            `discount` TINYINT(1) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
		

		$this->load->model('localisation/order_status');
		$epaycoOrderStatus = [
            "Complete test",
            "Canceled test",
            "Processing test",
            "Failed test",
            "Pending test"
        ];
		$queryw = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE name = 'Pending'");
        foreach ( $epaycoOrderStatus as $status){
			$this->db->query("INSERT INTO " . DB_PREFIX . "order_status (name, language_id)  SELECT * FROM (SELECT '" . $this->db->escape($status) . "','" . (int)$queryw->row["order_status_id"] . "') AS tmp
			WHERE NOT EXISTS (
				SELECT name FROM " . DB_PREFIX . "order_status  WHERE name = '" . $this->db->escape($status) . "'
			) LIMIT 1;  
			");
        }


		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_info'] = $this->language->get('text_info');

		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');

		$data['entry_title'] = $this->language->get('entry_title');
		$data['entry_title_description'] = $this->language->get('entry_title_description');
		$data['entry_description'] = $this->language->get('entry_description');
		$data['entry_description_description'] = $this->language->get('entry_description_description');
		$data['entry_merchant'] = $this->language->get('entry_merchant');
		$data['entry_merchant_description'] = $this->language->get('entry_merchant_description');
		$data['entry_key'] = $this->language->get('entry_key');
		$data['entry_key_description'] = $this->language->get('entry_key_description');
		$data['entry_public_key'] = $this->language->get('entry_public_key');
		$data['entry_public_key_description'] = $this->language->get('entry_public_key_description');
		$data['entry_checkout_type'] = $this->language->get('entry_checkout_type');
		$data['entry_checkout_type_description'] = $this->language->get('entry_checkout_type_description');
		$data['entry_callback'] = $this->language->get('entry_callback');
		$data['entry_callback_description'] = $this->language->get('entry_callback_description');
		$data['entry_confirmation'] = $this->language->get('entry_confirmation');
		$data['entry_confirmation_description'] = $this->language->get('entry_confirmation_description');
		//$data['entry_md5'] = $this->language->get('entry_md5');
		//$data['entry_total'] = $this->language->get('entry_total');
		//$data['entry_comision'] = $this->language->get('entry_comision');
		//$data['entry_valor_comision'] = $this->language->get('entry_valor_comision');
		
		$data['entry_test'] = $this->language->get('entry_test');
		$data['entry_test_description'] = $this->language->get('entry_test_description');
		$data['entry_initial_order_status'] = $this->language->get('entry_initial_order_status');
		$data['entry_initial_order_status_description'] = $this->language->get('entry_initial_order_status_description');
		$data['entry_final_order_status'] = $this->language->get('entry_final_order_status');
		$data['entry_final_order_status_description'] = $this->language->get('entry_final_order_status_description');
		
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_status_description'] = $this->language->get('entry_status_description');
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

		if (isset($this->error['public_key'])) {
			$data['error_public_key'] = $this->error['public_key'];
		} else {
			$data['error_public_key'] = '';
		}

		if (isset($this->error['title'])) {
			$data['error_title'] = $this->error['title'];
		} else {
			$data['error_title'] = '';
		}

		if (isset($this->error['description'])) {
			$data['error_description'] = $this->error['description'];
		} else {
			$data['error_description'] = '';
		}

		if (isset($this->error['callback'])) {
			$data['error_callback'] = $this->error['callback'];
		} else {
			$data['error_callback'] = '';
		}

		if (isset($this->error['confirmation'])) {
			$data['error_confirmation'] = $this->error['confirmation'];
		} else {
			$data['error_confirmation'] = '';
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

		if ($this->config->get('payment_payco_callback')  === null) {
			$this->request->post['payment_payco_callback'] = HTTP_CATALOG . 'index.php?route=extension/payment/payco/callback&'; //permitir success
		}

		if ($this->config->get('payment_payco_confirmation') === null) {
			$this->request->post['payment_payco_confirmation'] = HTTP_CATALOG . 'index.php?route=extension/payment/payco/callback';
		} 

		if ($this->config->get('payment_payco_initial_order_status_id')  === null) {
			$this->request->post['payment_payco_initial_order_status_id'] = 1;
		}

		if ($this->config->get('payment_payco_final_order_status_id') === null) {
			$this->request->post['payment_payco_final_order_status_id'] = 5;
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
			'href' => $this->url->link('extension/payment/payco', 'user_token=' . $this->session->data['user_token'], true)
		);

		//links
		$data['action'] = $this->url->link('extension/payment/payco', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		if (isset($this->request->post['payment_payco_merchant'])) {
			$data['payment_payco_merchant'] = trim($this->request->post['payment_payco_merchant']);
		} else {
			$data['payment_payco_merchant'] = trim($this->config->get('payment_payco_merchant'));
		}

		if (isset($this->request->post['payment_payco_title'])) {
			$data['payment_payco_title'] = $this->request->post['payment_payco_title'];
		} else {
			if ($this->config->get('payment_payco_title') !== null) {
				$data['payment_payco_title'] = $this->config->get('payment_payco_title');
			} else {
				$data['payment_payco_title'] = $this->language->get('entry_title_default');
			}
		}

		if (isset($this->request->post['payment_payco_description'])) {
			$data['payment_payco_description'] = $this->request->post['payment_payco_description'];
		} else {
			if ($this->config->get('payment_payco_description') !== null) {
				$data['payment_payco_description'] = $this->config->get('payment_payco_description');
			} else {
				$data['payment_payco_description'] = $this->language->get('entry_description_default');
			}
		}

		if (isset($this->request->post['payment_payco_key'])) {
			$data['payment_payco_key'] = trim($this->request->post['payment_payco_key']);
		} else {
			$data['payment_payco_key'] = trim($this->config->get('payment_payco_key'));
		}

		if (isset($this->request->post['payment_payco_public_key'])) {
			$data['payment_payco_public_key'] = trim($this->request->post['payment_payco_public_key']);
		} else {
			$data['payment_payco_public_key'] = trim($this->config->get('payment_payco_public_key'));
		}

		if (isset($this->request->post['payment_payco_checkout_type'])) {
			$data['payment_payco_checkout_type'] = $this->request->post['payment_payco_checkout_type'];
		} else {
			$data['payment_payco_checkout_type'] = $this->config->get('payment_payco_checkout_type');
		}

		if (isset($this->request->post['payment_payco_comision'])) {
			$data['payment_payco_comision'] = $this->request->post['payment_payco_comision'];
		} else {
			$data['payment_payco_comision'] = $this->config->get('payment_payco_comision')?$this->config->get('payment_payco_comision'):2.99;
		}

		if (isset($this->request->post['payment_payco_valor_comision'])) {
			$data['payment_payco_valor_comision'] = $this->request->post['payment_payco_valor_comision'];
		} else {
			$data['payment_payco_valor_comision'] = $this->config->get('payment_payco_valor_comision')?$this->config->get('payment_payco_valor_comision'):600;
		}

		if (isset($this->request->post['payment_payco_test'])) {
			$data['payment_payco_test'] = $this->request->post['payment_payco_test'];
		} else {
			$data['payment_payco_test'] = $this->config->get('payment_payco_test');
		}

		if (isset($this->request->post['payment_payco_status'])) {
			$data['payment_payco_status'] = $this->request->post['payment_payco_status'];
		} else {
			$data['payment_payco_status'] = 0;
		}

		if (isset($this->request->post['payment_payco_callback'])) {
			$data['payment_payco_callback'] = $this->request->post['payment_payco_callback'];
		} else {
			$data['payment_payco_callback'] = $this->config->get('payment_payco_callback');
		}

		if (isset($this->request->post['payment_payco_confirmation'])) {
			$data['payment_payco_confirmation'] = $this->request->post['payment_payco_confirmation'];
		} else {
			$data['payment_payco_confirmation'] = $this->config->get('payment_payco_confirmation');

		}


		/*if (isset($this->request->post['payment_payco_md5'])) {
			$data['payment_payco_md5'] = $this->request->post['payment_payco_md5'];
		} else {
			$data['payment_payco_md5'] = $this->config->get('payment_payco_md5');
		}*/

		/*if (isset($this->request->post['payment_payco_total'])) {
			$data['payment_payco_total'] = $this->request->post['payment_payco_total'];
		} else {
			$data['payment_payco_total'] = $this->config->get('payment_payco_total');
		}*/

		if (isset($this->request->post['payment_payco_initial_order_status_id'])) {
			$data['payment_payco_initial_order_status_id'] = $this->request->post['payment_payco_initial_order_status_id'];
		} else {
			$data['payment_payco_initial_order_status_id'] = $this->config->get('payment_payco_initial_order_status_id');
		}

		if (isset($this->request->post['payment_payco_final_order_status_id'])) {
			$data['payment_payco_final_order_status_id'] = $this->request->post['payment_payco_final_order_status_id'];
		} else {
			$data['payment_payco_final_order_status_id'] = $this->config->get('payment_payco_final_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_payco_geo_zone_id'])) {
			$data['payment_payco_geo_zone_id'] = $this->request->post['payment_payco_geo_zone_id'];
		} else {
			$data['payment_payco_geo_zone_id'] = $this->config->get('payment_payco_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_payco_status'])) {
			$data['payment_payco_status'] = $this->request->post['payment_payco_status'];
		} else {
			$data['payment_payco_status'] = $this->config->get('payment_payco_status');
		}

		if (isset($this->request->post['payment_payco_sort_order'])) {
			$data['payment_payco_sort_order'] = $this->request->post['payment_payco_sort_order'];
		} else {
			$data['payment_payco_sort_order'] = $this->config->get('payment_payco_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/payco', $data));

	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/payco')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_payco_title']) {
			$this->error['title'] = $this->language->get('error_title');
		}

		if (!$this->request->post['payment_payco_description']) {
			$this->error['description'] = $this->language->get('error_description');
		}

		if (!$this->request->post['payment_payco_merchant']) {
			$this->error['merchant'] = $this->language->get('error_merchant');
		}

		if (!$this->request->post['payment_payco_key']) {
			$this->error['key'] = $this->language->get('error_key');
		}

		if (!$this->request->post['payment_payco_public_key']) {
			$this->error['public_key'] = $this->language->get('error_public_key');
		}

		if (!$this->request->post['payment_payco_callback']) {
			$this->request->post['payment_payco_callback'] = HTTP_CATALOG . 'index.php?route=extension/payment/payco/callback&'; //permitir success
		}

		if (!$this->request->post['payment_payco_confirmation']) {
			$this->request->post['payment_payco_confirmation'] = HTTP_CATALOG . 'index.php?route=extension/payment/payco/callback';
		}

		

		return !$this->error;
	}

	public function uninstall()
    {
       // $this->load->model('extension/payment/payco');
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('payment_payco');
    }
}