<?php
/**
 * Woo downloads hybrid view.
 *
 * @var array<int,array<string,mixed>> $downloads
 * @var bool $show_product_link
 * @var bool $show_expiry
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo tdm()->capture_template(
	'modules/downloads-table.php',
	array(
		'downloads'         => $downloads,
		'show_product_link' => $show_product_link,
		'show_expiry'       => $show_expiry,
	)
); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
