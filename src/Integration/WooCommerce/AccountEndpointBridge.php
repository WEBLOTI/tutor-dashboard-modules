<?php

namespace TDM\Integration\WooCommerce;

class AccountEndpointBridge {
	/**
	 * @param string $endpoint Endpoint key.
	 * @return string
	 */
	public function render_endpoint( $endpoint ) {
		if ( ! function_exists( 'wc_get_template' ) ) {
			return '';
		}

		ob_start();

		switch ( $endpoint ) {
			case 'downloads':
				if ( function_exists( 'woocommerce_account_downloads' ) ) {
					woocommerce_account_downloads();
				}
				break;
			case 'orders':
				if ( function_exists( 'woocommerce_account_orders' ) ) {
					woocommerce_account_orders( 1 );
				}
				break;
			case 'edit-account':
				if ( function_exists( 'woocommerce_account_edit_account' ) ) {
					woocommerce_account_edit_account();
				}
				break;
			case 'edit-address':
				wc_get_template( 'myaccount/my-address.php' );
				break;
			case 'payment-methods':
				if ( function_exists( 'woocommerce_account_payment_methods' ) ) {
					woocommerce_account_payment_methods();
				}
				break;
			case 'add-payment-method':
				if ( function_exists( 'woocommerce_account_add_payment_method' ) ) {
					woocommerce_account_add_payment_method();
				}
				break;
		}

		return (string) ob_get_clean();
	}
}
