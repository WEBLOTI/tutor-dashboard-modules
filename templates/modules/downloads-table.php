<?php
/**
 * WooCommerce downloads table.
 *
 * @var array<int,array<string,mixed>> $downloads
 * @var bool                           $show_product_link
 * @var bool                           $show_expiry
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="tdm-downloads">
	<div class="tutor-table-responsive">
		<table class="tutor-table tutor-table-middle">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Download', 'tutor-dashboard-modules' ); ?></th>
					<?php if ( $show_product_link ) : ?>
						<th><?php esc_html_e( 'Product', 'tutor-dashboard-modules' ); ?></th>
					<?php endif; ?>
					<th><?php esc_html_e( 'Remaining', 'tutor-dashboard-modules' ); ?></th>
					<?php if ( $show_expiry ) : ?>
						<th><?php esc_html_e( 'Expires', 'tutor-dashboard-modules' ); ?></th>
					<?php endif; ?>
					<th><?php esc_html_e( 'Action', 'tutor-dashboard-modules' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $downloads as $download ) : ?>
					<tr>
						<td>
							<strong><?php echo esc_html( isset( $download['download_name'] ) ? (string) $download['download_name'] : '' ); ?></strong>
						</td>
						<?php if ( $show_product_link ) : ?>
							<td>
								<?php if ( ! empty( $download['product_url'] ) ) : ?>
									<a href="<?php echo esc_url( $download['product_url'] ); ?>"><?php echo esc_html( isset( $download['product_name'] ) ? (string) $download['product_name'] : '' ); ?></a>
								<?php else : ?>
									<?php echo esc_html( isset( $download['product_name'] ) ? (string) $download['product_name'] : '' ); ?>
								<?php endif; ?>
							</td>
						<?php endif; ?>
						<td>
							<?php
							$remaining = isset( $download['downloads_remaining'] ) ? $download['downloads_remaining'] : '';
							echo '' === $remaining ? esc_html__( 'Unlimited', 'tutor-dashboard-modules' ) : esc_html( (string) $remaining );
							?>
						</td>
						<?php if ( $show_expiry ) : ?>
							<td>
								<?php
								$expiry = isset( $download['access_expires'] ) ? $download['access_expires'] : '';
								if ( empty( $expiry ) ) {
									esc_html_e( 'Never', 'tutor-dashboard-modules' );
								} else {
									echo esc_html( wp_date( get_option( 'date_format' ), strtotime( (string) $expiry ) ) );
								}
								?>
							</td>
						<?php endif; ?>
						<td>
							<a class="tutor-btn tutor-btn-outline-primary tutor-btn-sm" href="<?php echo esc_url( isset( $download['download_url'] ) ? (string) $download['download_url'] : '#' ); ?>">
								<?php esc_html_e( 'Download', 'tutor-dashboard-modules' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
