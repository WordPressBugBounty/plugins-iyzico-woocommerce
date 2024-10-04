<?php

namespace Iyzico\IyzipayWoocommerce\Common\Helpers;

use Iyzico\IyzipayWoocommerce\Checkout\CheckoutSettings;
use Iyzipay\Model\Buyer;
use Iyzipay\Model\Address;
use Iyzipay\Model\BasketItem;
use Iyzipay\Model\BasketItemType;
use WC_Order;

class DataFactory {
	protected $priceHelper;
	protected $checkoutSettings;

	public function __construct( PriceHelper $priceHelper, CheckoutSettings $checkoutSettings ) {
		$this->priceHelper      = $priceHelper;
		$this->checkoutSettings = $checkoutSettings;
	}

	protected function createBuyer( $customer, WC_Order $order ): Buyer {
		$buyer = new Buyer();
		$buyer->setId( $this->validateStringVal( $customer->ID ) );
		$buyer->setName( $this->validateStringVal( $order->get_billing_first_name() ) );
		$buyer->setSurname( $this->validateStringVal( $order->get_billing_last_name() ) );
		$buyer->setIdentityNumber( "11111111111" );
		$buyer->setEmail( $this->validateStringVal( $order->get_billing_email() ) );
		$buyer->setRegistrationDate( date( 'Y-m-d H:i:s' ) );
		$buyer->setLastLoginDate( date( 'Y-m-d H:i:s' ) );
		$buyer->setRegistrationAddress( $this->validateStringVal( $order->get_billing_address_1() ) . ' ' . $this->validateStringVal( $order->get_billing_address_2() ) );
		$buyer->setCity( $this->validateStringVal( $order->get_billing_city() ) );
		$buyer->setCountry( $this->validateStringVal( $order->get_billing_country() ) );
		$buyer->setZipCode( $this->validateStringVal( $order->get_billing_postcode() ) );
		$buyer->setIp( $this->validateStringVal( $_SERVER['REMOTE_ADDR'] ) );
		$buyer->setGsmNumber( $this->validateStringVal( $order->get_billing_phone() ) );

		return $buyer;
	}

	protected function createAddress( WC_Order $order, string $type ): Address {
		$isTypeBilling = $type === "billing";

		$firstName   = $isTypeBilling ? $order->get_billing_first_name() : $order->get_shipping_first_name();
		$lastName    = $isTypeBilling ? $order->get_billing_last_name() : $order->get_shipping_last_name();
		$contactName = $firstName . ' ' . $lastName;

		$city        = $isTypeBilling ? $order->get_billing_city() : $order->get_shipping_city();
		$country     = $isTypeBilling ? $order->get_billing_country() : $order->get_shipping_country();
		$address1    = $isTypeBilling ? $order->get_billing_address_1() : $order->get_shipping_address_1();
		$address2    = $isTypeBilling ? $order->get_billing_address_2() : $order->get_shipping_address_2();
		$fullAddress = trim( $address1 . ' ' . $address2 );
		$zipCode     = $isTypeBilling ? $order->get_billing_postcode() : $order->get_shipping_postcode();

		$address = new Address();
		$address->setContactName( $this->validateStringVal( $contactName ) );
		$address->setCity( $this->validateStringVal( $city ) );
		$address->setCountry( $this->validateStringVal( $country ) );
		$address->setAddress( $this->validateStringVal( $fullAddress ) );
		$address->setZipCode( $this->validateStringVal( $zipCode ) );

		return $address;
	}

	protected function createBasket( WC_Order $order, array $cart ): array {
		$basketItems             = [];
		$isShippingPriceIncluded = $this->orderHasShippingPrice( $order );

		if ( $isShippingPriceIncluded ) {
			$shippingItem = new BasketItem();
			$shippingItem->setId( 'SHIPPING' );
			$shippingItem->setName( 'Shipping' );
			$shippingItem->setCategory1( 'Shipping' );
			$shippingItem->setItemType( BasketItemType::PHYSICAL );
			$shippingPrice = strval( intval( $order->get_shipping_total() ) + intval( $order->get_shipping_tax() ) );
			$shippingItem->setPrice( $shippingPrice );
			$basketItems[] = $shippingItem;
		}

		foreach ( $cart as $item ) {
			$product    = $item['data'];
			$basketItem = new BasketItem();
			$basketItem->setId( (string) $item['product_id'] );
			$basketItem->setName( $product->get_name() );

			$product_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
			$categories = get_the_terms( $product_id, 'product_cat' );

			$category1 = '';
			if ( $categories && ! is_wp_error( $categories ) ) {
				$category_names = wp_list_pluck( $categories, 'name' );
				$category1      = implode( ', ', $category_names );
			}

			$basketItem->setCategory1( $this->validateStringVal( $category1 ) );
			$basketItem->setItemType( $product->is_virtual() ? BasketItemType::VIRTUAL : BasketItemType::PHYSICAL );
			$basketItem->setPrice( $item['quantity'] * $this->priceHelper->priceParser( $product->get_price() ) );
			$basketItems[] = $basketItem;
		}

		return $basketItems;
	}

	public function prepareCheckoutData( $customer, WC_Order $order, array $cart ): array {
		$cartHasPhysicalProduct = $this->cartHasPhysicalProduct( $cart );
		$data                   = [
			'buyer'           => $this->createBuyer( $customer, $order ),
			'billingAddress'  => $this->createAddress( $order, 'billing' ),
			'shippingAddress' => $this->createAddress( $order, 'shipping' ),
			'basketItems'     => $this->createBasket( $order, $cart ),
		];

		if ( ! $cartHasPhysicalProduct ) {
			unset( $data['shippingAddress'] );
		}

		return $data;

	}

	protected function cartHasPhysicalProduct( array $cart ): bool {
		foreach ( $cart as $item ) {
			if ( ! $item['data']->is_virtual() ) {
				return true;
			}
		}

		return false;
	}

	protected function orderHasShippingPrice( WC_Order $order ): bool {
		return $order->get_shipping_total() > 0;
	}

	protected function validateStringVal( $string ): string {
		if ( empty( $string ) ) {
			return 'UNKNOWN';
		}

		if ( is_null( $string ) ) {
			return 'UNKNOWN';
		}

		if ( strlen( $string ) === 0 ) {
			return 'UNKNOWN';
		}

		return $string;
	}
}