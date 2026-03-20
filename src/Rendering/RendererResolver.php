<?php

namespace TDM\Rendering;

use TDM\Integration\Elementor\ElementorBridge;
use TDM\Integration\WooCommerce\AccountEndpointBridge;
use TDM\Integration\WooCommerce\DownloadsProvider;
use TDM\Integration\WooCommerce\OrdersProvider;
use TDM\Plugin;
use TDM\Rendering\Renderers\CallbackRenderer;
use TDM\Rendering\Renderers\DynamicDataViewRenderer;
use TDM\Rendering\Renderers\ElementorTemplateRenderer;
use TDM\Rendering\Renderers\PhpViewRenderer;
use TDM\Rendering\Renderers\ShortcodeRenderer;
use TDM\Support\Logger;
use TDM\Support\ViewLoader;
use TDM\Rendering\Renderers\WooCommerceEndpointRenderer;

class RendererResolver {
	/**
	 * @var array<string,RendererInterface>
	 */
	private $renderers = array();

	/**
	 * @param Plugin              $plugin Plugin instance.
	 * @param ViewLoader          $view_loader View registry service.
	 * @param DownloadsProvider   $downloads_provider Downloads provider.
	 * @param OrdersProvider      $orders_provider Orders provider.
	 * @param AccountEndpointBridge $account_endpoint_bridge Account endpoint bridge.
	 * @param ElementorBridge     $elementor_bridge Elementor service.
	 * @param Logger              $logger Logger service.
	 */
	public function __construct( Plugin $plugin, ViewLoader $view_loader, DownloadsProvider $downloads_provider, OrdersProvider $orders_provider, AccountEndpointBridge $account_endpoint_bridge, ElementorBridge $elementor_bridge, Logger $logger ) {
		$this->renderers = array(
			'shortcode'           => new ShortcodeRenderer(),
			'php_view'            => new PhpViewRenderer( $view_loader ),
			'woocommerce_endpoint'=> new WooCommerceEndpointRenderer( $plugin, $downloads_provider, $orders_provider, $account_endpoint_bridge ),
			'elementor_template'  => new ElementorTemplateRenderer( $elementor_bridge ),
			'custom_callback'     => new CallbackRenderer( $view_loader ),
			'dynamic_data_view'   => new DynamicDataViewRenderer( $view_loader ),
		);

		$this->renderers = apply_filters( 'tdm/register_renderers', $this->renderers, $logger );
	}

	/**
	 * @param \TDM\Domain\ModuleDefinition $module Module definition.
	 * @param RenderContext                $context Render context.
	 * @return RenderResult
	 */
	public function render( \TDM\Domain\ModuleDefinition $module, RenderContext $context ) {
		if ( ! isset( $this->renderers[ $module->content_type ] ) ) {
			return RenderResult::fallback( __( 'The selected module renderer is not registered.', 'tutor-dashboard-modules' ) );
		}

		return $this->renderers[ $module->content_type ]->render( $module, $context );
	}
}
