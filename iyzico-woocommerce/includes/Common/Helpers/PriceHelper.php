<?php

namespace Iyzico\IyzipayWoocommerce\Common\Helpers;

class PriceHelper
{

	public function subTotalPriceCalc($items, $data)
	{

		$price = 0;

		$itemSize = count($items);
		if (!$itemSize) {
			$price = $data->get_total();
			return $this->priceParser($price);
		}

		foreach ($items as $item) {
			if ($item['variation_id']) {
				$productId = $item['variation_id'];
			} else {
				$productId = $item['product_id'];
			}

			$product = wc_get_product($productId);
			$realPrice = $this->realPrice($product->get_sale_price(), $product->get_price()) * $item['quantity'];
			$price += round($realPrice, 2);
		}

		$shipping = $data->get_total_shipping() + $data->get_shipping_tax();
		if ($shipping) {
			$price += $shipping;
		}

		return $this->priceParser($price);
	}

	public function priceParser($price)
	{

		if (strpos($price, ".") === false) {
			return $price . ".0";
		}

		$subStrIndex = 0;
		$priceReversed = strrev($price);

		for ($i = 0; $i < strlen($priceReversed); $i++) {
			if (strcmp($priceReversed[$i], "0") == 0) {
				$subStrIndex = $i + 1;
			} else if (strcmp($priceReversed[$i], ".") == 0) {
				$priceReversed = "0" . $priceReversed;
				break;
			} else {
				break;
			}
		}

		return strrev(substr($priceReversed, $subStrIndex));
	}

	protected function realPrice($salePrice, $regularPrice)
	{
		if (empty($salePrice)) {
			$salePrice = $regularPrice;
		}
		return $salePrice;
	}
}