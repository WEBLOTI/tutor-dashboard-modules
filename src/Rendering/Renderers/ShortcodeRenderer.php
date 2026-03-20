<?php

namespace TDM\Rendering\Renderers;

use TDM\Domain\ModuleDefinition;
use TDM\Rendering\RenderContext;
use TDM\Rendering\RendererInterface;
use TDM\Rendering\RenderResult;

class ShortcodeRenderer implements RendererInterface {
	/**
	 * @param ModuleDefinition $module Module definition.
	 * @param RenderContext    $context Render context.
	 * @return RenderResult
	 */
	public function render( ModuleDefinition $module, RenderContext $context ) {
		$shortcode = trim( (string) $module->config( 'shortcode_text', '' ) );
		if ( '' === $shortcode ) {
			return RenderResult::fallback( $this->resolve_fallback_message( $module, __( 'Este modulo no pudo mostrar contenido en este momento.', 'tutor-dashboard-modules' ) ) );
		}

		$html = do_shortcode( $shortcode );
		if ( '' === trim( (string) $html ) ) {
			return RenderResult::empty_state( __( 'No hay datos disponibles en esta seccion', 'tutor-dashboard-modules' ) );
		}

		return RenderResult::success( $html );
	}

	/**
	 * @param ModuleDefinition $module Module definition.
	 * @param string           $default Default message.
	 * @return string
	 */
	private function resolve_fallback_message( ModuleDefinition $module, $default ) {
		if ( 'custom' === $module->fallback_mode && '' !== trim( $module->fallback_message ) ) {
			return $module->fallback_message;
		}

		return $default;
	}
}
