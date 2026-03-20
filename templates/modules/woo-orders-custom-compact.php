<?php
/**
 * Woo orders custom compact view.
 *
 * @var object $orders
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="tutor-grid tutor-grid-2">
	<?php foreach ( $orders->orders as $order ) : ?>
		<div class="tutor-card">
			<div class="tutor-card-body">
				<div class="tutor-fs-6 tutor-fw-bold">#<?php echo esc_html( (string) $order->get_order_number() ); ?></div>
				<div class="tutor-color-secondary tutor-fs-7 tutor-mt-8"><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></div>
				<div class="tutor-mt-8"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
