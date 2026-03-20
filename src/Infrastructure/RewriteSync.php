<?php

namespace TDM\Infrastructure;

class RewriteSync {
	const OPTION_PENDING = 'tdm_flush_rewrite_rules_pending';

	/**
	 * @return void
	 */
	public function mark_pending() {
		update_option( self::OPTION_PENDING, 1, false );
	}

	/**
	 * @return bool
	 */
	public function is_pending() {
		return (bool) get_option( self::OPTION_PENDING, false );
	}

	/**
	 * @return void
	 */
	public function maybe_flush() {
		if ( ! $this->is_pending() ) {
			return;
		}

		delete_option( self::OPTION_PENDING );
		flush_rewrite_rules( false );
	}
}
