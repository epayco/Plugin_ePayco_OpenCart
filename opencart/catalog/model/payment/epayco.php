<?php
namespace Opencart\Catalog\Model\Extension\Epayco\Payment;

class Epayco extends \Opencart\System\Engine\Model
{
	/**
	 * @param array $address
	 *
	 * @return array
	 */
	public function getMethods(array $address = []): array
	{
		$method_data = [];
		
		$this->load->language('extension/epayco/payment/epayco');
		if(!empty($address)){
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('payment_epayco_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");
			if ($this->cart->hasSubscription()) {
				$status = false;
			} elseif (!$this->config->get('payment_epayco_geo_zone_id')) {
				$status = true;
			} elseif ($query->num_rows) {
				$status = true;
			} else {
				$status = false;
			}
			if ($status) {
				$method_data = [
					'code'       => 'epayco',
					'title'       => $this->language->get('heading_title'),
					'sort_order' => $this->config->get('payment_epayco_sort_order')
				];
			}
		}else{
			
			$total = $this->cart->getTotal();

			if (!empty($this->session->data['vouchers'])) {
				$amounts = array_column($this->session->data['vouchers'], 'amount');
			} else {
				$amounts = [];
			}

			$total = $total + array_sum($amounts);

			if ((float)$total <= 0.00) {
				$status = true;
			} elseif ($this->cart->hasSubscription()) {
				$status = false;
			} else {
				$status = false;
			}
			if ($status) {
				$option_data['epayco'] = [
					'code' => 'epayco.epayco',
					'name' => $this->language->get('heading_title')
				];
				$method_data = [
					'code'       => 'epayco',
					'name'       => $this->language->get('heading_title'),
					'option'     => $option_data,
					'sort_order' => $this->config->get('payment_epayco_sort_order')
				];
			}
		}
		
		return $method_data;
	}
}