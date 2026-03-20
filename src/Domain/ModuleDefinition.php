<?php

namespace TDM\Domain;

class ModuleDefinition {
	/** @var int */
	public $id;
	/** @var string */
	public $title;
	/** @var string */
	public $slug;
	/** @var string */
	public $icon;
	/** @var int */
	public $menu_order;
	/** @var bool */
	public $active;
	/** @var string */
	public $audience_type;
	/** @var string */
	public $content_type;
	/** @var string */
	public $fallback_mode;
	/** @var string */
	public $fallback_message;
	/** @var string */
	public $wrapper_variant;
	/** @var int */
	public $cache_ttl;
	/** @var bool */
	public $show_title;
	/** @var string */
	public $notes;
	/** @var array<string,mixed> */
	public $config;

	/**
	 * @param array<string,mixed> $data Hydrated module data.
	 */
	public function __construct( array $data ) {
		$this->id                = (int) $data['id'];
		$this->title             = (string) $data['title'];
		$this->slug              = (string) $data['slug'];
		$this->icon              = (string) $data['icon'];
		$this->menu_order        = (int) $data['menu_order'];
		$this->active            = (bool) $data['active'];
		$this->audience_type     = (string) $data['audience_type'];
		$this->content_type      = (string) $data['content_type'];
		$this->fallback_mode     = (string) $data['fallback_mode'];
		$this->fallback_message  = (string) $data['fallback_message'];
		$this->wrapper_variant   = (string) $data['wrapper_variant'];
		$this->cache_ttl         = (int) $data['cache_ttl'];
		$this->show_title        = (bool) $data['show_title'];
		$this->notes             = (string) $data['notes'];
		$this->config            = is_array( $data['config'] ) ? $data['config'] : array();
	}

	/**
	 * @param string $key Config key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public function config( $key, $default = null ) {
		return array_key_exists( $key, $this->config ) ? $this->config[ $key ] : $default;
	}
}
