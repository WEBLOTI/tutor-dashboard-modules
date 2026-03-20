<?php

namespace TDM\Rendering\Renderers;

use TDM\Domain\ModuleDefinition;
use TDM\Rendering\RenderContext;
use TDM\Rendering\RendererInterface;
use TDM\Rendering\RenderResult;
use TDM\Support\ViewLoader;

class PhpViewRenderer implements RendererInterface {
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
		$view_key = (string) $module->config( 'view_key', '' );
		$result   = $this->view_loader->render_php_view(
			$view_key,
			array(
				'module'  => $module,
				'context' => $context,
			)
		);

		if ( is_wp_error( $result ) ) {
			return RenderResult::fallback( $this->resolve_fallback_message( $module, __( 'Esta vista interna no esta disponible en este momento.', 'tutor-dashboard-modules' ) ) );
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
