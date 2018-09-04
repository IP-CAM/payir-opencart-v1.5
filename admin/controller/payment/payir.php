<?php 

class ControllerPaymentPayir extends Controller
{
	private $error = array ();

	public function index()
	{
		$this->load->language('payment/payir');
		$this->load->model('setting/setting');

		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {

			$this->model_setting_setting->editSetting('payir', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');

		$this->data['entry_api'] = $this->language->get('entry_api');
		$this->data['entry_send'] = $this->language->get('entry_send');
		$this->data['entry_verify'] = $this->language->get('entry_verify');
		$this->data['entry_gateway'] = $this->language->get('entry_gateway');
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['help_encryption'] = $this->language->get('help_encryption');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		$this->data['tab_general'] = $this->language->get('tab_general');

		$this->data['breadcrumbs'] = array ();

		$this->data['breadcrumbs'][] = array (

			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array (

			'text' => $this->language->get('text_payment'),
			'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array (

			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('payment/payir', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('payment/payir', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->error['warning'])) {

			$this->data['error_warning'] = $this->error['warning'];

		} else {

			$this->data['error_warning'] = false;
		}

		if (isset($this->error['api'])) {

			$this->data['error_api'] = $this->error['api'];

		} else {

			$this->data['error_api'] = false;
		}

		if (isset($this->error['send'])) {

			$this->data['error_send'] = $this->error['send'];

		} else {

			$this->data['error_send'] = false;
		}

		if (isset($this->error['verify'])) {

			$this->data['error_verify'] = $this->error['verify'];

		} else {

			$this->data['error_verify'] = false;
		}

		if (isset($this->error['gateway'])) {

			$this->data['error_gateway'] = $this->error['gateway'];

		} else {

			$this->data['error_gateway'] = false;
		}

		if (isset($this->request->post['payir_api'])) {

			$this->data['payir_api'] = $this->request->post['payir_api'];

		} else {

			$this->data['payir_api'] = $this->config->get('payir_api');
		}

		if (isset($this->request->post['payir_send'])) {

			$this->data['payir_send'] = $this->request->post['payir_send'];

		} else {

			$this->data['payir_send'] = $this->config->get('payir_send');

			if(isset($this->data['payir_send'])){

				$this->data['payir_send'] = $this->data['payir_send'];

			} else {

				$this->data['payir_send'] = 'https://pay.ir/payment/send';

			}
		}

		if (isset($this->request->post['payir_verify'])) {

			$this->data['payir_verify'] = $this->request->post['payir_verify'];

		} else {

			$this->data['payir_verify'] = $this->config->get('payir_verify');

			if(isset($this->data['payir_verify'])){

				$this->data['payir_verify'] = $this->data['payir_verify'];

			} else {

				$this->data['payir_verify'] = 'https://pay.ir/payment/verify';
			}
		}

		if (isset($this->request->post['payir_gateway'])) {

			$this->data['payir_gateway'] = $this->request->post['payir_gateway'];

		} else {

			$this->data['payir_gateway'] = $this->config->get('payir_gateway');

			if(isset($this->data['payir_gateway'])){

				$this->data['payir_gateway'] = $this->data['payir_gateway'];

			} else {

				$this->data['payir_gateway'] = 'https://pay.ir/payment/gateway/';
			}
		}

		if (isset($this->request->post['payir_order_status_id'])) {

			$this->data['payir_order_status_id'] = $this->request->post['payir_order_status_id'];

		} else {

			$this->data['payir_order_status_id'] = $this->config->get('payir_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payir_status'])) {

			$this->data['payir_status'] = $this->request->post['payir_status'];

		} else {

			$this->data['payir_status'] = $this->config->get('payir_status');
		}

		if (isset($this->request->post['payir_sort_order'])) {

			$this->data['payir_sort_order'] = $this->request->post['payir_sort_order'];

		} else {

			$this->data['payir_sort_order'] = $this->config->get('payir_sort_order');
		}

		$this->template = 'payment/payir.tpl';

		$this->children = array (
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	private function validate()
	{
		if (!$this->user->hasPermission('modify', 'payment/payir')) {

			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payir_api']) {

			$this->error['warning'] = $this->language->get('error_validate');
			$this->error['api'] = $this->language->get('error_api');
		}

		if (!$this->request->post['payir_send']) {

			$this->error['warning'] = $this->language->get('error_validate');
			$this->error['send'] = $this->language->get('error_send');
		}

		if (!$this->request->post['payir_verify']) {

			$this->error['warning'] = $this->language->get('error_validate');
			$this->error['verify'] = $this->language->get('error_verify');
		}

		if (!$this->request->post['payir_gateway']) {

			$this->error['warning'] = $this->language->get('error_validate');
			$this->error['gateway'] = $this->language->get('error_gateway');
		}

		if (!$this->error) {

			return true;

		} else {

			return false;
		}
	}
}