<?php

namespace TDM\Security;

use TDM\Domain\ModuleDefinition;
use TDM\Support\DependencyManager;

class PermissionsManager {
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
	 * @param ModuleDefinition $module Module definition.
	 * @return bool
	 */
	public function can_access( ModuleDefinition $module ) {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( ! $this->matches_audience( $module ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @param ModuleDefinition $module Module definition.
	 * @return bool
	 */
	public function is_visible( ModuleDefinition $module ) {
		$is_visible = $this->can_access( $module );

		return (bool) apply_filters( 'tdm/module_is_visible', $is_visible, $module, $this );
	}

	/**
	 * @param ModuleDefinition $module Module definition.
	 * @return bool
	 */
	private function matches_audience( ModuleDefinition $module ) {
		if ( 'logged_in_any' === $module->audience_type ) {
			return true;
		}

		if ( 'students_only' === $module->audience_type ) {
			if ( function_exists( 'tutor_utils' ) && method_exists( tutor_utils(), 'is_instructor' ) ) {
				return ! tutor_utils()->is_instructor();
			}

			return true;
		}

		if ( 'instructors_only' === $module->audience_type ) {
			if ( function_exists( 'tutor_utils' ) && method_exists( tutor_utils(), 'is_instructor' ) ) {
				return (bool) tutor_utils()->is_instructor();
			}

			return false;
		}

		return true;
	}
}
