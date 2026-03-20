<?php

namespace TDM\Rendering\Renderers;

use TDM\Domain\ModuleDefinition;
use TDM\Integration\WooCommerce\AccountEndpointBridge;
use TDM\Integration\WooCommerce\DownloadsProvider;
use TDM\Integration\WooCommerce\OrdersProvider;
use TDM\Plugin;
use TDM\Rendering\RenderContext;
use TDM\Rendering\RendererInterface;
use TDM\Rendering\RenderResult;

class WooCommerceEndpointRenderer implements RendererInterface {
	/**
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * @var DownloadsProvider
	 */
	private $downloads_provider;

	/**
	 * @var OrdersProvider
	 */
	private $orders_provider;

	/**
	 * @var AccountEndpointBridge
	 */
	private $account_bridge;

	/**
	 * @param Plugin               $plugin Plugin instance.
	 * @param DownloadsProvider    $downloads_provider Downloads provider.
	 * @param OrdersProvider       $orders_provider Orders provider.
	 * @param AccountEndpointBridge $account_bridge Account endpoint bridge.
	 */
	public function __construct( Plugin $plugin, DownloadsProvider $downloads_provider, OrdersProvider $orders_provider, AccountEndpointBridge $account_bridge ) {
		$this->plugin             = $plugin;
		$this->downloads_provider = $downloads_provider;
		$this->orders_provider    = $orders_provider;
		$this->account_bridge     = $account_bridge;
	}

	/**
	 * @param ModuleDefinition $module Module definition.
	 * @param RenderContext    $context Render context.
	 * @return RenderResult
	 */
	public function render( ModuleDefinition $module, RenderContext $context ) {
		if ( ! function_exists( 'wc_get_orders' ) ) {
			return RenderResult::fallback( $this->get_default_fallback_message( $module ) );
		}

		$endpoint    = (string) $module->config( 'woo_endpoint', 'downloads' );
		$render_mode = (string) $module->config( 'woo_render_mode', 'hybrid' );
		$layout      = (string) $module->config( 'woo_layout', 'downloads_table' );

		if ( 'downloads' === $endpoint ) {
			return $this->render_downloads( $module, $context, $render_mode, $layout );
		}

		if ( 'orders' === $endpoint ) {
			return $this->render_orders( $module, $context, $render_mode, $layout );
		}

		$html = $this->account_bridge->render_endpoint( $endpoint );
		if ( '' === trim( $html ) ) {
			return RenderResult::fallback( $this->get_default_fallback_message( $module ) );
		}

		return RenderResult::success( $html );
	}

	/**
	 * @param ModuleDefinition $module Module definition.
	 * @param RenderContext    $context Render context.
	 * @param string           $render_mode Render mode.
	 * @param string           $layout Layout.
	 * @return RenderResult
	 */
	private function render_downloads( ModuleDefinition $module, RenderContext $context, $render_mode, $layout ) {
		$downloads = $this->downloads_provider->get_downloads_for_user( $context->user_id );
		if ( empty( $downloads ) ) {
			return RenderResult::empty_state( $this->get_default_empty_message( $module ) );
		}

		if ( 'native_tutor' === $render_mode ) {
			return RenderResult::success(
				$this->plugin->capture_template(
					'modules/woo-downloads-native-tutor.php',
					array(
						'downloads' => $downloads,
					)
				)
			);
		}

		if ( 'custom' === $render_mode && 'downloads_cards' === $layout ) {
			return RenderResult::success(
				$this->plugin->capture_template(
					'modules/woo-downloads-custom-cards.php',
					array(
						'downloads' => $downloads,
					)
				)
			);
		}

		return RenderResult::success(
			$this->plugin->capture_template(
				'modules/woo-downloads-hybrid.php',
				array(
					'downloads'        => $downloads,
					'show_product_link'=> (bool) $module->config( 'show_product_link', false ),
					'show_expiry'      => (bool) $module->config( 'show_expiry', false ),
				)
			)
		);
	}

	/**
	 * @param ModuleDefinition $module Module definition.
	 * @param RenderContext    $context Render context.
	 * @param string           $render_mode Render mode.
	 * @param string           $layout Layout.
	 * @return RenderResult
	 */
	private function render_orders( ModuleDefinition $module, RenderContext $context, $render_mode, $layout ) {
		$orders = $this->orders_provider->get_orders_for_user( $context->user_id, 1 );
		$has_orders = is_object( $orders ) && isset( $orders->total ) ? (int) $orders->total > 0 : false;

		if ( ! $has_orders ) {
			return RenderResult::empty_state( $this->get_default_empty_message( $module ) );
		}

		if ( 'native_tutor' === $render_mode ) {
			return RenderResult::success(
				$this->plugin->capture_template(
					'modules/woo-orders-native-tutor.php',
					array(
						'orders' => $orders,
					)
				)
			);
		}

		if ( 'custom' === $render_mode && 'orders_compact' === $layout ) {
			return RenderResult::success(
				$this->plugin->capture_template(
					'modules/woo-orders-custom-compact.php',
					array(
						'orders' => $orders,
					)
				)
			);
		}

		if ( 'custom' === $render_mode ) {
			return RenderResult::success(
				$this->plugin->capture_template(
					'modules/woo-orders-custom-table.php',
					array(
						'orders' => $orders,
					)
				)
			);
		}

		return RenderResult::success(
			$this->plugin->capture_template(
				'modules/woo-orders-hybrid.php',
				array(
					'orders' => $orders,
				)
			)
		);
	}

	/**
	 * @param ModuleDefinition $module Module definition.
	 * @return string
	 */
	private function get_default_fallback_message( ModuleDefinition $module ) {
		$message = trim( (string) $module->fallback_message );
		if ( 'custom' === $module->fallback_mode && '' !== $message ) {
			return $message;
		}

		$endpoint = (string) $module->config( 'woo_endpoint', 'downloads' );
		if ( 'downloads' === $endpoint ) {
			return __( 'Este modulo requiere WooCommerce para mostrarse.', 'tutor-dashboard-modules' );
		}

		return __( 'Este modulo de WooCommerce no esta disponible en este momento.', 'tutor-dashboard-modules' );
	}

	/**
	 * @param ModuleDefinition $module Module definition.
	 * @return string
	 */
	private function get_default_empty_message( ModuleDefinition $module ) {
		$endpoint = (string) $module->config( 'woo_endpoint', 'downloads' );
		if ( 'downloads' === $endpoint ) {
			return __( 'No hay datos disponibles en esta seccion', 'tutor-dashboard-modules' );
		}

		if ( 'orders' === $endpoint ) {
			return __( 'No hay pedidos disponibles en esta seccion', 'tutor-dashboard-modules' );
		}

		return $this->get_default_fallback_message( $module );
	}
}
