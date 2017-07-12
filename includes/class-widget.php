<?php
/**
 * DoubleClick for WordPress Widget.
 *
 * @since   0.2.1
 * @package DoubleClick_For_WordPress
 */

/**
 * DoubleClick for WordPress Widget class.
 *
 * @since 0.2.1
 */
class DCWP_Widget extends WP_Widget {

	/**
	 * Unique identifier for this widget.
	 *
	 * Will also serve as the widget class.
	 *
	 * @var    string
	 * @since  0.2.1
	 */
	protected $widget_slug = 'doubleclick-for-wordpress-widget';


	/**
	 * Widget name displayed in Widgets dashboard.
	 * Set in __construct since __() shouldn't take a variable.
	 *
	 * @var    string
	 * @since  0.2.1
	 */
	protected $widget_name = '';


	/**
	 * Default widget title displayed in Widgets dashboard.
	 * Set in __construct since __() shouldn't take a variable.
	 *
	 * @var string
	 * @since  0.2.1
	 */
	protected $default_widget_title = '';

	/**
	 * Shortcode name for this widget
	 *
	 * @var    string
	 * @since  0.2.1
	 */
	protected static $shortcode = 'doubleclick-for-wordpress-widget';

	/**
	 * Construct widget class.
	 *
	 * @since  0.2.1
	 */
	public function __construct() {

		$this->widget_name = esc_html__( 'DoubleClick Ad', 'doubleclick-for-wordpress' );
		$this->default_widget_title = esc_html__( 'DoubleClick Ad', 'doubleclick-for-wordpress' );

		parent::__construct(
			$this->widget_slug,
			$this->widget_name,
			array(
				'classname'   => $this->widget_slug,
				'description' => esc_html__( 'Serve ads from DFP.', 'doubleclick-for-wordpress' ),
			)
		);

		// Clear cache on save.
		add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );

		// Add a shortcode for our widget.
		add_shortcode( self::$shortcode, array( __CLASS__, 'get_widget' ) );
	}

	/**
	 * Delete this widget's cache.
	 *
	 * Note: Could also delete any transients
	 * delete_transient( 'some-transient-generated-by-this-widget' );
	 *
	 * @since  0.2.1
	 */
	public function flush_widget_cache() {
		wp_cache_delete( $this->widget_slug, 'widget' );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @since  0.2.1
	 *
	 * @param  array $args     The widget arguments set up when a sidebar is registered.
	 * @param  array $instance The widget settings as set by user.
	 */
	public function widget( $args, $instance ) {

		// Set widget attributes.
		$atts = array(
			'before_widget' => $args['before_widget'],
			'after_widget'  => $args['after_widget'],
			'before_title'  => $args['before_title'],
			'after_title'   => $args['after_title'],
			'title'         => $instance['title'],
			'text'          => $instance['text'],
		);

		// Display the widget.
		echo self::get_widget( $atts ); // WPCS XSS OK.
	}

	/**
	 * Return the widget/shortcode output
	 *
	 * @since  0.2.1
	 *
	 * @param  array $atts Array of widget/shortcode attributes/args.
	 * @return string      Widget output
	 */
	public static function get_widget( $atts ) {

		global $doubleclick;

		$defaults = array(
			'before_widget' => '',
			'after_widget'  => '',
			'before_title'  => '',
			'after_title'   => '',
			'title'         => '',
			'text'          => '',
		);

		// Parse defaults and create a shortcode.
		$atts = shortcode_atts( $defaults, (array) $atts, self::$shortcode );

		// prepare identifier parameter.
		$identifier = ! empty( $instance['identifier'] ) ? $instance['identifier'] : 'ident';

		// prepare size parameter.
		$sizes = $instance['sizes'];
		if ( ! empty( $sizes ) ) {
			foreach ( $sizes as $breakpoint => $size ) {
				if ( empty( $sizes[ $breakpoint ] ) ) {
					unset( $sizes[ $breakpoint ] );
				}
			}
		} else {
			printf(
				'<!-- %1$s -->',
				esc_html__( 'This DoubleClick for WordPress widget is not appearing because the widget has no sizes set for its breakpoints.', 'dfw' )
			);
			return;
		}

		// prepare dfw_args parameter.
		$dfw_args = null;
		if ( $instance['lazyLoad'] ) {
			$dfw_args = array( 'lazyLoad' => true );
		}

		// Start an output buffer.
		ob_start();

		// Start widget markup.
		echo wp_kses_post( $atts['before_widget'] );

		// Maybe display widget title.
		echo ( $atts['title'] ) ? wp_kses_post( $atts['before_title'] ) . esc_html( $atts['title'] ) . wp_kses_post( $atts['after_title'] ) : '' ;

		// and finally, place the ad.
		$doubleclick->place_ad( $identifier, $sizes, $dfw_args );

		// End the widget markup.
		echo wp_kses_post( $atts['after_widget'] );

		// Return the output buffer.
		return ob_get_clean();

	}

	/**
	 * Update form values as they are saved.
	 *
	 * @since  0.2.1
	 *
	 * @param  array $new_instance New settings for this instance as input by the user.
	 * @param  array $old_instance Old settings for this instance.
	 * @return array               Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {

		// Previously saved values.
		$instance = $old_instance;

		// @TODO add sanitization
		$instance['identifier'] = ( ! empty( $new_instance['identifier'] ) ) ? strip_tags( $new_instance['identifier'] ) : '';
		$instance['lazyLoad'] = ( ! empty( $new_instance['lazyLoad'] ) ) ? $new_instance['lazyLoad'] : 0 ;
		$instance['breakpoints'] = $new_instance['breakpoints'];
		$instance['sizes'] = str_replace( ' ', '', $new_instance['sizes'] );
		$instance['size'] = str_replace( ' ', '', $new_instance['size'] );

		// Flush cache.
		$this->flush_widget_cache();

		return $instance;
	}

	/**
	 * Back-end widget form with defaults.
	 *
	 * @since  0.2.1
	 *
	 * @param  array $instance Current settings.
	 */
	public function form( $instance ) {
		global $doubleclick;

	 var_dump( $doubleclick );

		// Set defaults.
		$defaults = array(
			'title' => $this->default_widget_title,
			'text'  => '',
		);

		// Parse args.
		$instance = wp_parse_args( (array) $instance, $defaults );

		$identifier = ! empty( $instance['identifier'] ) ? $instance['identifier'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'identifier' ) ); ?>"><?php esc_html_e( 'Identifier:' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'identifier' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'identifier' ) ); ?>" type="text" value="<?php echo esc_attr( $identifier ); ?>">
		</p>

		<?php if ( count( $doubleclick->breakpoints ) > 0 ) : $i = 0; ?>

			<p><strong>Size for breakpoints:</strong></p>

			<?php foreach ( $doubleclick->breakpoints as $breakpoint ) : ?>
				<p>
					<label><?php echo esc_html( $breakpoint->identifier ); ?> <em>(<?php echo esc_html( $breakpoint->min_width ); ?>px+)</em></label><br/>
					<input
						class="widefat"
						type="text"
						name="<?php echo esc_attr( $this->get_field_name( 'sizes' ) ); ?>[<?php echo esc_attr( $breakpoint->identifier ); ?>]"
						value="<?php echo esc_attr( $instance['sizes'][ $breakpoint->identifier ] ); ?>"
						>
				</p>

			<?php endforeach; ?>

			<p><hr/></p>

		<?php else : ?>

			<p>
			<label><strong>Size: </strong></label><br/>
				<input
					class="widefat"
					type="text"
					name="<?php echo esc_attr( $this->get_field_name( 'size' ) ); ?>"
					value="<?php echo esc_attr( $instance['size'] ); ?>"
					>
			</p>

		<?php endif; ?>

		<p><strong>Lazy Load?</strong></p>
		<p>
			<input
				class="checkbox"
				type="checkbox"
				name="<?php echo esc_attr( $this->get_field_name( 'lazyLoad' ) ); ?>"
				value="1"
				<?php if ( $instance['lazyLoad'] ) { echo 'checked';} ?>
				><label>Only load ad once it comes into view on screen.</label><br/>
		</p>
		<hr/><br>
		<?php
	}
}

/**
 * Register widget with WordPress.
 */
function doubleclick_for_wordpress_register_undefined() {
	register_widget( 'DCWP_Widget' );
}
add_action( 'widgets_init', 'doubleclick_for_wordpress_register_undefined' );
