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

		$data['action'] = $this->url->link('extension/payment/payco', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        if (isset($this->request->post['payment_payco_status'])) {
            $data['payment_payco_status'] = $this->request->post['payment_payco_status'];
        } else {
            $data['payment_payco_status'] = $this->config->get('payment_payco_status');
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

        if (isset($this->request->post['payment_payco_merchant'])) {
            $data['payment_payco_merchant'] = trim($this->request->post['payment_payco_merchant']);
        } else {
            $data['payment_payco_merchant'] = trim($this->config->get('payment_payco_merchant'));
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

        if (isset($this->request->post['payment_payco_test'])) {
            $data['payment_payco_test'] = $this->request->post['payment_payco_test'];
        } else {
            $data['payment_payco_test'] = $this->config->get('payment_payco_test');
        }

        if (isset($this->request->post['payment_payco_language'])) {
            $data['payment_payco_language'] = $this->request->post['payment_payco_language'];
        } else {
            $data['payment_payco_language'] = $this->config->get('payment_payco_language');
        }

        if (isset($this->request->post['payment_payco_checkout_type'])) {
            $data['payment_payco_checkout_type'] = $this->request->post['payment_payco_checkout_type'];
        } else {
            $data['payment_payco_checkout_type'] = $this->config->get('payment_payco_checkout_type');
        }

        if (isset($this->request->post['payment_payco_order_status_id'])) {
            $data['payment_payco_order_status_id'] = $this->request->post['payment_payco_order_status_id'];
        } else {
            $data['payment_payco_order_status_id'] = $this->config->get('payment_payco_order_status_id');
        }

        if (isset($this->request->post['payment_payco_final_order_status_id'])) {
            $data['payment_payco_final_order_status_id'] = $this->request->post['payment_payco_final_order_status_id'];
        } else {
            $data['payment_payco_final_order_status_id'] = $this->config->get('payment_payco_final_order_status_id');
        }


		if (isset($this->request->post['payment_payco_total'])) {
			$data['payment_payco_total'] = $this->request->post['payment_payco_total'];
		} else {
			$data['payment_payco_total'] = $this->config->get('payment_payco_total');
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

        if (!$this->request->post['payment_payco_merchant']) {
            $this->error['merchant'] = $this->language->get('error_merchant');
        }

        if (!$this->request->post['payment_payco_key']) {
            $this->error['key'] = $this->language->get('error_key');
        }

        if (!$this->request->post['payment_payco_public_key']) {
            $this->error['public_key'] = $this->language->get('error_public_key');
        }

		return !$this->error;
	}
}