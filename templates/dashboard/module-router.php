<?php
/**
 * Tutor dashboard module router template.
 *
 * @var string $template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo tdm()->render_current_module(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
