<?php

namespace TDM\Support;

class Logger {
	const OPTION_RECENT_LOGS = 'tdm_recent_logs';
	const OPTION_DEBUG       = 'tdm_debug';

	/**
	 * @param string               $level Log level.
	 * @param string               $message Message text.
	 * @param array<string, mixed> $context Context payload.
	 * @return void
	 */
	public function log( $level, $message, $context = array() ) {
		$entry = array(
			'timestamp' => current_time( 'mysql' ),
			'level'     => sanitize_key( $level ),
			'message'   => wp_strip_all_tags( $message ),
			'context'   => $context,
		);

		$logs   = get_option( self::OPTION_RECENT_LOGS, array() );
		$logs[] = $entry;

		if ( count( $logs ) > 20 ) {
			$logs = array_slice( $logs, -20 );
		}

		update_option( self::OPTION_RECENT_LOGS, $logs, false );

		if ( class_exists( 'WC_Logger' ) && function_exists( 'wc_get_logger' ) ) {
			wc_get_logger()->log( $entry['level'], $entry['message'], array( 'source' => 'tdm' ) );
		} elseif ( $this->is_debug_enabled() ) {
			error_log( 'TDM [' . $entry['level'] . '] ' . $entry['message'] ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}

	/**
	 * @return bool
	 */
	public function is_debug_enabled() {
		return (bool) get_option( self::OPTION_DEBUG, false );
	}

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public function get_recent_logs() {
		return get_option( self::OPTION_RECENT_LOGS, array() );
	}
}
