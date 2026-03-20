<?php

namespace TDM\Support;

use TDM\Plugin;

class TutorUiBridge {
	/**
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * @param Plugin $plugin Plugin instance.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * @param string $message Empty state message.
	 * @param string $status Result status.
	 * @return string
	 */
	public function render_empty_state( $message, $status = 'fallback' ) {
		$message = trim( (string) $message );
		$message = '' !== $message ? $message : __( 'This module is not available right now.', 'tutor-dashboard-modules' );
		$message = apply_filters( 'tdm/module_fallback_message', $message, $status );

		if ( function_exists( 'tutor_utils' ) && method_exists( tutor_utils(), 'tutor_empty_state' ) ) {
			ob_start();
			tutor_utils()->tutor_empty_state( $message );

			return (string) ob_get_clean();
		}

		return $this->plugin->capture_template(
			'modules/fallback.php',
			array(
				'message' => $message,
				'status'  => $status,
			)
		);
	}
}
