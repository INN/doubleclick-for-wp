<?php
/**
 * DoubleClick for WordPress Breakpoint.
 *
 * @since   0.2.1
 * @package DoubleClick_For_WordPress
 */

/**
 * DoubleClick for WordPress Breakpoint class.
 *
 * @since   0.2.1
 * @package DoubleClick_For_WordPress
 */
class DCWP_Breakpoint {
	/**
	 * Slug of the breakpoint
	 *
	 * @var string
	 */
	public $identifier = '';

	/**
	 * Minimum width for the breakpoint
	 *
	 * @var integer
	 */
	public $min_width;

	/**
	 * Maximum width for the breakpoint
	 *
	 * @var integer
	 */
	public $max_width;

	/**
	 * Was this breakpoint added by a theme or
	 * through an option?
	 *
	 * @var boolean
	 */
	public $option;

	/**
	 * @TODO need a docbloc
	 *
	 * @param $identifier
	 * @param $args
	 */
	public function __construct( $identifier, $args = null ) {
		if ( isset( $args['min_width'] ) ) {
			$this->min_width = $args['min_width'];
		}

		if ( isset( $args['max_width'] ) ) {
			$this->max_width = $args['max_width'];
		}

		if ( isset( $args['_option'] ) && $args['_option'] ) {
			$this->option = true;
		}

		$this->identifier = $identifier;
	}

	/**
	 * Prints a javascript boolean statement for this breakpoint
	 */
	public function js_logic() {
		echo wp_json_encode( $this->get_js_logic() );
	}

	/**
	 * Returns a string with the boolean logic for the breakpoint.
	 *
	 * @return String boolean logic for breakpoint.
	 */
	public function get_js_logic() {
		return "($this->min_width <= document.documentElement.clientWidth && document.documentElement.clientWidth < $this->max_width)";
	}
}
