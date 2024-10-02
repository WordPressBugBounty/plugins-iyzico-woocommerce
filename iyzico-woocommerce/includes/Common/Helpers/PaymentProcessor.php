<?php

namespace Iyzico\IyzipayWoocommerce\Common\Helpers;

use Exception;
use Iyzico\IyzipayWoocommerce\Checkout\CheckoutSettings;
use Iyzico\IyzipayWoocommerce\Database\DatabaseManager;
use Iyzipay\Model\CheckoutForm as CheckoutFormModel;
use Iyzipay\Model\Mapper\CheckoutFormMapper;
use Iyzipay\Options;
use Iyzipay\Request\RetrieveCheckoutFormRequest;
use WC_Data_Exception;
use WC_Order;
use WC_Order_Item_Fee;

class PaymentProcessor
{

	protected $logger;
	protected $priceHelper;
	protected $cookieManager;
	protected $versionChecker;
	protected $tlsVerifier;
	protected $checkoutSettings;
	protected $databaseManager;

	public function __construct(
		Logger $logger,
		PriceHelper $priceHelper,
		CookieManager $cookieManager,
		VersionChecker $versionChecker,
		TlsVerifier $tlsVerifier,
		CheckoutSettings $checkoutSettings,
		DatabaseManager $databaseManager
	) {
		$this->logger = $logger;
		$this->priceHelper = $priceHelper;
		$this->cookieManager = $cookieManager;
		$this->versionChecker = $versionChecker;
		$this->tlsVerifier = $tlsVerifier;
		$this->checkoutSettings = $checkoutSettings;
		$this->databaseManager = $databaseManager;
	}

	public function processCallback(): void
	{
		try {
			$this->validateToken();
			$checkoutFormResult = $this->retrieveCheckoutForm();
			$order = $this->getOrder($checkoutFormResult->getBasketId());
			$this->ensurePaymentMethod($order);

			/** Use Mapper */
			$checkoutFormResult = CheckoutFormMapper::create($checkoutFormResult)
				->mapCheckoutForm($checkoutFormResult);

			$this->addOrderComment($checkoutFormResult, $order);
			$this->saveUserCard($checkoutFormResult);
			$this->checkInstallment($checkoutFormResult, $order);
			$this->saveCardType($checkoutFormResult, $order);
			$this->saveCardAssociation($checkoutFormResult, $order);
			$this->saveCardFamily($checkoutFormResult, $order);
			$this->saveLastFourDigits($checkoutFormResult, $order);
			$this->updateOrder($checkoutFormResult, $order);
			$this->saveOrder($checkoutFormResult, $order);
			$this->redirectToOrderReceived($checkoutFormResult, $order);

		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function processWebhook($response)
	{
		try {
			$checkoutFormResult = $this->retrieveCheckoutFormV2($response['token']);
			$order = $this->getOrder($checkoutFormResult->getBasketId());

			if ($order->get_status() == 'completed' || $order->get_status() == 'processing') {
				return http_response_code(200);
			}

			if ($response['iyziEventType'] == 'CREDIT_PAYMENT_PENDING' && $checkoutFormResult->getPaymentStatus() == 'PENDING_CREDIT') {
				$orderMessage = __("Currently in the process of applying for a shopping loan.", "woocommerce-iyzico");
				$order->add_order_note($orderMessage, 0, true);
				$order->update_status("on-hold");

				return http_response_code(200);
			}

			if ($response['iyziEventType'] == 'CREDIT_PAYMENT_AUTH' && $checkoutFormResult->getStatus() == 'success') {
				$orderMessage = __("The shopping loan transaction was completed successfully.", "woocommerce-iyzico");
				$order->add_order_note($orderMessage, 0, true);
				$order->update_status("processing");

				return http_response_code(200);
			}

			if ($response['iyziEventType'] == 'CREDIT_PAYMENT_INIT' && $checkoutFormResult->getStatus() == 'INIT_CREDIT') {
				$orderMessage = __("The shopping credit transaction has been initiated.", "woocommerce-iyzico");
				$order->add_order_note($orderMessage, 0, true);
				$order->update_status("on-hold");

				return http_response_code(200);
			}


		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	private function addOrderComment($checkoutFormResult, $order)
	{
		$message = "Payment ID: " . $checkoutFormResult->getPaymentId();
		$order->add_order_note($message, 0, true);

		if ($this->checkoutSettings->findByKey('api_type') === "https://sandbox-api.iyzipay.com") {
			$message = '<strong><p style="color:red">TEST ÖDEMESİ</a></strong>';
			$order->add_order_note($message, 0, true);
		}
	}

	private function checkInstallment($response, $order)
	{
		if (isset($response) && !empty($response->getInstallment()) && $response->getInstallment() > 1) {
			$orderData = $order->get_data();
			$orderTotal = $orderData['total'];

			$installmentFee = $response->getPaidPrice() - $orderTotal;
			$itemFee = new WC_Order_Item_Fee();
			$itemFee->set_name($response->getInstallment() . " " . __("Installment Commission", 'woocommerce-iyzico'));
			$itemFee->set_amount($installmentFee);
			$itemFee->set_tax_class('');
			$itemFee->set_tax_status('none');
			$itemFee->set_total($installmentFee);

			$order->add_item($itemFee);
			$order->calculate_totals(true);

			$order->update_meta_data('iyzico_no_of_installment', $response->installment);
			$order->update_meta_data('iyzico_installment_fee', $installmentFee);
		}
	}

	private function saveCardFamily($response, $order)
	{
		if (isset($response) && !empty($response->getCardFamily())) {
			$order->update_meta_data('iyzico_card_family', $response->getCardFamily());
		}
	}

	private function saveCardAssociation($response, $order)
	{
		if (isset($response) && !empty($response->getCardAssociation())) {
			$order->update_meta_data('iyzico_card_association', $response->getCardAssociation());
		}
	}

	private function saveCardType($response, $order)
	{
		if (isset($response) && !empty($response->getCardType())) {
			$order->update_meta_data('iyzico_card_type', $response->getCardType());
		}
	}

	private function saveLastFourDigits($response, $order)
	{
		if (isset($response) && !empty($response->getBinNumber())) {
			$order->update_meta_data('iyzico_last_four_digits', $response->getLastFourDigits());
		}
	}

	/**
	 * @throws Exception
	 */
	private function validateToken(): void
	{
		if (empty($_POST['token'])) {
			throw new Exception(__("Payment token is missing. Please try again or contact the store owner if the problem persists.", "woocommerce-iyzico"));
		}
	}

	/**
	 * @throws Exception
	 */
	private function retrieveCheckoutForm()
	{
		$request = new RetrieveCheckoutFormRequest();
		$locale = $this->checkoutSettings->findByKey('form_language') ?? "tr";
		$request->setLocale($locale);
		$request->setToken($_POST['token']);

		$checkoutFormResult = CheckoutFormModel::retrieve($request, $this->createOptions());

		if (!$checkoutFormResult || $checkoutFormResult->getStatus() !== 'success') {
			throw new Exception(__("Payment process failed. Please try again or choose a different payment method.", "woocommerce-iyzico"));
		}

		return $checkoutFormResult;
	}

	/**
	 * @throws Exception
	 */
	private function retrieveCheckoutFormV2(string $token)
	{
		$request = new RetrieveCheckoutFormRequest();
		$locale = $this->checkoutSettings->findByKey('form_language') ?? "tr";
		$request->setLocale($locale);
		$request->setToken($token);

		$checkoutFormResult = CheckoutFormModel::retrieve($request, $this->createOptions());

		if (!$checkoutFormResult || $checkoutFormResult->getStatus() !== 'success') {
			throw new Exception(__("Payment process failed. Please try again or choose a different payment method.", "woocommerce-iyzico"));
		}

		return $checkoutFormResult;
	}

	/**
	 * @throws Exception
	 */
	private function getOrder($basketId): WC_Order
	{
		$order = wc_get_order($basketId);

		if (!$order) {
			throw new Exception(__("Order not found.", "woocommerce-iyzico"));
		}

		return $order;
	}

	/**
	 * @throws WC_Data_Exception
	 */
	private function ensurePaymentMethod(WC_Order $order): void
	{
		if ($order->get_payment_method_title() !== 'iyzico') {
			$order->set_payment_method('iyzico');
		}
	}

	private function updateOrder($checkoutFormResult, WC_Order $order): void
	{
		if ($checkoutFormResult->getPaymentStatus() === 'SUCCESS' && $checkoutFormResult->getStatus() === 'success') {
			$order->payment_complete();
			$order->save();

			$orderStatus = $this->checkoutSettings->findByKey('order_status');

			if ($orderStatus !== 'default' && !empty($orderStatus)) {
				$order->update_status($orderStatus);
			}
		}

		if ($checkoutFormResult->getPaymentStatus() === "INIT_BANK_TRANSFER" && $checkoutFormResult->getStatus() === "success") {
			$order->update_status("on-hold");
			$orderMessage = __('iyzico Bank transfer/EFT payment is pending.', 'woocommerce-iyzico');
			$order->add_order_note($orderMessage, 0, true);
		}

		if ($checkoutFormResult->getPaymentStatus() === "PENDING_CREDIT" && $checkoutFormResult->getStatus() === "success") {
			$order->update_status("on-hold");
			$orderMessage = __('The shopping credit transaction has been initiated.', 'woocommerce-iyzico');
			$order->add_order_note($orderMessage, 0, true);
		}
	}

	private function saveUserCard($checkoutFormResult)
	{
		if (isset($checkoutFormResult->cardUserKey)) {
			$customer = wp_get_current_user();

			if ($customer->ID) {
				$cardUserKey = $this->databaseManager->findUserCardKey($customer->ID, $this->checkoutSettings->findByKey('api_key'));

				if ($checkoutFormResult->cardUserKey != $cardUserKey) {
					$this->databaseManager->saveUserCardKey($customer->ID, $checkoutFormResult->cardUserKey, $this->checkoutSettings->findByKey('api_key'));
				}

			}
		}
	}

	private function saveOrder($checkoutFormResult, WC_Order $order)
	{
		if ($checkoutFormResult->getStatus() === "success") {

			$orderId = $order->get_id();
			$checkoutFormResult->getPaymentId();
			$totalAmount = $checkoutFormResult->getPaidPrice();
			$status = $checkoutFormResult->getStatus();

			$this->databaseManager->createOrder(
				$checkoutFormResult->getPaymentId(),
				$orderId,
				$totalAmount,
				$status
			);

		}

	}

	/**
	 * @throws Exception
	 */
	private function redirectToOrderReceived($checkoutFormResult, WC_Order $order): void
	{
		if ($checkoutFormResult->getStatus() === "success" && $checkoutFormResult->getPaymentStatus() !== "FAILURE") {
			$checkoutOrderUrl = $order->get_checkout_order_received_url();
			$redirectUrl = add_query_arg([
				'msg' => 'Thank You',
				'type' => 'woocommerce-message'
			], $checkoutOrderUrl);

			wp_redirect($redirectUrl);
			exit;
		}

		throw new Exception(__("Error", "woocommerce-iyzico"));
	}

	private function handleException(Exception $e): void
	{
		$this->logger->error('PaymentProcessor.php: ' . $e->getMessage());
		WC()->session->set('iyzico_error', $e->getMessage());
		wp_redirect(wc_get_checkout_url() . '?payment=failed');
		exit;
	}

	protected function createOptions(): Options
	{
		$options = new Options();
		$options->setApiKey($this->checkoutSettings->findByKey('api_key'));
		$options->setSecretKey($this->checkoutSettings->findByKey('secret_key'));
		$options->setBaseUrl($this->checkoutSettings->findByKey('api_type'));

		return $options;
	}
}