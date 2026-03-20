<?php

namespace TDM\Rendering\Renderers;

use TDM\Domain\ModuleDefinition;
use TDM\Rendering\RenderContext;
use TDM\Rendering\RendererInterface;
use TDM\Rendering\RenderResult;
use TDM\Support\ViewLoader;

class DynamicDataViewRenderer implements RendererInterface {
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
		$providers = $this->view_loader->get_registered_dynamic_providers();
		$key       = (string) $module->config( 'provider_key', '' );

		if ( empty( $providers[ $key ]['callback'] ) || ! is_callable( $providers[ $key ]['callback'] ) ) {
			return RenderResult::fallback( $this->resolve_fallback_message( $module, __( 'Este modulo no esta disponible en este momento.', 'tutor-dashboard-modules' ) ) );
		}

		$data = call_user_func( $providers[ $key ]['callback'], $module, $context );
		if ( isset( $providers[ $key ]['view'] ) ) {
			$view = $this->view_loader->render_php_view(
				(string) $providers[ $key ]['view'],
				array(
					'module'  => $module,
					'context' => $context,
					'data'    => $data,
				)
			);
			if ( ! is_wp_error( $view ) ) {
				return RenderResult::success( $view );
			}
		}

		if ( is_array( $data ) ) {
			$data = wp_json_encode( $data, JSON_PRETTY_PRINT );
		}

		if ( '' === trim( (string) $data ) ) {
			return RenderResult::empty_state( __( 'No hay datos disponibles en esta seccion', 'tutor-dashboard-modules' ) );
		}

		return RenderResult::success( '<pre class="tdm-dynamic-pre">' . esc_html( (string) $data ) . '</pre>' );
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
