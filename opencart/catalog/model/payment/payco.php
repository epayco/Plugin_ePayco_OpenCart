<?php
namespace Opencart\Catalog\Model\Extension\Payco\Payment;

class Payco extends \Opencart\System\Engine\Model
{
	public function getMethod(array $address): array
	{
		$this->load->language('extension/payco/payment/payco');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('payment_payco_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

		if ($this->cart->hasSubscription()) {
			$status = false;
		} elseif (!$this->config->get('payment_payco_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$method_data = [
				'code' => 'payco',
				'title' => $this->language->get('text_title'),
				'sort_order' => $this->config->get('payment_payco_sort_order')
			];
		}

		return $method_data;
	}

	public function log(array $data = [], string $title = ''): void
	{
		if ($this->config->get('payment_payco_debug')) {
			$log = new \Opencart\System\Library\Log('payco.log');
			$log->write('payco debug (' . $title . '): ' . json_encode($data));
		}
	}
  
}