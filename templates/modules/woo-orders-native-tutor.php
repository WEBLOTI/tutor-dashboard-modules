<?php
/**
 * Woo orders native Tutor view.
 *
 * @var object $orders
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="tutor-dashboard-content-inner">
	<div class="tutor-table-responsive">
		<table class="tutor-table tutor-table-middle">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Order', 'tutor-dashboard-modules' ); ?></th>
					<th><?php esc_html_e( 'Date', 'tutor-dashboard-modules' ); ?></th>
					<th><?php esc_html_e( 'Status', 'tutor-dashboard-modules' ); ?></th>
					<th><?php esc_html_e( 'Total', 'tutor-dashboard-modules' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $orders->orders as $order ) : ?>
					<tr>
						<td>#<?php echo esc_html( (string) $order->get_order_number() ); ?></td>
						<td><?php echo esc_html( wp_date( get_option( 'date_format' ), strtotime( (string) $order->get_date_created() ) ) ); ?></td>
						<td><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></td>
						<td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
