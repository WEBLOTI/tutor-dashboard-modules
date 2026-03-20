<?php
/**
 * Woo orders hybrid view.
 *
 * @var object $orders
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'woocommerce_account_orders' ) ) {
	ob_start();
	woocommerce_account_orders( 1 );
	echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
