<?php

namespace TDM\Rendering\Renderers;

use TDM\Domain\ModuleDefinition;
use TDM\Integration\WooCommerce\DownloadsProvider;
use TDM\Plugin;
use TDM\Rendering\RenderContext;
use TDM\Rendering\RendererInterface;
use TDM\Rendering\RenderResult;

class WooCommerceDownloadsRenderer implements RendererInterface {
	/**
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * @var DownloadsProvider
	 */
	private $provider;

	/**
	 * @param Plugin            $plugin Plugin instance.
	 * @param DownloadsProvider $provider Downloads provider.
	 */
	public function __construct( Plugin $plugin, DownloadsProvider $provider ) {
		$this->plugin   = $plugin;
		$this->provider = $provider;
	}

	/**
	 * @param ModuleDefinition $module Module definition.
	 * @param RenderContext    $context Render context.
	 * @return RenderResult
	 */
	public function render( ModuleDefinition $module, RenderContext $context ) {
		if ( ! function_exists( 'wc_get_customer_available_downloads' ) ) {
			return RenderResult::fallback( __( 'WooCommerce is not active, so downloads cannot be displayed.', 'tutor-dashboard-modules' ) );
		}

		$downloads = $this->provider->get_downloads_for_user( $context->user_id );
		if ( empty( $downloads ) ) {
			return RenderResult::empty_state( __( 'No downloads are currently available for your account.', 'tutor-dashboard-modules' ) );
		}

		$html = $this->plugin->capture_template(
			'modules/downloads-table.php',
			array(
				'downloads'        => $downloads,
				'show_product_link'=> (bool) $module->config( 'show_product_link', false ),
				'show_expiry'      => (bool) $module->config( 'show_expiry', false ),
			)
		);

		return RenderResult::success( $html );
	}
}
