<?php
/**
 * DoubleClick for WordPress Ad Slot.
 *
 * @since   0.2.1
 * @package DoubleClick_For_WordPress
 */

class DCWP_AdSlot {
	/**
	 * DFP ad code.
	 *
	 * @var String.
	 */
	public $identifer;

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
	 * Constructor.
	 *
	 * @since  0.2.1
	 *
	 * @param string $identifier the identifier for the ad slot
	 * @param string $size the size of the placement
	 */
	public function __construct( $identifier, $size ) {
		global $doubleclick;
		/**
		 * doubleclick escapes '/' with '//' for some odd reason.
		 * currently we don't try to fix this, but could with this line:
		 * $this->identifier = str_replace('/','//',$identifier);
		 */
		$this->identifier = $identifer;
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

			// Remove any extra spaces in the list of sizes.
			// (needs to be just a comma-separated list of values)
			$size = str_replace( ' ', '', $size );

			// eg. 300x250,336x300
			$size_strings = explode( ',', $size );
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
