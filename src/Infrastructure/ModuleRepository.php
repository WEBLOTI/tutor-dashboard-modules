<?php

namespace TDM\Infrastructure;

use TDM\Domain\ModuleDefinition;
use TDM\Support\DependencyManager;
use WP_Error;
use WP_Post;

class ModuleRepository {
	const CPT = 'tdm_module';

	const META_ICON              = '_tdm_icon_class';
	const META_AUDIENCE          = '_tdm_audience_type';
	const META_ROLES             = '_tdm_roles';
	const META_CAPABILITIES      = '_tdm_capabilities';
	const META_CONTENT_TYPE      = '_tdm_content_type';
	const META_DEPENDENCY_POLICY = '_tdm_dependency_policy';
	const META_FALLBACK_MODE     = '_tdm_fallback_mode';
	const META_FALLBACK_MESSAGE  = '_tdm_fallback_message';
	const META_WRAPPER_VARIANT   = '_tdm_wrapper_variant';
	const META_CACHE_TTL         = '_tdm_cache_ttl';
	const META_SHOW_TITLE        = '_tdm_show_title';
	const META_NOTES             = '_tdm_admin_notes';
	const META_CONFIG            = '_tdm_config';
	const META_ENDPOINT_SLUG     = '_tdm_endpoint_slug';

	const CACHE_KEY_ALL    = 'tdm_modules_all';
	const CACHE_KEY_ACTIVE = 'tdm_modules_active';

	/**
	 * @var DependencyManager
	 */
	private $dependencies;

	/**
	 * @param DependencyManager $dependencies Dependency service.
	 */
	public function __construct( DependencyManager $dependencies ) {
		$this->dependencies = $dependencies;

		add_action( 'save_post_' . self::CPT, array( $this, 'invalidate_cache' ) );
		add_action( 'deleted_post', array( $this, 'handle_deleted_post' ) );
	}

	/**
	 * @return array<int,ModuleDefinition>
	 */
	public function get_all_modules() {
		$cached = wp_cache_get( self::CACHE_KEY_ALL, self::CPT );
		if ( false !== $cached ) {
			return $cached;
		}

		$posts = get_posts(
			array(
				'post_type'      => self::CPT,
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => -1,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				),
			)
		);

		$modules = array();
		foreach ( $posts as $post ) {
			$modules[] = $this->hydrate( $post );
		}

		wp_cache_set( self::CACHE_KEY_ALL, $modules, self::CPT );

		return $modules;
	}

	/**
	 * @return array<int,ModuleDefinition>
	 */
	public function get_active_modules() {
		$cached = wp_cache_get( self::CACHE_KEY_ACTIVE, self::CPT );
		if ( false !== $cached ) {
			return $cached;
		}

		$modules = array_filter(
			$this->get_all_modules(),
			static function ( ModuleDefinition $module ) {
				return $module->active;
			}
		);

		wp_cache_set( self::CACHE_KEY_ACTIVE, $modules, self::CPT );

		return array_values( $modules );
	}

	/**
	 * @param string $slug Requested endpoint slug.
	 * @return ModuleDefinition|null
	 */
	public function find_by_slug( $slug ) {
		foreach ( $this->get_active_modules() as $module ) {
			if ( $module->slug === $slug ) {
				return $module;
			}
		}

		return null;
	}

	/**
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function invalidate_cache( $post_id = 0 ) {
		if ( $post_id && self::CPT !== get_post_type( $post_id ) ) {
			return;
		}

		wp_cache_delete( self::CACHE_KEY_ALL, self::CPT );
		wp_cache_delete( self::CACHE_KEY_ACTIVE, self::CPT );
	}

	/**
	 * @param int $post_id Deleted post ID.
	 * @return void
	 */
	public function handle_deleted_post( $post_id ) {
		if ( self::CPT === get_post_type( $post_id ) ) {
			$this->invalidate_cache( $post_id );
		}
	}

	/**
	 * @param int                  $post_id Module post ID.
	 * @param array<string, mixed> $posted_data Raw submitted data.
	 * @return array<string,mixed>
	 */
	public function save_module_settings( $post_id, array $posted_data ) {
		$sanitized = $this->sanitize_settings( $posted_data );

		update_post_meta( $post_id, self::META_ICON, $sanitized['icon'] );
		update_post_meta( $post_id, self::META_AUDIENCE, $sanitized['audience_type'] );
		update_post_meta( $post_id, self::META_CONTENT_TYPE, $sanitized['content_type'] );
		update_post_meta( $post_id, self::META_FALLBACK_MODE, $sanitized['fallback_mode'] );
		update_post_meta( $post_id, self::META_FALLBACK_MESSAGE, $sanitized['fallback_message'] );
		update_post_meta( $post_id, self::META_WRAPPER_VARIANT, $sanitized['wrapper_variant'] );
		update_post_meta( $post_id, self::META_CACHE_TTL, $sanitized['cache_ttl'] );
		update_post_meta( $post_id, self::META_SHOW_TITLE, $sanitized['show_title'] ? '1' : '0' );
		update_post_meta( $post_id, self::META_NOTES, $sanitized['notes'] );
		update_post_meta( $post_id, self::META_CONFIG, wp_json_encode( $sanitized['config'] ) );
		update_post_meta( $post_id, self::META_ENDPOINT_SLUG, $sanitized['endpoint_slug'] );

		$this->invalidate_cache( $post_id );

		return $sanitized;
	}

	/**
	 * @param string $slug Slug candidate.
	 * @param int    $exclude_post_id Post ID to ignore.
	 * @return true|WP_Error
	 */
	public function validate_slug( $slug, $exclude_post_id = 0 ) {
		$slug = sanitize_title( $slug );
		if ( '' === $slug ) {
			return new WP_Error( 'tdm_empty_slug', __( 'The endpoint slug cannot be empty.', 'tutor-dashboard-modules' ) );
		}

		if ( in_array( $slug, $this->get_reserved_slugs(), true ) ) {
			return new WP_Error( 'tdm_reserved_slug', __( 'That endpoint slug is reserved by Tutor LMS or this plugin.', 'tutor-dashboard-modules' ) );
		}

		$existing = get_posts(
			array(
				'post_type'      => self::CPT,
				'name'           => $slug,
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'exclude'        => $exclude_post_id ? array( $exclude_post_id ) : array(),
			)
		);

		if ( ! empty( $existing ) ) {
			return new WP_Error( 'tdm_duplicate_slug', __( 'Another dashboard module is already using that endpoint slug.', 'tutor-dashboard-modules' ) );
		}

		return true;
	}

	/**
	 * @return array<int,string>
	 */
	public function get_reserved_slugs() {
		$slugs = array(
			'index',
			'settings',
			'logout',
			'retrieve-password',
		);

		if ( $this->dependencies->is_tutor_active() ) {
			$pages = (array) tutor_utils()->tutor_dashboard_permalinks();
			$slugs = array_merge( $slugs, array_keys( $pages ) );
		}

		$custom_slugs = array();
		foreach ( $this->get_all_modules() as $module ) {
			$custom_slugs[] = $module->slug;
		}

		$slugs = array_diff( $slugs, $custom_slugs );

		return array_values( array_unique( array_filter( array_map( 'sanitize_title', $slugs ) ) ) );
	}

	/**
	 * @param WP_Post $post Module post object.
	 * @return ModuleDefinition
	 */
	private function hydrate( WP_Post $post ) {
		$config = json_decode( (string) get_post_meta( $post->ID, self::META_CONFIG, true ), true );
		if ( ! is_array( $config ) ) {
			$config = array();
		}

		return new ModuleDefinition(
			array(
				'id'                => (int) $post->ID,
				'title'             => $post->post_title,
				'slug'              => $post->post_name,
				'icon'              => (string) get_post_meta( $post->ID, self::META_ICON, true ),
				'menu_order'        => (int) $post->menu_order,
				'active'            => 'publish' === $post->post_status,
				'audience_type'     => $this->normalize_audience( (string) get_post_meta( $post->ID, self::META_AUDIENCE, true ) ),
				'content_type'      => $this->normalize_content_type( (string) get_post_meta( $post->ID, self::META_CONTENT_TYPE, true ) ?: 'shortcode' ),
				'fallback_mode'     => (string) get_post_meta( $post->ID, self::META_FALLBACK_MODE, true ) ?: 'default',
				'fallback_message'  => (string) get_post_meta( $post->ID, self::META_FALLBACK_MESSAGE, true ),
				'wrapper_variant'   => (string) get_post_meta( $post->ID, self::META_WRAPPER_VARIANT, true ) ?: 'card',
				'cache_ttl'         => (int) get_post_meta( $post->ID, self::META_CACHE_TTL, true ),
				'show_title'        => '0' !== (string) get_post_meta( $post->ID, self::META_SHOW_TITLE, true ),
				'notes'             => (string) get_post_meta( $post->ID, self::META_NOTES, true ),
				'config'            => $this->normalize_config(
					$this->normalize_content_type( (string) get_post_meta( $post->ID, self::META_CONTENT_TYPE, true ) ?: 'shortcode' ),
					$config
				),
			)
		);
	}

	/**
	 * @param array<string,mixed> $posted_data Submitted module settings.
	 * @return array<string,mixed>
	 */
	private function sanitize_settings( array $posted_data ) {
		$type = isset( $posted_data['tdm_content_type'] ) ? sanitize_key( wp_unslash( $posted_data['tdm_content_type'] ) ) : 'shortcode';

		$config = array();
		if ( isset( $posted_data['tdm_template_id'] ) ) {
			$config['template_id'] = absint( $posted_data['tdm_template_id'] );
		}
		if ( isset( $posted_data['tdm_shortcode_text'] ) ) {
			$config['shortcode_text'] = sanitize_textarea_field( wp_unslash( $posted_data['tdm_shortcode_text'] ) );
		}
		if ( isset( $posted_data['tdm_php_view_key'] ) ) {
			$config['view_key'] = sanitize_key( wp_unslash( $posted_data['tdm_php_view_key'] ) );
		}
		if ( isset( $posted_data['tdm_callback_key'] ) ) {
			$config['callback_key'] = sanitize_key( wp_unslash( $posted_data['tdm_callback_key'] ) );
		}
		if ( isset( $posted_data['tdm_provider_key'] ) ) {
			$config['provider_key'] = sanitize_key( wp_unslash( $posted_data['tdm_provider_key'] ) );
		}
		if ( isset( $posted_data['tdm_dynamic_view_key'] ) ) {
			$config['view_key'] = sanitize_key( wp_unslash( $posted_data['tdm_dynamic_view_key'] ) );
		}
		if ( isset( $posted_data['tdm_woo_endpoint'] ) ) {
			$config['woo_endpoint'] = sanitize_key( wp_unslash( $posted_data['tdm_woo_endpoint'] ) );
		}
		if ( isset( $posted_data['tdm_woo_render_mode'] ) ) {
			$config['woo_render_mode'] = sanitize_key( wp_unslash( $posted_data['tdm_woo_render_mode'] ) );
		}
		if ( isset( $posted_data['tdm_woo_layout'] ) ) {
			$config['woo_layout'] = sanitize_key( wp_unslash( $posted_data['tdm_woo_layout'] ) );
		}
		$config['show_product_link'] = ! empty( $posted_data['tdm_show_product_link'] );
		$config['show_expiry']       = ! empty( $posted_data['tdm_show_expiry'] );

		return array(
			'endpoint_slug'      => isset( $posted_data['tdm_endpoint_slug'] ) ? sanitize_title( wp_unslash( $posted_data['tdm_endpoint_slug'] ) ) : '',
			'icon'               => isset( $posted_data['tdm_icon_class'] ) ? $this->sanitize_class_list( wp_unslash( $posted_data['tdm_icon_class'] ) ) : '',
			'audience_type'      => $this->normalize_audience( isset( $posted_data['tdm_audience_type'] ) ? sanitize_key( wp_unslash( $posted_data['tdm_audience_type'] ) ) : 'students_only' ),
			'content_type'       => $this->normalize_content_type( $type ),
			'fallback_mode'      => isset( $posted_data['tdm_fallback_mode'] ) ? sanitize_key( wp_unslash( $posted_data['tdm_fallback_mode'] ) ) : 'default',
			'fallback_message'   => isset( $posted_data['tdm_fallback_message'] ) ? sanitize_textarea_field( wp_unslash( $posted_data['tdm_fallback_message'] ) ) : '',
			'wrapper_variant'    => isset( $posted_data['tdm_wrapper_variant'] ) ? sanitize_key( wp_unslash( $posted_data['tdm_wrapper_variant'] ) ) : 'card',
			'cache_ttl'          => isset( $posted_data['tdm_cache_ttl'] ) ? absint( $posted_data['tdm_cache_ttl'] ) : 0,
			'show_title'         => ! empty( $posted_data['tdm_show_title'] ),
			'notes'              => isset( $posted_data['tdm_admin_notes'] ) ? sanitize_textarea_field( wp_unslash( $posted_data['tdm_admin_notes'] ) ) : '',
			'config'             => $config,
		);
	}

	/**
	 * @param string $classes Raw CSS classes.
	 * @return string
	 */
	private function sanitize_class_list( $classes ) {
		$parts = preg_split( '/\s+/', (string) $classes );
		$parts = array_filter( array_map( 'sanitize_html_class', $parts ) );

		return implode( ' ', array_unique( $parts ) );
	}

	/**
	 * @param string $audience Raw audience value.
	 * @return string
	 */
	private function normalize_audience( $audience ) {
		$allowed = array( 'students_only', 'instructors_only', 'logged_in_any' );
		if ( in_array( $audience, $allowed, true ) ) {
			return $audience;
		}

		return 'logged_in_any';
	}

	/**
	 * @param string $type Raw content type.
	 * @return string
	 */
	private function normalize_content_type( $type ) {
		if ( 'woocommerce_downloads' === $type ) {
			return 'woocommerce_endpoint';
		}

		return $type;
	}

	/**
	 * @param string               $type Normalized type.
	 * @param array<string,mixed>  $config Stored config.
	 * @return array<string,mixed>
	 */
	private function normalize_config( $type, array $config ) {
		if ( 'woocommerce_endpoint' !== $type ) {
			return $config;
		}

		if ( empty( $config['woo_endpoint'] ) ) {
			$config['woo_endpoint'] = 'downloads';
		}

		if ( empty( $config['woo_render_mode'] ) ) {
			$config['woo_render_mode'] = 'hybrid';
		}

		if ( empty( $config['woo_layout'] ) ) {
			$config['woo_layout'] = 'downloads' === $config['woo_endpoint'] ? 'downloads_table' : 'orders_table';
		}

		return $config;
	}
}
