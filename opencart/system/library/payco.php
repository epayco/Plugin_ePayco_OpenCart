<?php
namespace Opencart\System\Library;

class Payco
{
	private $server = '';
	private $last_response = array();
	private $epayco_api_key = '';
	private $epayco_p_cust_id = '';

	private $epayco_dark_mode = false;
	private $epayco_test_mode = false;
	private $errors = array();

	//IN:  epayco info
	public function __construct($epayco_info)
	{
		if (!empty($epayco_info['payment_payco_api_key'])) {
			$this->epayco_api_key = $epayco_info['payment_payco_api_key'];
		}
		if (!empty($epayco_info['payment_payco_p_cust_id'])) {
			$this->epayco_p_cust_id = $epayco_info['payment_payco_p_cust_id'];
		}
		if (!empty($epayco_info['payment_payco_dark_mode_value'])) {
			$this->epayco_dark_mode = $epayco_info['payment_payco_dark_mode_value'];
		}
		if (!empty($epayco_info['payment_payco_test_mode'])) {
			$this->epayco_test_mode = $epayco_info['payment_payco_test_mode'];
		}

	}

	public function checkOrderStatus($order_id)
	{
		$command = 'transactions/' . $order_id;
		$result = $this->execute('GET', $command);
		if (!empty($result['id'])) {
			return $result;
		} else {
			$this->errors[] = $result;

			return false;
		}
	}

	public function addOrderDetails($order_id, $data)
	{
		$command = 'transactions/' . $order_id . '/set_order_details';
		$result = $this->execute('POST', $command, $data, true);
		if (!$result) {
			return $result;
		} else {
			$this->errors[] = $result;

			return false;
		}
	}

	//OUT: number of errors
	public function hasErrors()
	{
		return count($this->errors);
	}

	//OUT: array of errors
	public function getErrors()
	{
		return $this->errors;
	}

	//OUT: last response
	public function getResponse()
	{
		return $this->last_response;
	}

	private function execute($method, $command, $params = array(), $json = false)
	{
		$this->errors = array();

		if ($method && $command) {
			$curl_options = array(
				CURLOPT_URL => $this->server . $command,
				CURLOPT_HEADER => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_INFILESIZE => Null,
				CURLOPT_HTTPHEADER => array(),
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_TIMEOUT => 10
			);

			$curl_options[CURLOPT_HTTPHEADER][] = 'Accept-Charset: utf-8';
			$curl_options[CURLOPT_HTTPHEADER][] = 'Accept: application/json';
			$curl_options[CURLOPT_HTTPHEADER][] = 'Accept-Language: en_US';
			$curl_options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';

			switch (strtolower(trim($method))) {
				case 'get':
					$curl_options[CURLOPT_HTTPGET] = true;
					$curl_options[CURLOPT_URL] .= '?' . $this->buildQuery($params, $json);

					break;
				case 'post':
					$curl_options[CURLOPT_POST] = true;
					$curl_options[CURLOPT_POSTFIELDS] = $this->buildQuery($params, $json);

					break;
				case 'patch':
					$curl_options[CURLOPT_POST] = true;
					$curl_options[CURLOPT_POSTFIELDS] = $this->buildQuery($params, $json);
					$curl_options[CURLOPT_CUSTOMREQUEST] = strtoupper($method);

					break;
				case 'delete':
					$curl_options[CURLOPT_POST] = true;
					$curl_options[CURLOPT_POSTFIELDS] = $this->buildQuery($params, $json);
					$curl_options[CURLOPT_CUSTOMREQUEST] = strtoupper($method);

					break;
				case 'put':
					$curl_options[CURLOPT_PUT] = true;

					if ($params) {
						if ($buffer = fopen('php://memory', 'w+')) {
							$params_string = $this->buildQuery($params, $json);
							fwrite($buffer, $params_string);
							fseek($buffer, 0);
							$curl_options[CURLOPT_INFILE] = $buffer;
							$curl_options[CURLOPT_INFILESIZE] = strlen($params_string);
						} else {
							$this->errors[] = array('name' => 'FAILED_OPEN_TEMP_FILE', 'message' => 'Unable to open a temporary file');
						}
					}

					break;
				case 'head':
					$curl_options[CURLOPT_NOBODY] = true;

					break;
				default:
					$curl_options[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
			}

			$ch = curl_init();
			curl_setopt_array($ch, $curl_options);
			$response = curl_exec($ch);

			if (curl_errno($ch)) {
				$curl_code = curl_errno($ch);

				$constant = get_defined_constants(true);
				$curl_constant = preg_grep('/^CURLE_/', array_flip($constant['curl']));

				$this->errors[] = array('name' => $curl_constant[$curl_code], 'message' => curl_strerror($curl_code));
			}



			$head = '';
			$body = '';

			$parts = explode("\r\n\r\n", $response, 3);

			if (isset($parts[0]) && isset($parts[1])) {
				if (($parts[0] == 'HTTP/1.1 100 Continue') && isset($parts[2])) {
					list($head, $body) = array($parts[1], $parts[2]);
				} else {
					list($head, $body) = array($parts[0], $parts[1]);
				}
			}

			$response_headers = array();
			$header_lines = explode("\r\n", $head);
			array_shift($header_lines);

			foreach ($header_lines as $line) {
				list($key, $value) = explode(':', $line, 2);
				$response_headers[$key] = $value;
			}

			curl_close($ch);

			if (isset($buffer) && is_resource($buffer)) {
				fclose($buffer);
			}

			$this->last_response = json_decode($body, true);

			return $this->last_response;
		}
	}

	private function buildQuery($params, $json)
	{
		if (is_string($params)) {
			return $params;
		}

		if ($json) {
			return json_encode($params);
		} else {
			return http_build_query($params);
		}
	}

	private function token($length = 32)
	{
		// Create random token
		$string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

		$max = strlen($string) - 1;

		$token = '';

		for ($i = 0; $i < $length; $i++) {
			$token .= $string[mt_rand(0, $max)];
		}

		return $token;
	}
}