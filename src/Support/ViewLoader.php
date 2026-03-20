<?php

namespace TDM\Support;

use TDM\Plugin;
use WP_Error;

class ViewLoader {
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
	 * @return array<string,array<string,mixed>>
	 */
	public function get_registered_php_views() {
		return apply_filters( 'tdm/register_php_views', array() );
	}

	/**
	 * @return array<string,array<string,mixed>>
	 */
	public function get_registered_callbacks() {
		return apply_filters( 'tdm/register_callbacks', array() );
	}

	/**
	 * @return array<string,array<string,mixed>>
	 */
	public function get_registered_dynamic_providers() {
		return apply_filters( 'tdm/register_dynamic_providers', array() );
	}

	/**
	 * @param string               $view_key View key.
	 * @param array<string, mixed> $args View args.
	 * @return string|WP_Error
	 */
	public function render_php_view( $view_key, $args = array() ) {
		$views = $this->get_registered_php_views();
		if ( ! isset( $views[ $view_key ]['template'] ) ) {
			return new WP_Error( 'tdm_unknown_view', __( 'The selected PHP view is not registered.', 'tutor-dashboard-modules' ) );
		}

		$template = ltrim( (string) $views[ $view_key ]['template'], '/' );
		$html     = $this->plugin->capture_template( $template, $args );

		if ( '' === trim( $html ) ) {
			return new WP_Error( 'tdm_empty_view', __( 'The selected PHP view returned empty output.', 'tutor-dashboard-modules' ) );
		}

		return $html;
	}
}
