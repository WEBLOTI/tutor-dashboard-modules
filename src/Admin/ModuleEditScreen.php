<?php

namespace TDM\Admin;

use TDM\Infrastructure\ModuleRepository;
use TDM\Infrastructure\RewriteSync;
use TDM\Support\DependencyManager;
use TDM\Support\TutorIconRegistry;

class ModuleEditScreen {
	const NOTICE_OPTION = 'tdm_admin_notice_';

	/**
	 * @var ModuleRepository
	 */
	private $repository;

	/**
	 * @var RewriteSync
	 */
	private $rewrite_sync;

	/**
	 * @var DependencyManager
	 */
	private $dependencies;

	/**
	 * @var TutorIconRegistry
	 */
	private $icon_registry;

	/**
	 * @var bool
	 */
	private $syncing_slug = false;

	/**
	 * @param ModuleRepository  $repository Repository.
	 * @param RewriteSync       $rewrite_sync Rewrite synchronizer.
	 * @param DependencyManager $dependencies Dependency service.
	 * @param TutorIconRegistry $icon_registry Icon registry.
	 */
	public function __construct( ModuleRepository $repository, RewriteSync $rewrite_sync, DependencyManager $dependencies, TutorIconRegistry $icon_registry ) {
		$this->repository    = $repository;
		$this->rewrite_sync  = $rewrite_sync;
		$this->dependencies  = $dependencies;
		$this->icon_registry = $icon_registry;

		add_action( 'add_meta_boxes_' . ModuleRepository::CPT, array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post_' . ModuleRepository::CPT, array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_notices', array( $this, 'render_notice' ) );
	}

	/**
	 * @return void
	 */
	public function register_meta_boxes() {
		add_meta_box(
			'tdm-module-config',
			__( 'Module Configuration', 'tutor-dashboard-modules' ),
			array( $this, 'render_meta_box' ),
			ModuleRepository::CPT,
			'normal',
			'high'
		);
	}

	/**
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function render_meta_box( $post ) {
		$php_views      = tdm()->service( 'view_loader' )->get_registered_php_views();
		$callbacks      = tdm()->service( 'view_loader' )->get_registered_callbacks();
		$dynamic_views  = tdm()->service( 'view_loader' )->get_registered_dynamic_providers();
		$content_types  = $this->get_content_types( $callbacks, $dynamic_views );
		$current_type   = (string) get_post_meta( $post->ID, ModuleRepository::META_CONTENT_TYPE, true ) ?: 'shortcode';
		if ( 'woocommerce_downloads' === $current_type ) {
			$current_type = 'woocommerce_endpoint';
		}
		$config         = json_decode( (string) get_post_meta( $post->ID, ModuleRepository::META_CONFIG, true ), true );
		$config         = is_array( $config ) ? $config : array();
		$endpoint_slug  = (string) get_post_meta( $post->ID, ModuleRepository::META_ENDPOINT_SLUG, true );
		$endpoint_slug  = $endpoint_slug ?: $post->post_name;
		$icons          = $this->icon_registry->get_icons();
		$selected_icon  = (string) get_post_meta( $post->ID, ModuleRepository::META_ICON, true );
		$selected_icon  = $selected_icon ?: 'tutor-icon-folder';
		$fallback_mode  = (string) get_post_meta( $post->ID, ModuleRepository::META_FALLBACK_MODE, true );
		$fallback_mode  = $fallback_mode ?: 'default';
		$audience_type  = (string) get_post_meta( $post->ID, ModuleRepository::META_AUDIENCE, true );
		$audience_type  = $audience_type ?: 'students_only';
		$is_legacy_icon = ! in_array( $selected_icon, $icons, true );

		if ( $is_legacy_icon ) {
			array_unshift( $icons, $selected_icon );
		}

		wp_nonce_field( 'tdm_save_module', 'tdm_module_nonce' );
		?>
		<div class="tdm-admin">
			<p class="description"><?php esc_html_e( 'Manage custom Tutor dashboard modules with a simpler, Tutor-aligned interface.', 'tutor-dashboard-modules' ); ?></p>

			<section class="tdm-panel">
				<h3><?php esc_html_e( 'General', 'tutor-dashboard-modules' ); ?></h3>
				<div class="tdm-grid">
					<p>
						<label for="tdm_endpoint_slug"><strong><?php esc_html_e( 'Endpoint Slug', 'tutor-dashboard-modules' ); ?></strong></label><br />
						<input type="text" class="regular-text" id="tdm_endpoint_slug" name="tdm_endpoint_slug" value="<?php echo esc_attr( $endpoint_slug ); ?>" />
					</p>
					<p>
						<label for="tdm_content_type"><strong><?php esc_html_e( 'Content Type', 'tutor-dashboard-modules' ); ?></strong></label><br />
						<select id="tdm_content_type" name="tdm_content_type">
							<?php foreach ( $content_types as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current_type, $key ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<p>
						<label for="tdm_audience_type"><strong><?php esc_html_e( 'Audience', 'tutor-dashboard-modules' ); ?></strong></label><br />
						<select id="tdm_audience_type" name="tdm_audience_type">
							<option value="students_only" <?php selected( $audience_type, 'students_only' ); ?>><?php esc_html_e( 'Students Only', 'tutor-dashboard-modules' ); ?></option>
							<option value="instructors_only" <?php selected( $audience_type, 'instructors_only' ); ?>><?php esc_html_e( 'Instructors Only', 'tutor-dashboard-modules' ); ?></option>
							<option value="logged_in_any" <?php selected( $audience_type, 'logged_in_any' ); ?>><?php esc_html_e( 'Any Logged-in User', 'tutor-dashboard-modules' ); ?></option>
						</select>
					</p>
					<div class="tdm-icon-picker-wrap">
						<label><strong><?php esc_html_e( 'Tutor Icon', 'tutor-dashboard-modules' ); ?></strong></label>
						<input type="hidden" id="tdm_icon_class" name="tdm_icon_class" value="<?php echo esc_attr( $selected_icon ); ?>" />
						<input type="search" id="tdm_icon_search" class="regular-text" placeholder="<?php esc_attr_e( 'Search icon', 'tutor-dashboard-modules' ); ?>" />
						<div class="tdm-selected-icon">
							<span id="tdm_icon_preview" class="<?php echo esc_attr( $selected_icon ); ?>"></span>
							<code id="tdm_icon_label"><?php echo esc_html( $selected_icon ); ?></code>
						</div>
						<?php if ( $is_legacy_icon ) : ?>
							<p class="description"><?php esc_html_e( 'The current icon comes from a legacy custom class. You can keep it or replace it with a Tutor icon.', 'tutor-dashboard-modules' ); ?></p>
						<?php endif; ?>
						<div class="tdm-icon-grid" id="tdm_icon_grid" data-selected="<?php echo esc_attr( $selected_icon ); ?>">
							<?php foreach ( $icons as $icon ) : ?>
								<button type="button" class="tdm-icon-option<?php echo $icon === $selected_icon ? ' is-active' : ''; ?>" data-icon="<?php echo esc_attr( $icon ); ?>">
									<span class="<?php echo esc_attr( $icon ); ?>"></span>
									<small><?php echo esc_html( $icon ); ?></small>
								</button>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</section>

			<section class="tdm-panel">
				<h3><?php esc_html_e( 'Content', 'tutor-dashboard-modules' ); ?></h3>
				<div class="tdm-type-panels">
					<div class="tdm-type-panel" data-tdm-panel="elementor_template">
						<p class="description"><?php esc_html_e( 'Render an Elementor template inside the Tutor dashboard endpoint.', 'tutor-dashboard-modules' ); ?></p>
						<p>
							<label for="tdm_template_id"><strong><?php esc_html_e( 'Elementor Template ID', 'tutor-dashboard-modules' ); ?></strong></label><br />
							<input type="number" min="0" id="tdm_template_id" name="tdm_template_id" value="<?php echo esc_attr( (string) ( isset( $config['template_id'] ) ? $config['template_id'] : '' ) ); ?>" />
						</p>
					</div>

					<div class="tdm-type-panel" data-tdm-panel="shortcode">
						<p class="description"><?php esc_html_e( 'Execute a shortcode and show its output inside the dashboard.', 'tutor-dashboard-modules' ); ?></p>
						<p>
							<label for="tdm_shortcode_text"><strong><?php esc_html_e( 'Shortcode', 'tutor-dashboard-modules' ); ?></strong></label><br />
							<textarea id="tdm_shortcode_text" name="tdm_shortcode_text" rows="5" class="large-text"><?php echo esc_textarea( isset( $config['shortcode_text'] ) ? (string) $config['shortcode_text'] : '' ); ?></textarea>
						</p>
					</div>

					<div class="tdm-type-panel" data-tdm-panel="php_view">
						<p class="description"><?php esc_html_e( 'Use a registered internal plugin view for controlled PHP output.', 'tutor-dashboard-modules' ); ?></p>
						<p>
							<label for="tdm_php_view_key"><strong><?php esc_html_e( 'Registered PHP View', 'tutor-dashboard-modules' ); ?></strong></label><br />
							<select id="tdm_php_view_key" name="tdm_php_view_key">
								<option value=""><?php esc_html_e( 'Select a view', 'tutor-dashboard-modules' ); ?></option>
								<?php foreach ( $php_views as $key => $view ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $config['view_key'] ) ? $config['view_key'] : '', $key ); ?>><?php echo esc_html( isset( $view['label'] ) ? $view['label'] : $key ); ?></option>
								<?php endforeach; ?>
							</select>
						</p>
					</div>

					<div class="tdm-type-panel" data-tdm-panel="woocommerce_endpoint">
						<p class="description"><?php esc_html_e( 'Choose a WooCommerce account endpoint and how it should be rendered inside Tutor.', 'tutor-dashboard-modules' ); ?></p>
						<div class="tdm-grid">
							<p>
								<label for="tdm_woo_endpoint"><strong><?php esc_html_e( 'WooCommerce Endpoint', 'tutor-dashboard-modules' ); ?></strong></label><br />
								<select id="tdm_woo_endpoint" name="tdm_woo_endpoint">
									<?php foreach ( $this->get_woo_endpoints() as $key => $label ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $config['woo_endpoint'] ) ? $config['woo_endpoint'] : 'downloads', $key ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</p>
							<p>
								<label for="tdm_woo_render_mode"><strong><?php esc_html_e( 'Render Mode', 'tutor-dashboard-modules' ); ?></strong></label><br />
								<select id="tdm_woo_render_mode" name="tdm_woo_render_mode"></select>
							</p>
							<p>
								<label for="tdm_woo_layout"><strong><?php esc_html_e( 'Layout', 'tutor-dashboard-modules' ); ?></strong></label><br />
								<select id="tdm_woo_layout" name="tdm_woo_layout"></select>
							</p>
							<p class="tdm-field-downloads-only">
								<label><input type="checkbox" name="tdm_show_product_link" value="1" <?php checked( ! empty( $config['show_product_link'] ) ); ?> /> <?php esc_html_e( 'Show product links', 'tutor-dashboard-modules' ); ?></label>
							</p>
							<p class="tdm-field-downloads-only">
								<label><input type="checkbox" name="tdm_show_expiry" value="1" <?php checked( ! empty( $config['show_expiry'] ) ); ?> /> <?php esc_html_e( 'Show access expiry', 'tutor-dashboard-modules' ); ?></label>
							</p>
						</div>
					</div>

					<div class="tdm-type-panel" data-tdm-panel="custom_callback">
						<p>
							<label for="tdm_callback_key"><strong><?php esc_html_e( 'Registered Callback', 'tutor-dashboard-modules' ); ?></strong></label><br />
							<select id="tdm_callback_key" name="tdm_callback_key">
								<option value=""><?php esc_html_e( 'Select a callback', 'tutor-dashboard-modules' ); ?></option>
								<?php foreach ( $callbacks as $key => $callback ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $config['callback_key'] ) ? $config['callback_key'] : '', $key ); ?>><?php echo esc_html( isset( $callback['label'] ) ? $callback['label'] : $key ); ?></option>
								<?php endforeach; ?>
							</select>
						</p>
					</div>

					<div class="tdm-type-panel" data-tdm-panel="dynamic_data_view">
						<p>
							<label for="tdm_provider_key"><strong><?php esc_html_e( 'Dynamic Provider', 'tutor-dashboard-modules' ); ?></strong></label><br />
							<select id="tdm_provider_key" name="tdm_provider_key">
								<option value=""><?php esc_html_e( 'Select a provider', 'tutor-dashboard-modules' ); ?></option>
								<?php foreach ( $dynamic_views as $key => $provider ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $config['provider_key'] ) ? $config['provider_key'] : '', $key ); ?>><?php echo esc_html( isset( $provider['label'] ) ? $provider['label'] : $key ); ?></option>
								<?php endforeach; ?>
							</select>
						</p>
						<p>
							<label for="tdm_dynamic_view_key"><strong><?php esc_html_e( 'Registered View Key', 'tutor-dashboard-modules' ); ?></strong></label><br />
							<input type="text" id="tdm_dynamic_view_key" name="tdm_dynamic_view_key" class="regular-text" value="<?php echo esc_attr( isset( $config['view_key'] ) ? (string) $config['view_key'] : '' ); ?>" />
						</p>
					</div>
				</div>
			</section>

			<section class="tdm-panel">
				<h3><?php esc_html_e( 'Fallback', 'tutor-dashboard-modules' ); ?></h3>
				<p class="description"><?php esc_html_e( 'If the selected integration cannot render, the module will show a Tutor-style empty state.', 'tutor-dashboard-modules' ); ?></p>
				<div class="tdm-grid">
					<p>
						<label for="tdm_fallback_mode"><strong><?php esc_html_e( 'Fallback Message', 'tutor-dashboard-modules' ); ?></strong></label><br />
						<select id="tdm_fallback_mode" name="tdm_fallback_mode">
							<option value="default" <?php selected( $fallback_mode, 'default' ); ?>><?php esc_html_e( 'Use default message', 'tutor-dashboard-modules' ); ?></option>
							<option value="custom" <?php selected( $fallback_mode, 'custom' ); ?>><?php esc_html_e( 'Use custom message', 'tutor-dashboard-modules' ); ?></option>
						</select>
					</p>
					<p class="tdm-fallback-message-wrap">
						<label for="tdm_fallback_message"><strong><?php esc_html_e( 'Custom fallback message', 'tutor-dashboard-modules' ); ?></strong></label><br />
						<textarea id="tdm_fallback_message" name="tdm_fallback_message" rows="3" class="large-text"><?php echo esc_textarea( (string) get_post_meta( $post->ID, ModuleRepository::META_FALLBACK_MESSAGE, true ) ); ?></textarea>
					</p>
				</div>
			</section>

			<section class="tdm-panel tdm-panel--advanced">
				<h3><?php esc_html_e( 'Advanced', 'tutor-dashboard-modules' ); ?></h3>
				<div class="tdm-grid">
					<p>
						<label><input type="checkbox" name="tdm_show_title" value="1" <?php checked( '0' !== (string) get_post_meta( $post->ID, ModuleRepository::META_SHOW_TITLE, true ) ); ?> /> <?php esc_html_e( 'Show module title inside dashboard content', 'tutor-dashboard-modules' ); ?></label>
					</p>
					<p>
						<label for="tdm_wrapper_variant"><strong><?php esc_html_e( 'Wrapper Variant', 'tutor-dashboard-modules' ); ?></strong></label><br />
						<select id="tdm_wrapper_variant" name="tdm_wrapper_variant">
							<option value="card" <?php selected( get_post_meta( $post->ID, ModuleRepository::META_WRAPPER_VARIANT, true ), 'card' ); ?>><?php esc_html_e( 'Card', 'tutor-dashboard-modules' ); ?></option>
							<option value="plain" <?php selected( get_post_meta( $post->ID, ModuleRepository::META_WRAPPER_VARIANT, true ), 'plain' ); ?>><?php esc_html_e( 'Plain', 'tutor-dashboard-modules' ); ?></option>
						</select>
					</p>
					<p>
						<label for="tdm_cache_ttl"><strong><?php esc_html_e( 'Cache TTL (seconds)', 'tutor-dashboard-modules' ); ?></strong></label><br />
						<input type="number" min="0" step="60" id="tdm_cache_ttl" name="tdm_cache_ttl" value="<?php echo esc_attr( (string) get_post_meta( $post->ID, ModuleRepository::META_CACHE_TTL, true ) ); ?>" />
					</p>
					<p>
						<label for="tdm_admin_notes"><strong><?php esc_html_e( 'Admin Notes', 'tutor-dashboard-modules' ); ?></strong></label><br />
						<textarea id="tdm_admin_notes" name="tdm_admin_notes" rows="4" class="large-text"><?php echo esc_textarea( (string) get_post_meta( $post->ID, ModuleRepository::META_NOTES, true ) ); ?></textarea>
					</p>
				</div>
			</section>
		</div>
		<?php
	}

	/**
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function save( $post_id, $post ) {
		if ( $this->syncing_slug ) {
			return;
		}

		if ( ! isset( $_POST['tdm_module_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tdm_module_nonce'] ) ), 'tdm_save_module' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$sanitized = $this->repository->save_module_settings( $post_id, $_POST );
		$slug      = $sanitized['endpoint_slug'] ? $sanitized['endpoint_slug'] : sanitize_title( $post->post_title );
		$valid     = $this->repository->validate_slug( $slug, $post_id );

		if ( is_wp_error( $valid ) ) {
			$this->set_notice( $valid->get_error_message(), 'error' );
			$slug = '' === $post->post_name ? 'module-' . $post_id : $post->post_name;
		} else {
			$this->set_notice( __( 'Dashboard module saved successfully.', 'tutor-dashboard-modules' ) );
		}

		if ( $slug !== $post->post_name ) {
			$this->syncing_slug = true;
			wp_update_post(
				array(
					'ID'        => $post_id,
					'post_name' => $slug,
				)
			);
			$this->syncing_slug = false;
		}

		update_post_meta( $post_id, ModuleRepository::META_ENDPOINT_SLUG, $slug );
		$this->repository->invalidate_cache( $post_id );
		$this->rewrite_sync->mark_pending();
	}

	/**
	 * @param string $hook_suffix Current hook suffix.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		$screen = get_current_screen();
		if ( ! $screen || ModuleRepository::CPT !== $screen->post_type ) {
			return;
		}

		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$config  = $post_id ? json_decode( (string) get_post_meta( $post_id, ModuleRepository::META_CONFIG, true ), true ) : array();
		$config  = is_array( $config ) ? $config : array();
		$content_type = $post_id ? (string) get_post_meta( $post_id, ModuleRepository::META_CONTENT_TYPE, true ) : 'shortcode';
		if ( 'woocommerce_downloads' === $content_type ) {
			$content_type = 'woocommerce_endpoint';
		}

		wp_enqueue_style(
			'tdm-admin-module-edit',
			TDM_URL . 'assets/admin/module-edit.css',
			array(),
			TDM_VERSION
		);
		wp_enqueue_script(
			'tdm-admin-module-edit',
			TDM_URL . 'assets/admin/module-edit.js',
			array(),
			TDM_VERSION,
			true
		);

		wp_localize_script(
			'tdm-admin-module-edit',
			'tdmAdmin',
			array(
				'contentType'   => $content_type,
				'icons'         => $this->icon_registry->get_icons(),
				'wooOptions'    => $this->get_woo_ui_options(),
				'wooConfig'     => array(
					'endpoint'    => (string) ( $config['woo_endpoint'] ?? 'downloads' ),
					'renderMode'  => (string) ( $config['woo_render_mode'] ?? 'hybrid' ),
					'layout'      => (string) ( $config['woo_layout'] ?? 'downloads_table' ),
				),
				'fallbackMode'  => $post_id ? (string) get_post_meta( $post_id, ModuleRepository::META_FALLBACK_MODE, true ) : 'default',
			)
		);
	}

	/**
	 * @return void
	 */
	public function render_notice() {
		$notice = get_transient( self::NOTICE_OPTION . get_current_user_id() );
		if ( empty( $notice ) ) {
			return;
		}

		delete_transient( self::NOTICE_OPTION . get_current_user_id() );
		printf(
			'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
			esc_attr( isset( $notice['type'] ) ? $notice['type'] : 'success' ),
			esc_html( isset( $notice['message'] ) ? $notice['message'] : '' )
		);
	}

	/**
	 * @param string $message Notice message.
	 * @param string $type Notice type.
	 * @return void
	 */
	private function set_notice( $message, $type = 'success' ) {
		set_transient(
			self::NOTICE_OPTION . get_current_user_id(),
			array(
				'message' => $message,
				'type'    => $type,
			),
			60
		);
	}

	/**
	 * @param array<string,array<string,mixed>> $callbacks Registered callbacks.
	 * @param array<string,array<string,mixed>> $dynamic_views Registered dynamic providers.
	 * @return array<string,string>
	 */
	private function get_content_types( array $callbacks, array $dynamic_views ) {
		$types = array(
			'elementor_template' => __( 'Elementor Template', 'tutor-dashboard-modules' ),
			'shortcode'          => __( 'Shortcode', 'tutor-dashboard-modules' ),
			'php_view'           => __( 'PHP View', 'tutor-dashboard-modules' ),
			'woocommerce_endpoint' => __( 'WooCommerce Endpoints', 'tutor-dashboard-modules' ),
		);

		if ( ! empty( $callbacks ) ) {
			$types['custom_callback'] = __( 'Custom Callback', 'tutor-dashboard-modules' );
		}

		if ( ! empty( $dynamic_views ) ) {
			$types['dynamic_data_view'] = __( 'Dynamic Data View', 'tutor-dashboard-modules' );
		}

		return apply_filters( 'tdm/register_module_types', $types );
	}

	/**
	 * @return array<string,string>
	 */
	private function get_woo_endpoints() {
		return array(
			'orders'             => __( 'Orders', 'tutor-dashboard-modules' ),
			'downloads'          => __( 'Downloads', 'tutor-dashboard-modules' ),
			'edit-account'       => __( 'Edit Account', 'tutor-dashboard-modules' ),
			'edit-address'       => __( 'Edit Address', 'tutor-dashboard-modules' ),
			'payment-methods'    => __( 'Payment Methods', 'tutor-dashboard-modules' ),
			'add-payment-method' => __( 'Add Payment Method', 'tutor-dashboard-modules' ),
		);
	}

	/**
	 * @return array<string,mixed>
	 */
	private function get_woo_ui_options() {
		return array(
			'downloads' => array(
				'renderModes' => array(
					'hybrid'      => __( 'Hybrid', 'tutor-dashboard-modules' ),
					'native_tutor'=> __( 'Native Tutor', 'tutor-dashboard-modules' ),
					'custom'      => __( 'Custom', 'tutor-dashboard-modules' ),
				),
				'layouts'     => array(
					'hybrid'       => array(
						'downloads_table' => __( 'Downloads Table', 'tutor-dashboard-modules' ),
					),
					'native_tutor' => array(
						'downloads_native' => __( 'Tutor Styled Downloads', 'tutor-dashboard-modules' ),
					),
					'custom'       => array(
						'downloads_cards' => __( 'Download Cards', 'tutor-dashboard-modules' ),
						'downloads_table' => __( 'Download Table', 'tutor-dashboard-modules' ),
					),
				),
			),
			'orders' => array(
				'renderModes' => array(
					'hybrid'      => __( 'Hybrid', 'tutor-dashboard-modules' ),
					'native_tutor'=> __( 'Native Tutor', 'tutor-dashboard-modules' ),
					'custom'      => __( 'Custom', 'tutor-dashboard-modules' ),
				),
				'layouts'     => array(
					'hybrid'       => array(
						'orders_table' => __( 'Orders Table', 'tutor-dashboard-modules' ),
					),
					'native_tutor' => array(
						'orders_native' => __( 'Tutor Styled Orders', 'tutor-dashboard-modules' ),
					),
					'custom'       => array(
						'orders_table'   => __( 'Orders Table', 'tutor-dashboard-modules' ),
						'orders_compact' => __( 'Compact Orders', 'tutor-dashboard-modules' ),
					),
				),
			),
			'edit-account' => array(
				'renderModes' => array(
					'hybrid' => __( 'Hybrid', 'tutor-dashboard-modules' ),
				),
				'layouts' => array(
					'hybrid' => array(
						'edit_account_form' => __( 'Account Form', 'tutor-dashboard-modules' ),
					),
				),
			),
			'edit-address' => array(
				'renderModes' => array(
					'hybrid' => __( 'Hybrid', 'tutor-dashboard-modules' ),
				),
				'layouts' => array(
					'hybrid' => array(
						'address_book' => __( 'Address Book', 'tutor-dashboard-modules' ),
					),
				),
			),
			'payment-methods' => array(
				'renderModes' => array(
					'hybrid' => __( 'Hybrid', 'tutor-dashboard-modules' ),
				),
				'layouts' => array(
					'hybrid' => array(
						'payment_methods' => __( 'Payment Methods', 'tutor-dashboard-modules' ),
					),
				),
			),
			'add-payment-method' => array(
				'renderModes' => array(
					'hybrid' => __( 'Hybrid', 'tutor-dashboard-modules' ),
				),
				'layouts' => array(
					'hybrid' => array(
						'add_payment_method' => __( 'Add Payment Method Form', 'tutor-dashboard-modules' ),
					),
				),
			),
		);
	}
}
