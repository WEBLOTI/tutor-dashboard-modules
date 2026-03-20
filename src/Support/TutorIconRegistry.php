<?php

namespace TDM\Support;

class TutorIconRegistry {
	/**
	 * @var DependencyManager
	 */
	private $dependencies;

	/**
	 * @param DependencyManager $dependencies Dependency service.
	 */
	public function __construct( DependencyManager $dependencies ) {
		$this->dependencies = $dependencies;
	}

	/**
	 * @return array<int,string>
	 */
	public function get_icons() {
		static $icons = null;

		if ( null !== $icons ) {
			return $icons;
		}

		$icons = array();
		$paths = array(
			WP_PLUGIN_DIR . '/tutor/assets/css/tutor-icon.min.css',
			WP_PLUGIN_DIR . '/tutor/assets/css/tutor-front.min.css',
		);

		foreach ( $paths as $path ) {
			if ( ! file_exists( $path ) ) {
				continue;
			}

			$contents = file_get_contents( $path );
			if ( false === $contents ) {
				continue;
			}

			if ( preg_match_all( '/tutor-icon-[a-z0-9-]+/i', $contents, $matches ) ) {
				$icons = array_merge( $icons, $matches[0] );
			}
		}

		$icons = array_values( array_unique( array_filter( array_map( 'sanitize_html_class', $icons ) ) ) );
		sort( $icons );

		if ( empty( $icons ) ) {
			$icons = array(
				'tutor-icon-bookmark-bold',
				'tutor-icon-book-open',
				'tutor-icon-calender-line',
				'tutor-icon-cart-bold',
				'tutor-icon-chart-pie',
				'tutor-icon-folder',
				'tutor-icon-gear',
				'tutor-icon-question',
				'tutor-icon-rocket',
				'tutor-icon-user-bold',
			);
		}

		return $icons;
	}
}
