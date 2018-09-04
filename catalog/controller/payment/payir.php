<?php

class ControllerPaymentPayir extends Controller
{
	public function index()
	{
		$this->load->language('payment/payir');
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$amount     = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

		if ($this->currency->getCode() != 'RLS' && $this->currency->getCode() != 'IRR') {

			$amount = $amount * 10;
		}

		$this->data['error_warning'] = false;

		$this->data['button_confirm'] = $this->language->get('button_confirm');

		if (extension_loaded('curl')) {

			$parameters = array (
				'api' => $this->config->get('payir_api'),
				'amount' => $amount,
				'redirect' => urlencode($this->url->link('payment/payir/callback', 'order_id=' . $order_info['order_id'], '', 'SSL')),
				'factorNumber' => $order_info['order_id']
			);

			$result = $this->common($this->config->get('payir_send'), $parameters);
			$result = json_decode($result);

			if (isset($result->status) && $result->status == 1) {

				$this->data['action'] = $this->config->get('payir_gateway') . $result->transId;

			} else {

				$code = isset($result->errorCode) ? $result->errorCode : 'Undefined';
				$message = isset($result->errorMessage) ? $result->errorMessage : $this->language->get('error_undefined');

				$this->data['error_warning'] = $this->language->get('error_request') . '<br/><br/>' . $this->language->get('error_code') . $code . '<br/>' . $this->language->get('error_message') . $message;
			}

		} else {

			$this->data['error_warning'] = $this->language->get('error_curl');
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/payir.tpl')) {

			$this->template = $this->config->get('config_template') . '/template/payment/payir.tpl';

		} else {

			$this->template = 'default/template/payment/payir.tpl';
		}

		$this->response->setOutput($this->render());
	}

	public function callback()
	{
		$this->load->language('payment/payir');
		$this->load->model('checkout/order');

		$this->document->setTitle($this->language->get('heading_title'));

		$order_id = isset($this->session->data['order_id']) ? $this->session->data['order_id'] : false;	
		$order_id = isset($order_id) ? $order_id : $this->request->get['order_id'];

		$order_info = $this->model_checkout_order->getOrder($order_id);
		$amount     = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

		if ($this->currency->getCode() != 'RLS' && $this->currency->getCode() != 'IRR') {

			$amount = $amount * 10;
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['button_continue'] = $this->language->get('button_continue');
		$this->data['continue'] = $this->url->link('common/home', '', 'SSL');

		$this->data['error_warning'] = false;

		$this->data['continue'] = $this->url->link('checkout/cart', '', 'SSL');

		if ($this->request->post['status'] && $this->request->post['transId'] && $this->request->post['factorNumber']) {

			$status = $this->request->post['status'];
			$trans_id = $this->request->post['transId'];
			$factor_number = $this->request->post['factorNumber'];
			$message = $this->request->post['message'];

			if (isset($status) && $status == 1) {

				if ($order_id == $factor_number && $factor_number == $order_info['order_id']) {

					$parameters = array (
						'api' => $this->config->get('payir_api'),
						'transId' => $trans_id
					);

					$result = $this->common($this->config->get('payir_verify'), $parameters);
					$result = json_decode($result);

					if (isset($result->status) && $result->status == 1) {

						if ($amount == $result->amount) {

							$this->model_checkout_order->confirm($order_info['order_id'], $this->config->get('payir_order_status_id'), $trans_id);

						} else {

							$this->data['error_warning'] = $this->language->get('error_amount');
						}

					} else {

						$code = isset($result->errorCode) ? $result->errorCode : 'Undefined';
						$message = isset($result->errorMessage) ? $result->errorMessage : $this->language->get('error_undefined');

						$this->data['error_warning'] =  $this->language->get('error_request') . '<br/><br/>' . $this->language->get('error_code') . $code . '<br/>' . $this->language->get('error_message') . $message;
					}

				} else {

					$this->data['error_warning'] = $this->language->get('error_invoice');
				}

			} else {

				$this->data['error_warning'] = $this->language->get('error_payment');
			}

		} else {

			$this->data['error_warning'] = $this->language->get('error_data');
		}

		if ($this->data['error_warning']) {

			$this->data['breadcrumbs'] = array ();

			$this->data['breadcrumbs'][] = array (
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home', '', 'SSL'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array (
				'text' => $this->language->get('text_basket'),
				'href' => $this->url->link('checkout/cart', '', 'SSL'),
				'separator' => ' » '
			);
		
			$this->data['breadcrumbs'][] = array (
				'text' => $this->language->get('text_checkout'),
				'href' => $this->url->link('checkout/checkout', '', 'SSL'),
				'separator' => ' » '
			);

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/payir_callback.tpl')) {

				$this->template = $this->config->get('config_template') . '/template/payment/payir_callback.tpl';

			} else {

				$this->template = 'default/template/payment/payir_callback.tpl';
			}

			$this->children = array (
				'common/header',
				'common/footer'
			);

			$this->response->setOutput($this->render());

		} else {

			$this->redirect($this->url->link('checkout/success', '', 'SSL'));
		}
	}

	protected function common($url, $parameters)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));

		$response = curl_exec($ch);
		curl_close($ch);

		return $response;
	}
}
?>
