<?php
/**
 * Subscriptions placeholder PHP view.
 *
 * @var \TDM\Domain\ModuleDefinition $module
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="tdm-placeholder-card">
	<p class="tdm-placeholder-kicker"><?php esc_html_e( 'Internal PHP View', 'tutor-dashboard-modules' ); ?></p>
	<h3><?php esc_html_e( 'Subscriptions Module Placeholder', 'tutor-dashboard-modules' ); ?></h3>
	<p><?php esc_html_e( 'Use this internal view while you define the final subscription experience. You can replace it later with an Elementor template, shortcode, or registered callback without changing the endpoint architecture.', 'tutor-dashboard-modules' ); ?></p>
	<ul>
		<li><?php esc_html_e( 'Current module:', 'tutor-dashboard-modules' ); ?> <?php echo esc_html( $module->title ); ?></li>
		<li><?php esc_html_e( 'Endpoint:', 'tutor-dashboard-modules' ); ?> <?php echo esc_html( $module->slug ); ?></li>
		<li><?php esc_html_e( 'Viewer:', 'tutor-dashboard-modules' ); ?> <?php echo esc_html( wp_get_current_user()->display_name ); ?></li>
	</ul>
</div>
