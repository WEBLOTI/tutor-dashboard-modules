<?php

namespace TDM\Integration\WooCommerce;

class DownloadsProvider {
	/**
	 * @param int $user_id User ID.
	 * @return array<int,array<string,mixed>>
	 */
	public function get_downloads_for_user( $user_id ) {
		if ( ! function_exists( 'wc_get_customer_available_downloads' ) ) {
			return array();
		}

		$downloads = wc_get_customer_available_downloads( $user_id );

		return apply_filters( 'tdm/woocommerce_downloads_data', $downloads, $user_id, $this );
	}
}
