<?php
/**
 * An object-oriented approach to managing ad slots
 *
 * @since 0.1
 */
class DoubleClickAdSlot {

	/**
	 * Google Ad Manager ad code/identifier.
	 *
	 * @var String.
	 */
	public $identifier;

	/**
	 * Either a string of sizes, or a size mapping.
	 *
	 * @var Array|String
	 */
	public $sizes;

	/**
	 * Each ad gets a unique number to identify it.
	 *
	 * @var int
	 */
	public $id;

	/**
	 * @param String $identifier
	 * @param Mixed $size
	 */
	public function __construct( $identifier, $size ) {

		global $doubleclick;

		// doubleclick escapes '/' with '//' for some odd reason.
		// currently we don't try to fix this, but could with this line:
		// $this->identifier = str_replace('/','//',$identifier);
		$this->identifier = $identifier;
		$this->sizes = $size;
		$this->id = ++ DoubleClick::$count;
	}

	public function breakpoint_identifier() {
		return null;
	}

	/**
	 * If this ad unit has a size mapping.
	 *
	 */
	public function has_mapping() {
		if ( is_string( $this->sizes ) ) {
			return false;
		} else {
			return true;
		}
	}

	public function mapping() {
		global $doubleclick;

		// Return false if there is no mapping
		if ( ! $this->has_mapping() ) {
			return false;
		}

		$mapping = array();

		if ( empty( $this->sizes ) ) {
			return $mapping;
		}

		foreach ( $this->sizes as $breakpoint_identifier => $size ) {
			$breakpoint = $doubleclick->breakpoints[ $breakpoint_identifier ];

			// The minimum browser width/height for this sizemapping.
			$browser_height = 1;
			$browser_width = (int) $breakpoint->min_width;

			$size_strings = explode( ',', $size );	// eg. 300x250,336x300
			$size_array = array();

			foreach ( $size_strings as $size ) {
				if ( ! empty( $size ) ) {
					$arr = explode( 'x', $size );		// eg. 300x250
					$width = (int) $arr[0];
					$height = (int) $arr[1];
					$size_array[] = array( $width, $height );
				}
			}

			$mapping[] = array(
				'browser' => array( $browser_width, $browser_height ),
				'ad_sizes' => $size_array,
			);
		}

		return $mapping;
	}
}

