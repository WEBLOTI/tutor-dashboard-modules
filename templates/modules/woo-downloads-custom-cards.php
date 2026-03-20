<?php
/**
 * Woo downloads custom cards.
 *
 * @var array<int,array<string,mixed>> $downloads
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="tutor-grid tutor-grid-2">
	<?php foreach ( $downloads as $download ) : ?>
		<div class="tutor-card">
			<div class="tutor-card-body">
				<div class="tutor-fs-6 tutor-fw-bold tutor-mb-8"><?php echo esc_html( (string) ( $download['download_name'] ?? '' ) ); ?></div>
				<div class="tutor-color-secondary tutor-fs-7 tutor-mb-12"><?php echo esc_html( (string) ( $download['product_name'] ?? '' ) ); ?></div>
				<a class="tutor-btn tutor-btn-primary tutor-btn-sm" href="<?php echo esc_url( (string) ( $download['download_url'] ?? '#' ) ); ?>"><?php esc_html_e( 'Download', 'tutor-dashboard-modules' ); ?></a>
			</div>
		</div>
	<?php endforeach; ?>
</div>
