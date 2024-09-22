<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;


class Iyzico_For_WooCommerce_Blocks extends AbstractPaymentMethodType
{
	protected $name = 'iyzico_blocks_for_woocommerce_gateway';
	private $gateways = [];

	public function initialize()
	{
		$this->gateways['iyzico'] = 'Iyzico_Checkout_For_WooCommerce_Gateway';
		$this->gateways['iyzico_pwi'] = 'Iyzico_PWI_For_WooCommerce_Gateway';
	}

	public function is_active()
	{
		$payment_methods = $this->get_payment_method_infos();

		if (count($payment_methods) > 0) {
			return true;
		}

		return false;
	}

	public function get_payment_method_script_handles()
	{
		wp_register_script(
			'iyzico_blocks_for_woocommerce_gateway_script',
			plugin_dir_url(__DIR__) . 'media/js/checkout.js',
			[
				'wc-blocks-registry',
				'wc-settings',
				'wp-element',
				'wp-html-entities',
				'wp-i18n',
			],
			'1.5',
			true
		);

		if (function_exists('wp_set_script_translations')) {
			wp_set_script_translations('iyzico_blocks_for_woocommerce_gateway_script', 'woocommerce-iyzico');
		}

		return ['iyzico_blocks_for_woocommerce_gateway_script'];
	}

	public function get_payment_method_data()
	{
		$data = $this->get_payment_method_infos();
		return $data;
	}

	public function get_payment_method_infos()
	{
		$payment_gateways = WC_Payment_Gateways::instance();
		$available_gateways = $payment_gateways->payment_gateways();

		$data = [];

		foreach ($available_gateways as $gateway) {
			if (in_array($gateway->id, array_keys($this->gateways))) {
				if ($gateway->enabled === 'yes') {
					$data[$gateway->id] = [
						'title' => $gateway->title ? $gateway->title : "Alışverişlerini hızla ve kolayca iyzico ile Öde!",
						'description' => $gateway->description,
						'logo_url' => plugin_dir_url(__DIR__) . "image/" . ($gateway->id == "iyzico_pwi" ? "pwi_tr.png" : "cards.png")
					];
				}
			}
		}

		return $data;
	}
}
