<?php

namespace Iyzico\IyzipayWoocommerce\Common\Helpers;

use Iyzico\IyzipayWoocommerce\Checkout\CheckoutSettings;
use Iyzico\IyzipayWoocommerce\Database\DatabaseManager;

class WebhookHelper {
	private $checkoutSettings;
	private $status;
	private $paymentProcessor;
	private $logger;
	private $priceHelper;
	private $cookieManager;
	private $versionChecker;
	private $tlsVerifier;
	private $databaseManager;
	private $paymentConversationId;
	private $token;
	private $iyziEventType;


	public function __construct() {
		$this->logger           = new Logger();
		$this->priceHelper      = new PriceHelper();
		$this->cookieManager    = new CookieManager();
		$this->versionChecker   = new VersionChecker( $this->logger );
		$this->tlsVerifier      = new TlsVerifier();
		$this->checkoutSettings = new CheckoutSettings();
		$this->databaseManager  = new DatabaseManager();

		$this->paymentProcessor = new PaymentProcessor(
			$this->logger,
			$this->priceHelper,
			$this->cookieManager,
			$this->versionChecker,
			$this->tlsVerifier,
			$this->checkoutSettings,
			$this->databaseManager
		);

		$this->namespace     = 'iyzico/v1';
		$this->resource_name = 'webhook/' . self::getIyziUrlId();
	}

	public static function getIyziUrlId() {

		if ( ! get_option( WEBHOOK_URL_KEY ) ) {
			add_action( 'admin_notices', [ self::class, 'webhookAdminNoticeWarning' ] );

			return;
		} else {
			return get_option( WEBHOOK_URL_KEY );
		}
	}

	private function get_rest_url( $route ): string {
		return esc_url_raw( rest_url( "iyzico/v1/{$route}" ) );
	}

	public function addRoute(): void {
		$webhookID = get_option( 'iyzicoWebhookUrlKey' );

		if ( ! $webhookID ) {
			$webhookID = substr( base64_encode( time() . mt_rand() ), 15, 6 );
			update_option( 'iyzicoWebhookUrlKey', $webhookID );
		}

		register_rest_route( 'iyzico/v1', "/webhook/{$webhookID}", [
			'methods'             => 'POST',
			'callback'            => [ $this, 'processWebhook' ],
			'permission_callback' => '__return_true',
		] );
	}

	public function processWebhook( $request ) {
		$headers         = getallheaders();
		$iyzicoSignature = $headers['X-IYZ-SIGNATURE'];

		if ( is_null( $iyzicoSignature ) ) {
			$iyzicoSignature = $headers['X-Iyz-Signature'];
		}

		if ( is_null( $iyzicoSignature ) ) {
			$iyzicoSignature = $headers['x-iyz-signature'];
		}

		if ( is_null( $iyzicoSignature ) ) {
			$iyzicoSignature = $headers['x_iyz_signature'];
		}

		$params = wp_parse_args( $request->get_json_params() );

		if ( isset( $params['iyziEventType'] ) && isset( $params['token'] ) && isset( $params['paymentConversationId'] ) ) {
			$this->paymentConversationId = $params['paymentConversationId'];
			$this->token                 = $params['token'];
			$this->iyziEventType         = $params['iyziEventType'];

			if ( $iyzicoSignature ) {
				$createIyzicoSignature = base64_encode( sha1( $this->checkoutSettings->findByKey( 'secret_key' ) . $this->iyziEventType . $this->token, true ) );
				if ( $iyzicoSignature == $createIyzicoSignature ) {
					$params = [
						'iyziEventType'         => $this->iyziEventType,
						'token'                 => $this->token,
						'paymentConversationId' => $this->paymentConversationId,
					];

					return $this->handleSuccessfulPayment( $params );
				} else {
					return new \WP_Error( 'signature_not_valid', 'X-IYZ-SIGNATURE geçersiz', array( 'status' => 404 ) );
				}
			} else {
				return new \WP_Error( 'signature_not_found', 'X-IYZ-SIGNATURE bulunamadı', array( 'status' => 404 ) );
			}
		} else {
			return new \WP_Error( 'invalid_parameters', 'Gönderilen parametreler geçersiz', array( 'status' => 404 ) );
		}
	}

	private function handleSuccessfulPayment( $data ) {
		return $this->paymentProcessor->processWebhook( $data );
	}
}