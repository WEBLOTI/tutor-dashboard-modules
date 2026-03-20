<?php

namespace TDM\Integration\WooCommerce;

class OrdersProvider {
	/**
	 * @param int $user_id User ID.
	 * @param int $page Current page.
	 * @return \WC_Order_Query|\stdClass|object|null
	 */
	public function get_orders_for_user( $user_id, $page = 1 ) {
		if ( ! function_exists( 'wc_get_orders' ) ) {
			return null;
		}

		return wc_get_orders(
			apply_filters(
				'tdm/woocommerce_orders_query_args',
				array(
					'customer' => $user_id,
					'page'     => max( 1, absint( $page ) ),
					'paginate' => true,
				),
				$user_id,
				$page
			)
		);
	}
}
