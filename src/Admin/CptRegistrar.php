<?php

namespace TDM\Admin;

use TDM\Infrastructure\ModuleRepository;

class CptRegistrar {
	/**
	 * @return void
	 */
	public function register() {
		register_post_type(
			ModuleRepository::CPT,
			array(
				'labels'              => array(
					'name'          => __( 'Dashboard Modules', 'tutor-dashboard-modules' ),
					'singular_name' => __( 'Dashboard Module', 'tutor-dashboard-modules' ),
					'add_new_item'  => __( 'Add New Module', 'tutor-dashboard-modules' ),
					'edit_item'     => __( 'Edit Dashboard Module', 'tutor-dashboard-modules' ),
					'menu_name'     => __( 'Tutor Modules', 'tutor-dashboard-modules' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_rest'        => false,
				'menu_position'       => 58,
				'menu_icon'           => 'dashicons-screenoptions',
				'supports'            => array( 'title', 'page-attributes' ),
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'has_archive'         => false,
				'exclude_from_search' => true,
			)
		);

		add_filter( 'manage_' . ModuleRepository::CPT . '_posts_columns', array( $this, 'register_columns' ) );
		add_action( 'manage_' . ModuleRepository::CPT . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 30 );
	}

	/**
	 * @return void
	 */
	public function register_admin_menu() {
		$capability = 'edit_posts';
		$slug       = 'edit.php?post_type=' . ModuleRepository::CPT;
		add_submenu_page(
			'tutor',
			__( 'Tutor Modules', 'tutor-dashboard-modules' ),
			__( 'Tutor Modules', 'tutor-dashboard-modules' ),
			$capability,
			$slug
		);

		add_submenu_page(
			'tutor',
			__( 'Add New Module', 'tutor-dashboard-modules' ),
			__( 'Add New Module', 'tutor-dashboard-modules' ),
			$capability,
			'post-new.php?post_type=' . ModuleRepository::CPT
		);
	}

	/**
	 * @param array<string,string> $columns Existing columns.
	 * @return array<string,string>
	 */
	public function register_columns( $columns ) {
		return array(
			'cb'           => $columns['cb'],
			'title'        => __( 'Module', 'tutor-dashboard-modules' ),
			'endpoint'     => __( 'Endpoint', 'tutor-dashboard-modules' ),
			'content_type' => __( 'Content Type', 'tutor-dashboard-modules' ),
			'audience'     => __( 'Audience', 'tutor-dashboard-modules' ),
			'status'       => __( 'Status', 'tutor-dashboard-modules' ),
			'date'         => $columns['date'],
		);
	}

	/**
	 * @param string $column Column ID.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_column( $column, $post_id ) {
		switch ( $column ) {
			case 'endpoint':
				echo esc_html( get_post_field( 'post_name', $post_id ) );
				break;
			case 'content_type':
				$type = (string) get_post_meta( $post_id, ModuleRepository::META_CONTENT_TYPE, true ) ?: 'shortcode';
				if ( 'woocommerce_downloads' === $type ) {
					$type = 'woocommerce_endpoint';
				}
				echo esc_html( $type );
				break;
			case 'audience':
				echo esc_html( (string) get_post_meta( $post_id, ModuleRepository::META_AUDIENCE, true ) ?: 'students_only' );
				break;
			case 'status':
				echo 'publish' === get_post_status( $post_id )
					? esc_html__( 'Active', 'tutor-dashboard-modules' )
					: esc_html__( 'Inactive', 'tutor-dashboard-modules' );
				break;
		}
	}
}
