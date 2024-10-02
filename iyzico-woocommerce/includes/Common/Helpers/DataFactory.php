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
		$buyer->setId( $customer->ID );
		$buyer->setName( $order->get_billing_first_name() );
		$buyer->setSurname( $order->get_billing_last_name() );
		$buyer->setIdentityNumber( "11111111111" );
		$buyer->setEmail( $order->get_billing_email() );
		$buyer->setRegistrationDate( date( 'Y-m-d H:i:s' ) );
		$buyer->setLastLoginDate( date( 'Y-m-d H:i:s' ) );
		$buyer->setRegistrationAddress( $order->get_billing_address_1() . ' ' . $order->get_billing_address_2() );
		$buyer->setCity( $order->get_billing_city() );
		$buyer->setCountry( $order->get_billing_country() );
		$buyer->setZipCode( $order->get_billing_postcode() );
		$buyer->setIp( $_SERVER['REMOTE_ADDR'] );
		$buyer->setGsmNumber( $order->get_billing_phone() );

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
		$address->setContactName( $contactName );
		$address->setCity( $city );
		$address->setCountry( $country );
		$address->setAddress( $fullAddress );
		$address->setZipCode( $zipCode );

		return $address;
	}

	protected function createBasket( array $cart ): array {
		$basketItems = [];
		foreach ( $cart as $item ) {
			$product    = $item['data'];
			$basketItem = new BasketItem();
			$basketItem->setId( (string) $item['product_id'] );
			$basketItem->setName( $product->get_name() );

			$categories = get_the_terms( $product->get_id(), 'product_cat' );
			if ( $categories && ! is_wp_error( $categories ) ) {
				$category_names = wp_list_pluck( $categories, 'name' );
				$basketItem->setCategory1( implode( ', ', $category_names ) );
			}

			$basketItem->setItemType( BasketItemType::PHYSICAL );
			$basketItem->setPrice( $item['quantity'] * $this->priceHelper->priceParser( $product->get_price() ) );
			$basketItems[] = $basketItem;
		}

		return $basketItems;
	}

	public function prepareCheckoutData( $customer, WC_Order $order, array $cart ): array {
		return [
			'buyer'           => $this->createBuyer( $customer, $order ),
			'billingAddress'  => $this->createAddress( $order, 'billing' ),
			'shippingAddress' => $this->createAddress( $order, 'shipping' ),
			'basketItems'     => $this->createBasket( $cart ),
		];
	}

}