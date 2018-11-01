<?php
/**
 * Functions related to the DFW plugin's blocks.
 *
 * @link https://github.com/INN/doubleclick-for-wp/issues/70
 */

 /**
  * Register all block assets so they can be enqueued through Gutenberg in the corresponding context
  *
  * @link https://wordpress.org/gutenberg/handbook/blocks/writing-your-first-block-type/#enqueuing-block-scripts
  */
function dfw_block_init() {
	if( ! function_exists( 'register_block_type' ) ) {
		// Gutenberg is not active.
		return false;
	}

	// double dirname to get the plugin dir because this file is in `/inc/`.
	$dir = dirname( dirname( __FILE__ ) );

	$block_js = 'js/block.js';
	wp_register_script(
		'dfw-block-editor',
		plugins_url( $block_js, dirname( __FILE__ ) ),
		array(
			'wp-blocks',
			'wp-i18n',
			'wp-element',
			'wp-components',
			'wp-editor',
		),
		filemtime( "$dir/$block_js" )
	);

	global $doubleclick;
	if ( is_object( $doubleclick ) && is_array( $doubleclick->breakpoints ) ) {
		$dfw_options = array(
			'breakpoints' => $doubleclick->breakpoints,
		);
	} else {
		$dfw_options = array(
			'breakpoints' => array(),
		);
	}

	wp_localize_script(
		'dfw-block-editor',
		'dfw',
		$dfw_options
	);

	$editor_css = 'css/editor.css';
	wp_register_style(
		'dfw-block-editor',
		plugins_url( $editor_css, dirname( __FILE__ ) ),
		array(
		),
		filemtime( "$dir/$block_js" )
	);

	register_block_type( 'doubleclick-for-wp/dfw-ad-unit', array(
		'attributes' => array(
			'identifier' => array(
				'type' => 'string',
			),
			'lazyLoad' => array(
				'type' => 'boolean',
			),
			'breakpoints' => array(
				'type' => 'array',
			),
			'sizes' => array(
				'type' => 'array',
			),
			'size' => array(
				'type' => 'string',
			),
		),
		'editor_script' => 'dfw-block-editor',
		'editor_style' => 'dfw-block-editor',
		'style' => 'dfw-block',
		'render_callback' => 'dfw_block_render_callback',
		'description' => __( 'Place a Google Ad Manager ad unit', 'dfw' ),
		'keywords' => array(
			'ad',
			'google',
		),
	) );
}
add_action( 'init', 'dfw_block_init' );

/**
 * Render callback for block wraps DoubleClick_Widget->widget
 *
 * Things this must do:
 * - provide sidebar $args
 *     - provide an ID for the widget as widget_id
 *         - output this as part of $args['before_widget']
 *         - output this as $args['widget_id']
 *     - provide classes
 *
 * @param Array $args The widget/thing arguments
 */
function dfw_block_render_callback( $instance=array(), $content='', $tag='' ) {
	/*
	 * Widget needs two arguments: args, instance
	 *
	 * $args are display arguments, which come from the sidebar
	 * $instance is the settings for this specific widget
	 */
	$args = array(
		'before_widget' => '<aside class="widget widget_doubleclick_widget">',
		'after_widget' => '</aside>',
		'name' => 'In Post',
		'widget_id' => '',
		'widget_name' => 'DoubleClick Ad',
	);

	/*
	 * Type juggling
	 */
	if ( isset( $instance['lazyLoad'] ) && true === $instance['lazyLoad'] ) {
		$instance['lazyLoad'] = '1';
	}

	// create the widget and capture its output.
	ob_start();
	$widget = new DoubleClick_Widget();
	$widget->widget( $args, $instance );
	$return = ob_get_clean();
	return $return;
}
