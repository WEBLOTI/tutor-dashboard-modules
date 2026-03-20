<?php

namespace TDM\Rendering;

use TDM\Domain\ModuleDefinition;

class RenderContext {
	/** @var ModuleDefinition */
	public $module;
	/** @var int */
	public $user_id;
	/** @var array<string,mixed> */
	public $query_vars;
	/** @var array<string,bool> */
	public $dependencies;

	/**
	 * @param ModuleDefinition   $module Module definition.
	 * @param int                $user_id User ID.
	 * @param array<string,mixed> $query_vars Query vars.
	 * @param array<string,bool> $dependencies Dependency statuses.
	 */
	public function __construct( ModuleDefinition $module, $user_id, array $query_vars, array $dependencies ) {
		$this->module       = $module;
		$this->user_id      = (int) $user_id;
		$this->query_vars   = $query_vars;
		$this->dependencies = $dependencies;
	}
}
