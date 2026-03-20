<?php
/**
 * Woo downloads native Tutor view.
 *
 * @var array<int,array<string,mixed>> $downloads
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
					<th><?php esc_html_e( 'Download', 'tutor-dashboard-modules' ); ?></th>
					<th><?php esc_html_e( 'Product', 'tutor-dashboard-modules' ); ?></th>
					<th><?php esc_html_e( 'Remaining', 'tutor-dashboard-modules' ); ?></th>
					<th><?php esc_html_e( 'Action', 'tutor-dashboard-modules' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $downloads as $download ) : ?>
					<tr>
						<td><?php echo esc_html( (string) ( $download['download_name'] ?? '' ) ); ?></td>
						<td><?php echo esc_html( (string) ( $download['product_name'] ?? '' ) ); ?></td>
						<td><?php echo '' === (string) ( $download['downloads_remaining'] ?? '' ) ? esc_html__( 'Unlimited', 'tutor-dashboard-modules' ) : esc_html( (string) $download['downloads_remaining'] ); ?></td>
						<td><a class="tutor-btn tutor-btn-outline-primary tutor-btn-sm" href="<?php echo esc_url( (string) ( $download['download_url'] ?? '#' ) ); ?>"><?php esc_html_e( 'Download', 'tutor-dashboard-modules' ); ?></a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
