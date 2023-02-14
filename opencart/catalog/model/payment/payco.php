<?php
namespace Opencart\Catalog\Model\Extension\payco\Payment;
class payco extends \Opencart\System\Engine\Model {
  private $js_base_path = 'extension/payco/catalog/view/javascript/';
  public function getMethod(array $address): array{
    $this->load->language('extension/payco/payment/payco');
  
    $method_data = [
      'code' => 'payco',
      'title' => $this->config->get('payment_payco_payment_title') ? $this->config->get('payment_payco_payment_title') : 'ePayco',
      'terms' => '',
      'sort_order' => $this->config->get('payment_payco_sort_order')
    ];

    return $method_data;
  }
}