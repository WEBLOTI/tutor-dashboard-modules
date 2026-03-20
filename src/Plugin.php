<?php

namespace TDM;

use TDM\Admin\CptRegistrar;
use TDM\Admin\ModuleEditScreen;
use TDM\Domain\ModuleDefinition;
use TDM\Infrastructure\ModuleRepository;
use TDM\Infrastructure\RewriteSync;
use TDM\Integration\Elementor\ElementorBridge;
use TDM\Integration\WooCommerce\AccountEndpointBridge;
use TDM\Integration\WooCommerce\DownloadsProvider;
use TDM\Integration\WooCommerce\OrdersProvider;
use TDM\Rendering\RenderContext;
use TDM\Rendering\RendererResolver;
use TDM\Rendering\RenderResult;
use TDM\Security\PermissionsManager;
use TDM\Support\DependencyManager;
use TDM\Support\Logger;
use TDM\Support\TutorIconRegistry;
use TDM\Support\TutorUiBridge;
use TDM\Support\ViewLoader;
use TDM\Tutor\TutorDashboardIntegrator;

class Plugin {
	/**
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * @var array<string,mixed>
	 */
	private $services = array();

	/**
	 * @var bool
	 */
	private $booted = false;

	/**
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register plugin services and hooks.
	 *
	 * @return void
	 */
	public function boot() {
		if ( $this->booted ) {
			return;
		}

		$this->booted = true;

		$this->register_service( 'dependencies', new DependencyManager() );
		$this->register_service( 'logger', new Logger() );
		$this->register_service( 'rewrite_sync', new RewriteSync() );
		$this->register_service( 'repository', new ModuleRepository( $this->service( 'dependencies' ) ) );
		$this->register_service( 'view_loader', new ViewLoader( $this ) );
		$this->register_service( 'permissions', new PermissionsManager( $this->service( 'dependencies' ) ) );
		$this->register_service( 'tutor_ui', new TutorUiBridge( $this ) );
		$this->register_service( 'icon_registry', new TutorIconRegistry( $this->service( 'dependencies' ) ) );
		$this->register_service( 'elementor_bridge', new ElementorBridge() );
		$this->register_service( 'downloads_provider', new DownloadsProvider() );
		$this->register_service( 'orders_provider', new OrdersProvider() );
		$this->register_service( 'account_endpoint_bridge', new AccountEndpointBridge() );
		$this->register_service(
			'renderer_resolver',
			new RendererResolver(
				$this,
				$this->service( 'view_loader' ),
				$this->service( 'downloads_provider' ),
				$this->service( 'orders_provider' ),
				$this->service( 'account_endpoint_bridge' ),
				$this->service( 'elementor_bridge' ),
				$this->service( 'logger' )
			)
		);
		$this->register_service( 'cpt_registrar', new CptRegistrar() );
		$this->register_service(
			'module_edit_screen',
			new ModuleEditScreen(
				$this->service( 'repository' ),
				$this->service( 'rewrite_sync' ),
				$this->service( 'dependencies' ),
				$this->service( 'icon_registry' )
			)
		);
		$this->register_service(
			'tutor_integrator',
			new TutorDashboardIntegrator(
				$this,
				$this->service( 'repository' ),
				$this->service( 'permissions' ),
				$this->service( 'dependencies' ),
				$this->service( 'rewrite_sync' )
			)
		);

		add_action( 'init', array( $this->service( 'cpt_registrar' ), 'register' ) );
		add_action( 'init', array( $this->service( 'rewrite_sync' ), 'maybe_flush' ), 999 );
		$this->load_textdomain();

		$this->register_default_registries();
	}

	/**
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'tutor-dashboard-modules', false, dirname( plugin_basename( TDM_FILE ) ) . '/languages' );
	}

	/**
	 * @param string $id Service identifier.
	 * @param mixed  $service Service object.
	 * @return void
	 */
	private function register_service( $id, $service ) {
		$this->services[ $id ] = $service;
	}

	/**
	 * @param string $id Service identifier.
	 * @return mixed
	 */
	public function service( $id ) {
		return isset( $this->services[ $id ] ) ? $this->services[ $id ] : null;
	}

	/**
	 * @return void
	 */
	private function register_default_registries() {
		add_filter( 'tdm/register_php_views', array( $this, 'register_default_php_views' ) );
	}

	/**
	 * @param array<string,array<string,mixed>> $views Existing registry.
	 * @return array<string,array<string,mixed>>
	 */
	public function register_default_php_views( $views ) {
		$views['subscriptions_placeholder'] = array(
			'label'    => __( 'Subscriptions Placeholder', 'tutor-dashboard-modules' ),
			'template' => 'modules/php-view-subscriptions-placeholder.php',
		);
		$views['tools_placeholder']         = array(
			'label'    => __( 'Tools Placeholder', 'tutor-dashboard-modules' ),
			'template' => 'modules/php-view-tools-placeholder.php',
		);

		return $views;
	}

	/**
	 * Render a plugin template and return its HTML.
	 *
	 * @param string               $relative Relative template path from /templates.
	 * @param array<string, mixed> $vars Variables for the template.
	 * @return string
	 */
	public function capture_template( $relative, $vars = array() ) {
		$template = TDM_PATH . 'templates/' . ltrim( $relative, '/' );
		if ( ! file_exists( $template ) ) {
			return '';
		}

		ob_start();
		extract( $vars, EXTR_SKIP );
		include $template;

		return (string) ob_get_clean();
	}

	/**
	 * Render the current Tutor dashboard module.
	 *
	 * @return string
	 */
	public function render_current_module() {
		$slug       = (string) get_query_var( 'tutor_dashboard_page' );
		$repository = $this->service( 'repository' );
		$module     = $repository->find_by_slug( $slug );

		if ( ! $module instanceof ModuleDefinition ) {
			return '';
		}

		$context = new RenderContext(
			$module,
			get_current_user_id(),
			array(
				'tutor_dashboard_page'     => $slug,
				'tutor_dashboard_sub_page' => (string) get_query_var( 'tutor_dashboard_sub_page' ),
			),
			$this->service( 'dependencies' )->get_statuses()
		);

		$permissions = $this->service( 'permissions' );

		if ( ! $permissions->can_access( $module ) ) {
			$result = RenderResult::forbidden( __( 'You do not have access to this module.', 'tutor-dashboard-modules' ) );
		} else {
			do_action( 'tdm/module_before_render', $module, $context );
			$result = $this->service( 'renderer_resolver' )->render( $module, $context );
			$result = apply_filters( 'tdm/module_render_result', $result, $module, $context );
			do_action( 'tdm/module_after_render', $module, $context, $result );
		}

		return $this->capture_template(
			'dashboard/module-shell.php',
			array(
				'module'     => $module,
				'result'     => $result,
				'empty_html' => $this->service( 'tutor_ui' )->render_empty_state( $result->message, $result->status ),
			)
		);
	}

	/**
	 * Activation callback.
	 *
	 * @return void
	 */
	public static function activate() {
		self::instance()->boot();
		/** @var RewriteSync $rewrite_sync */
		$rewrite_sync = self::instance()->service( 'rewrite_sync' );
		$rewrite_sync->mark_pending();
	}

	/**
	 * Deactivation callback.
	 *
	 * @return void
	 */
	public static function deactivate() {
		delete_option( RewriteSync::OPTION_PENDING );
		flush_rewrite_rules( false );
	}
}
