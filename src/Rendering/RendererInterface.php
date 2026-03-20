<?php

namespace TDM\Rendering;

use TDM\Domain\ModuleDefinition;

interface RendererInterface {
	/**
	 * @param ModuleDefinition $module Module definition.
	 * @param RenderContext    $context Render context.
	 * @return RenderResult
	 */
	public function render( ModuleDefinition $module, RenderContext $context );
}
