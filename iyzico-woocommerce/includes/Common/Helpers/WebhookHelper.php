<?php

namespace Iyzico\IyzipayWoocommerce\Common\Helpers;
use Iyzico\IyzipayWoocommerce\Checkout\CheckoutSettings;
use Iyzico\IyzipayWoocommerce\Database\DatabaseManager;

class WebhookHelper
{
	private $checkoutSettings;
	private $status;
	private $paymentProcessor;
	private $logger;
	private $priceHelper;
	private $cookieManager;
	private $versionChecker;
	private $tlsVerifier;
	private $databaseManager;

	public function __construct()
	{
		$this->logger = new Logger();
		$this->priceHelper = new PriceHelper();
		$this->cookieManager = new CookieManager();
		$this->versionChecker = new VersionChecker($this->logger);
		$this->tlsVerifier = new TlsVerifier();
		$this->checkoutSettings = new CheckoutSettings();
		$this->databaseManager = new DatabaseManager();

		$this->paymentProcessor = new PaymentProcessor(
			$this->logger,
			$this->priceHelper,
			$this->cookieManager,
			$this->versionChecker,
			$this->tlsVerifier,
			$this->checkoutSettings,
			$this->databaseManager
		);
	}

	private function get_rest_url($route): string
	{
		return esc_url_raw(rest_url("iyzico/v1/{$route}"));
	}

	public function addRoute(): void
	{
		$webhookID = get_option('iyzicoWebhookUrlKey');

		if (!$webhookID) {
			$webhookID = substr(base64_encode(time() . mt_rand()), 15, 6);
			update_option('iyzicoWebhookUrlKey', $webhookID);
		}

		register_rest_route('iyzico/v1', "/webhook/{$webhookID}", [
			'methods' => 'POST',
			'callback' => [$this, 'processWebhook'],
			'permission_callback' => [$this, 'verifyWebhook']
		]);
	}

	public function verifyWebhook($request)
	{
		$signature = $request->get_header('X-IYZ-SIGNATURE');
		$iyziEventType = $request->get_header('iyziEventType');

		$body = $request->get_json_params();

		if ($signature && $iyziEventType && isset($body['token'])) {
			return $this->validateSignature($signature, $iyziEventType, $body['token']);
		}

		return false;
	}

	private function validateSignature($receivedSignature, $eventType, $token)
	{
		$secretKey = $this->checkoutSettings->findByKey('secret_key');

		if (!$secretKey) {
			error_log('iyzico secret key not found');
			return false;
		}

		$stringToBeHashed = $secretKey . $eventType . $token;
		$hash = base64_encode(sha1($stringToBeHashed, true));

		return hash_equals($hash, $receivedSignature);
	}

	public function processWebhook($request)
	{
		$params = wp_parse_args($request->get_json_params());
		$this->status = $params['status'];

		if ($this->status === 'SUCCESS') {
			return $this->handleSuccessfulPayment($params);
		} else {
			return $this->handleFailedPayment($params);
		}
	}

	private function handleSuccessfulPayment($data)
	{
		return $this->paymentProcessor->processWebhook($data);
	}

	private function handleFailedPayment($data)
	{
		$this->paymentProcessor->processWebhook($data);
		return new \WP_Error('payment_failed', 'Payment failed', ['status' => 400]);
	}
}