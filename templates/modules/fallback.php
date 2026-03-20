<?php
/**
 * Generic fallback template.
 *
 * @var string $message
 * @var string $status
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$default_message = __( 'This module is not available right now.', 'tutor-dashboard-modules' );
$message         = $message ? $message : $default_message;
$message         = apply_filters( 'tdm/module_fallback_message', $message, $status );
?>
<div class="tdm-fallback tdm-fallback--<?php echo esc_attr( $status ); ?>">
	<p><?php echo esc_html( $message ); ?></p>
</div>
