<?php

namespace TDM\Integration\Elementor;

use WP_Error;

class ElementorBridge {
	/**
	 * @param int $template_id Elementor template ID.
	 * @return string|WP_Error
	 */
	public function render_template( $template_id ) {
		$template_id = absint( $template_id );
		if ( ! $template_id ) {
			return new WP_Error( 'tdm_missing_template', __( 'No Elementor template was selected.', 'tutor-dashboard-modules' ) );
		}

		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return new WP_Error( 'tdm_missing_elementor', __( 'Elementor is not active.', 'tutor-dashboard-modules' ) );
		}

		$html = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id, true );
		$html = apply_filters( 'tdm/elementor_template_output', $html, $template_id, $this );

		if ( '' === trim( (string) $html ) ) {
			return new WP_Error( 'tdm_empty_template', __( 'The selected Elementor template returned empty output.', 'tutor-dashboard-modules' ) );
		}

		return $html;
	}
}
