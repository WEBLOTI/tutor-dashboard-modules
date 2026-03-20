<?php
/**
 * Dashboard module shell.
 *
 * @var \TDM\Domain\ModuleDefinition $module
 * @var \TDM\Rendering\RenderResult  $result
 * @var string                       $empty_html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="tdm-module-shell tdm-module-shell--<?php echo esc_attr( $module->wrapper_variant ); ?> tdm-status-<?php echo esc_attr( $result->status ); ?>">
	<?php if ( $module->show_title ) : ?>
		<div class="tutor-fs-5 tutor-fw-medium tutor-color-black tutor-mb-16"><?php echo esc_html( $module->title ); ?></div>
	<?php endif; ?>

	<?php foreach ( $result->notices as $notice ) : ?>
		<div class="tdm-module-notice"><?php echo esc_html( $notice ); ?></div>
	<?php endforeach; ?>

	<div class="tdm-module-shell__body">
		<?php
		if ( '' !== trim( (string) $result->html ) ) {
			echo $result->html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo $empty_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
	</div>
</div>
