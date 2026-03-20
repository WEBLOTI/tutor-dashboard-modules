<?php

namespace TDM\Rendering;

class RenderResult {
	/** @var string */
	public $status;
	/** @var string */
	public $html;
	/** @var string */
	public $message;
	/** @var array<int,string> */
	public $notices;

	/**
	 * @param string             $status Result status.
	 * @param string             $html Rendered HTML.
	 * @param string             $message Status message.
	 * @param array<int,string>  $notices Notices.
	 */
	public function __construct( $status, $html = '', $message = '', array $notices = array() ) {
		$this->status  = $status;
		$this->html    = $html;
		$this->message = $message;
		$this->notices = $notices;
	}

	/**
	 * @param string            $html HTML output.
	 * @param array<int,string> $notices Notices.
	 * @return self
	 */
	public static function success( $html, array $notices = array() ) {
		return new self( 'success', $html, '', $notices );
	}

	/**
	 * @param string            $message Message.
	 * @param array<int,string> $notices Notices.
	 * @return self
	 */
	public static function fallback( $message, array $notices = array() ) {
		return new self( 'fallback', '', $message, $notices );
	}

	/**
	 * @param string $message Message.
	 * @return self
	 */
	public static function empty_state( $message ) {
		return new self( 'empty', '', $message );
	}

	/**
	 * @param string $message Message.
	 * @return self
	 */
	public static function forbidden( $message ) {
		return new self( 'forbidden', '', $message );
	}
}
