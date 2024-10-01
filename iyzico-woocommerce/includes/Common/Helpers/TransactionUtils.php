<?php

namespace Iyzico\IyzipayWoocommerce\Common\Helpers;

class TransactionUtils {

	const WOOCOMMERCE                       = 'woocommerce';
	const WOOCOMMERCE_SUBS                  = 'woocommerce_subs';
	const WOOCOMMERCE_BLOCKS                = 'woocommerce_blocks';
	const PAYMENT                           = 'payment';
	const REFUND                            = 'refund';
	const CANCEL                            = 'cancel';
	const STARTED                           = 'started';
	const REDIRECTED                        = 'redirected';
	const COMPLETED                         = 'completed';
	const PENDING                           = 'pending_payment';
	const FAILED                            = 'failed';
	const REFUND_STATUS_CANCELLED           = 'refund_status_cancelled';
	const REFUND_STATUS_NOT_REFUNDED        = 'refund_status_not_refunded';
	const REFUND_STATUS_REFUNDED            = 'refund_status_refunded';
	const REFUND_STATUS_PARTIAL_REFUNDED    = 'refund_status_p_refunded';
}