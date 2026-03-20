<?php

namespace TDM\Rendering\Renderers;

use TDM\Domain\ModuleDefinition;
use TDM\Integration\Elementor\ElementorBridge;
use TDM\Rendering\RenderContext;
use TDM\Rendering\RendererInterface;
use TDM\Rendering\RenderResult;

class ElementorTemplateRenderer implements RendererInterface {
	/**
	 * @var ElementorBridge
	 */
	private $bridge;

	/**
	 * @param ElementorBridge $bridge Elementor bridge.
	 */
	public function __construct( ElementorBridge $bridge ) {
		$this->bridge = $bridge;
	}

	/**
	 * @param ModuleDefinition $module Module definition.
	 * @param RenderContext    $context Render context.
	 * @return RenderResult
	 */
	public function render( ModuleDefinition $module, RenderContext $context ) {
		$template_id = absint( $module->config( 'template_id', 0 ) );
		$result      = $this->bridge->render_template( $template_id );

		if ( is_wp_error( $result ) ) {
			return RenderResult::fallback( $this->resolve_fallback_message( $module, __( 'Este modulo requiere Elementor para mostrarse.', 'tutor-dashboard-modules' ) ) );
		}

		return RenderResult::success( $result );
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
