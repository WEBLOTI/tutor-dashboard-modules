<?php

namespace TDM\Rendering\Renderers;

use TDM\Domain\ModuleDefinition;
use TDM\Rendering\RenderContext;
use TDM\Rendering\RendererInterface;
use TDM\Rendering\RenderResult;
use TDM\Support\ViewLoader;

class CallbackRenderer implements RendererInterface {
	/**
	 * @var ViewLoader
	 */
	private $view_loader;

	/**
	 * @param ViewLoader $view_loader View loader.
	 */
	public function __construct( ViewLoader $view_loader ) {
		$this->view_loader = $view_loader;
	}

	/**
	 * @param ModuleDefinition $module Module definition.
	 * @param RenderContext    $context Render context.
	 * @return RenderResult
	 */
	public function render( ModuleDefinition $module, RenderContext $context ) {
		$callbacks = $this->view_loader->get_registered_callbacks();
		$key       = (string) $module->config( 'callback_key', '' );

		if ( empty( $callbacks[ $key ]['callback'] ) || ! is_callable( $callbacks[ $key ]['callback'] ) ) {
			return RenderResult::fallback( $this->resolve_fallback_message( $module, __( 'Este modulo no esta disponible en este momento.', 'tutor-dashboard-modules' ) ) );
		}

		$result = call_user_func( $callbacks[ $key ]['callback'], $module, $context );

		if ( $result instanceof RenderResult ) {
			return $result;
		}

		if ( '' === trim( (string) $result ) ) {
			return RenderResult::empty_state( __( 'No hay datos disponibles en esta seccion', 'tutor-dashboard-modules' ) );
		}

		return RenderResult::success( (string) $result );
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
