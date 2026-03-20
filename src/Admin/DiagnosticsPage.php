<?php

namespace TDM\Admin;

use TDM\Infrastructure\ModuleRepository;
use TDM\Infrastructure\RewriteSync;
use TDM\Support\DependencyManager;
use TDM\Support\Logger;
use TDM\Support\ViewLoader;

class DiagnosticsPage {
	/**
	 * @var ModuleRepository
	 */
	private $repository;

	/**
	 * @var DependencyManager
	 */
	private $dependencies;

	/**
	 * @var Logger
	 */
	private $logger;

	/**
	 * @var ViewLoader
	 */
	private $view_loader;

	/**
	 * @var bool
	 */
	private $rewrite_pending = false;

	/**
	 * @param ModuleRepository  $repository Repository.
	 * @param DependencyManager $dependencies Dependency manager.
	 * @param Logger            $logger Logger.
	 * @param ViewLoader        $view_loader View registry.
	 */
	public function __construct( ModuleRepository $repository, DependencyManager $dependencies, Logger $logger, ViewLoader $view_loader ) {
		$this->repository   = $repository;
		$this->dependencies = $dependencies;
		$this->logger       = $logger;
		$this->view_loader  = $view_loader;
		$this->rewrite_pending = (bool) get_option( RewriteSync::OPTION_PENDING, false );

		add_action( 'admin_menu', array( $this, 'register_page' ) );
	}

	/**
	 * @return void
	 */
	public function register_page() {
		add_submenu_page(
			'edit.php?post_type=' . ModuleRepository::CPT,
			__( 'Module Diagnostics', 'tutor-dashboard-modules' ),
			__( 'Diagnostics', 'tutor-dashboard-modules' ),
			'manage_options',
			'tdm-diagnostics',
			array( $this, 'render_page' )
		);
	}

	/**
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$modules = $this->repository->get_all_modules();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Tutor Dashboard Modules Diagnostics', 'tutor-dashboard-modules' ); ?></h1>
			<h2><?php esc_html_e( 'Dependencies', 'tutor-dashboard-modules' ); ?></h2>
			<p>
				<strong><?php esc_html_e( 'Rewrite Status:', 'tutor-dashboard-modules' ); ?></strong>
				<?php echo $this->rewrite_pending ? esc_html__( 'Pending flush', 'tutor-dashboard-modules' ) : esc_html__( 'In sync', 'tutor-dashboard-modules' ); ?>
			</p>
			<table class="widefat striped">
				<tbody>
					<?php foreach ( $this->dependencies->get_statuses() as $key => $status ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $key ); ?></strong></td>
							<td><?php echo $status ? esc_html__( 'Active', 'tutor-dashboard-modules' ) : esc_html__( 'Missing', 'tutor-dashboard-modules' ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<h2><?php esc_html_e( 'Modules', 'tutor-dashboard-modules' ); ?></h2>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Title', 'tutor-dashboard-modules' ); ?></th>
						<th><?php esc_html_e( 'Slug', 'tutor-dashboard-modules' ); ?></th>
						<th><?php esc_html_e( 'Type', 'tutor-dashboard-modules' ); ?></th>
						<th><?php esc_html_e( 'Status', 'tutor-dashboard-modules' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $modules ) ) : ?>
						<tr><td colspan="4"><?php esc_html_e( 'No modules found yet.', 'tutor-dashboard-modules' ); ?></td></tr>
					<?php endif; ?>
					<?php foreach ( $modules as $module ) : ?>
						<tr>
							<td><?php echo esc_html( $module->title ); ?></td>
							<td><?php echo esc_html( $module->slug ); ?></td>
							<td><?php echo esc_html( $module->content_type ); ?></td>
							<td><?php echo $module->active ? esc_html__( 'Active', 'tutor-dashboard-modules' ) : esc_html__( 'Inactive', 'tutor-dashboard-modules' ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<h2><?php esc_html_e( 'Registered Views and Callbacks', 'tutor-dashboard-modules' ); ?></h2>
			<ul>
				<li><?php printf( esc_html__( 'PHP Views: %d', 'tutor-dashboard-modules' ), count( $this->view_loader->get_registered_php_views() ) ); ?></li>
				<li><?php printf( esc_html__( 'Callbacks: %d', 'tutor-dashboard-modules' ), count( $this->view_loader->get_registered_callbacks() ) ); ?></li>
				<li><?php printf( esc_html__( 'Dynamic Providers: %d', 'tutor-dashboard-modules' ), count( $this->view_loader->get_registered_dynamic_providers() ) ); ?></li>
			</ul>

			<h2><?php esc_html_e( 'Recent Logs', 'tutor-dashboard-modules' ); ?></h2>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Timestamp', 'tutor-dashboard-modules' ); ?></th>
						<th><?php esc_html_e( 'Level', 'tutor-dashboard-modules' ); ?></th>
						<th><?php esc_html_e( 'Message', 'tutor-dashboard-modules' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php $logs = $this->logger->get_recent_logs(); ?>
					<?php if ( empty( $logs ) ) : ?>
						<tr><td colspan="3"><?php esc_html_e( 'No logs recorded.', 'tutor-dashboard-modules' ); ?></td></tr>
					<?php endif; ?>
					<?php foreach ( $logs as $log ) : ?>
						<tr>
							<td><?php echo esc_html( isset( $log['timestamp'] ) ? $log['timestamp'] : '' ); ?></td>
							<td><?php echo esc_html( isset( $log['level'] ) ? $log['level'] : '' ); ?></td>
							<td><?php echo esc_html( isset( $log['message'] ) ? $log['message'] : '' ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
