<?php

namespace TDM\Support;

class DependencyManager {
	/**
	 * @return array<string,bool>
	 */
	public function get_statuses() {
		return array(
			'tutor'      => $this->is_tutor_active(),
			'woocommerce'=> $this->is_woocommerce_active(),
			'elementor'  => $this->is_elementor_active(),
			'jet_engine' => $this->is_jet_engine_active(),
		);
	}

	/**
	 * @return bool
	 */
	public function is_tutor_active() {
		return function_exists( 'tutor_utils' ) && class_exists( '\TUTOR\Tutor' );
	}

	/**
	 * @return bool
	 */
	public function is_woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * @return bool
	 */
	public function is_elementor_active() {
		return did_action( 'elementor/loaded' ) || class_exists( '\Elementor\Plugin' );
	}

	/**
	 * @return bool
	 */
	public function is_jet_engine_active() {
		return class_exists( 'Jet_Engine' ) || defined( 'JET_ENGINE_VERSION' );
	}

	/**
	 * @param string $type Module type.
	 * @return array<string,bool>
	 */
	public function get_requirements_for_type( $type ) {
		$requirements = array(
			'tutor' => $this->is_tutor_active(),
		);

		if ( 'elementor_template' === $type ) {
			$requirements['elementor'] = $this->is_elementor_active();
		}

		if ( 'woocommerce_endpoint' === $type ) {
			$requirements['woocommerce'] = $this->is_woocommerce_active();
		}

		return apply_filters( 'tdm/module_dependencies_status', $requirements, $type, $this );
	}

	/**
	 * @param string $type Module type.
	 * @return bool
	 */
	public function are_requirements_met( $type ) {
		foreach ( $this->get_requirements_for_type( $type ) as $status ) {
			if ( ! $status ) {
				return false;
			}
		}

		return true;
	}
}
