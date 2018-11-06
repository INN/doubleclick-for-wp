<?php
/**
 * The Google Ad Manager Ad widget, and related functions
 */

/**
 * Adds DoubleClick_Widget widget.
 */
class DoubleClick_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'doubleclick_widget', // Base ID
			__( 'Google Ad Manager Ad', 'dfw' ), // Name
			array( 'description' => __( 'Serve ads from Google Ad Manager.', 'dfw' ) ) // Args.
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $doubleclick;

		// prepare identifier parameter.
		$identifier = ! empty( $instance['identifier'] ) ? $instance['identifier'] : 'ident';

		// prepare size parameter.
		if ( isset( $instance['sizes'] ) && ! empty( $instance['sizes'] ) ) {
			// check to see if it's JSON, which is saved by the Gutenberg Block
			if ( is_string( $instance['sizes'] ) ) {
				$temporary = json_decode( $instance['sizes'] );
				if ( json_last_error() === JSON_ERROR_NONE ) {
					$instance['sizes'] = (array) $temporary;
				}
			}

			foreach ( $instance['sizes'] as $breakpoint => $size ) {
				if ( isset( $instance['sizes'][ $breakpoint ] ) && empty( $instance['sizes'][ $breakpoint ] ) ) {
					unset( $instance['sizes'][ $breakpoint ] );
				}
			}
		} else {
			echo sprintf(
				'<!-- %1$s %2$s-->',
				esc_html__( 'This Google Ad Manager Ad widget is not appearing because the widget has no sizes set for its breakpoints.', 'dfw' ),
				var_export( $instance, true )
			);
			return;
		}

		// bugfix: replace $args with $dfw_args to prevent widget interference
		// prepare dfw_args parameter.
		$dfw_args = null;
		if ( $instance['lazyLoad'] ) {
			$dfw_args = array( 'lazyLoad' => true );
		}

		// begin actual widget output.
		echo wp_kses_post( $args['before_widget'] );

		// print (optional) title.
		if ( ! empty( $instance['title'] ) ) {
			echo wp_kses_post( $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'] );
		}

		// and finally, place the ad.
		$doubleclick->place_ad( $identifier, $instance['sizes'], $dfw_args );

		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		global $doubleclick;

		$identifier = ! empty( $instance['identifier'] ) ? $instance['identifier'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'identifier' ) ); ?>"><?php esc_html_e( 'Identifier/Ad code:' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'identifier' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'identifier' ) ); ?>" type="text" value="<?php echo esc_attr( $identifier ); ?>">
		</p>

		<?php
			if ( ! empty( $doubleclick->breakpoints ) ) {
				$i = 0;
				?>

				<p>
					<strong>Configure ad unit sizes to be displayed for each breakpoint</strong>
					<a href="https://github.com/INN/doubleclick-for-wp/blob/master/docs/readme.md#1-via-reusable-widget" target="_blank">
						<?php echo wp_kses_post( __( '(Help?)', 'dfw' ) ); ?>
					</a>
				</p>

				<?php
					foreach ( $doubleclick->breakpoints as $breakpoint ) {
						$sizes = '';

						if ( isset( $instance['sizes'] ) )  {
							if ( isset( $instance['sizes'][ $breakpoint->identifier ] ) ) {
								$sizes = $instance['sizes'][ $breakpoint->identifier ];
							}
						}

						echo '<p>';
						printf(
							'<label for="%3$s">%1$s <em>(%2$spx+)</em></label><br/>',
							esc_html( $breakpoint->identifier ),
							esc_html( $breakpoint->min_width ),
							esc_attr( $this->get_field_name( 'sizes' ) ) . '[' . esc_attr( $breakpoint->identifier ) . ']'
						);
						printf(
							'<input lass="widefat" type="text" id="%1$s" name="%1$s" value="%2$s">',
							esc_attr( $this->get_field_name( 'sizes' ) ) . '[' . esc_attr( $breakpoint->identifier ) . ']',
							$sizes
						);
						echo '</p>';
					}
				?>

				<hr/>

				<?php
			} else {

				echo '<p>';
				printf(
					'<label for="%2$s"><strong>%1$s</strong></label><br/>',
					wp_kses_post( __( 'Size:', 'dfw' ) ),
					esc_attr( $this->get_field_name( 'size' ) )
				);
				printf(
					'<input class="widefat" type="text" id="%1$s" name="%1$s" value="%2$s" >',
					esc_attr( $this->get_field_name( 'size' ) ),
					( isset( $instance['size'] ) ) ? esc_attr( $instance['size'] ) : ''
				);
				echo '</p>';

			}

		// Lazy-load toggle
		printf(
			'<p><strong></strong></p>',
			wp_kses_post( __( 'Lazy Load?', 'dfw' ) )
		);
		echo '<p>';
		printf(
			'<input class="checkbox" type="checkbox" id="%1$s" name="%1$s" value="1" %2$s ><label for="%1$s">%3$s</label><br/>',
			esc_attr( $this->get_field_name( 'lazyLoad' ) ),
			( isset( $instance['lazyLoad'] ) && $instance['lazyLoad'] ) ? 'checked' : '',
			wp_kses_post( __( 'Only load ad once it comes into view on screen.', 'dfw' ) )
		);
		echo '</p>';
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['identifier'] = ( ! empty( $new_instance['identifier'] ) ) ? strip_tags( $new_instance['identifier'] ) : '';
		$instance['lazyLoad'] = ( ! empty( $new_instance['lazyLoad'] ) ) ? $new_instance['lazyLoad'] : 0;
		$instance['breakpoints'] = ( isset( $new_instance['breakpoints'] ) ) ? $new_instance['breakpoints'] : '';
		$instance['sizes'] = ( isset( $new_instance['sizes'] ) ) ? $new_instance['sizes'] : '';
		$instance['size'] = ( isset( $new_instance['size'] ) ) ? $new_instance['size'] : '';
		return $instance;
	}

}

/**
 * Register the widget
 */
function dfw_register_widget() {
	register_widget( 'DoubleClick_Widget' );
}

add_action( 'widgets_init', 'dfw_register_widget' );
