<?php

namespace TDM\Tutor;

use TDM\Domain\ModuleDefinition;
use TDM\Infrastructure\ModuleRepository;
use TDM\Infrastructure\RewriteSync;
use TDM\Plugin;
use TDM\Security\PermissionsManager;
use TDM\Support\DependencyManager;

class TutorDashboardIntegrator {
	/**
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * @var ModuleRepository
	 */
	private $repository;

	/**
	 * @var PermissionsManager
	 */
	private $permissions;

	/**
	 * @var DependencyManager
	 */
	private $dependencies;

	/**
	 * @var RewriteSync
	 */
	private $rewrite_sync;

	/**
	 * @param Plugin             $plugin Plugin instance.
	 * @param ModuleRepository   $repository Repository.
	 * @param PermissionsManager $permissions Permissions manager.
	 * @param DependencyManager  $dependencies Dependencies service.
	 * @param RewriteSync        $rewrite_sync Rewrite sync service.
	 */
	public function __construct( Plugin $plugin, ModuleRepository $repository, PermissionsManager $permissions, DependencyManager $dependencies, RewriteSync $rewrite_sync ) {
		$this->plugin       = $plugin;
		$this->repository   = $repository;
		$this->permissions  = $permissions;
		$this->dependencies = $dependencies;
		$this->rewrite_sync = $rewrite_sync;

		add_filter( 'tutor_dashboard/nav_items', array( $this, 'register_nav_items' ) );
		add_filter( 'tutor_dashboard/permalinks', array( $this, 'register_permalinks' ) );
		add_filter( 'load_dashboard_template_part_from_other_location', array( $this, 'load_template' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * @param array<string,mixed> $items Existing nav items.
	 * @return array<string,mixed>
	 */
	public function register_nav_items( $items ) {
		if ( ! $this->dependencies->is_tutor_active() ) {
			return $items;
		}

		$before = array();
		$after  = array();
		foreach ( $items as $key => $item ) {
			if ( 'question-answer' === $key ) {
				$after[ $key ] = $item;
				continue;
			}

			if ( empty( $after ) ) {
				$before[ $key ] = $item;
			} else {
				$after[ $key ] = $item;
			}
		}

		foreach ( $this->repository->get_active_modules() as $module ) {
			if ( ! $this->permissions->is_visible( $module ) ) {
				continue;
			}

			$before[ $module->slug ] = array(
				'title' => $module->title,
				'icon'  => $module->icon ? $module->icon : 'tutor-icon-folder',
			);
		}

		return array_merge( $before, $after );
	}

	/**
	 * @param array<string,mixed> $items Existing permalinks.
	 * @return array<string,mixed>
	 */
	public function register_permalinks( $items ) {
		if ( ! $this->dependencies->is_tutor_active() ) {
			return $items;
		}

		foreach ( $this->repository->get_active_modules() as $module ) {
			$items[ $module->slug ] = array(
				'title' => $module->title,
				'icon'  => $module->icon ? $module->icon : 'tutor-icon-folder',
			);
		}

		return $items;
	}

	/**
	 * @param string $template Template path.
	 * @return string
	 */
	public function load_template( $template ) {
		if ( ! $this->dependencies->is_tutor_active() ) {
			return $template;
		}

		$slug = (string) get_query_var( 'tutor_dashboard_page' );
		if ( ! $slug ) {
			return $template;
		}

		$module = $this->repository->find_by_slug( $slug );
		if ( ! $module instanceof ModuleDefinition ) {
			return $template;
		}

		return TDM_PATH . 'templates/dashboard/module-router.php';
	}

	/**
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! $this->dependencies->is_tutor_active() || ! function_exists( 'tutor_utils' ) ) {
			return;
		}

		if ( ! tutor_utils()->is_tutor_frontend_dashboard() ) {
			return;
		}

		if ( empty( $this->repository->get_active_modules() ) ) {
			return;
		}

		wp_enqueue_style(
			'tdm-dashboard-modules',
			TDM_URL . 'assets/frontend/dashboard-modules.css',
			array(),
			TDM_VERSION
		);
	}
}
