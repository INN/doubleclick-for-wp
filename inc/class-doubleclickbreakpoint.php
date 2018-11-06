<?php
/**
 * An object-oriented approach to managing breakpoints
 *
 * @since 0.1
 */
class DoubleClickBreakpoint {

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

	public function __construct( $identifier, $args = null ) {
		// specify all the options because there has been come confusion, historically
		if ( isset( $args['min_width'] ) ) {
			$this->min_width = $args['min_width'];
		} else if ( isset( $args['minWidth'] ) ) {
			$this->min_width = $args['minWidth'];
		} else if ( isset( $args['min-width'] ) ) {
			$this->max_width = $args['min-width'];
		}

		if ( isset( $args['max_width'] ) ) {
			$this->max_width = $args['max_width'];
		} else if ( isset( $args['maxWidth'] ) ) {
			$this->max_width = $args['maxWidth'];
		} else if ( isset( $args['max-width'] ) ) {
			$this->max_width = $args['max-width'];
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
		echo esc_js( $this->get_js_logic() );
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

