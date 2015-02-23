<?php

// Get our global varaible.
global $DoubleClick;


/**
 * Adds DoubleClick_Widget widget.
 */
class DoubleClick_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'doubleclick_widget', // Base ID
			__( 'Double Click Ad', 'text_domain' ), // Name
			array( 'description' => __( 'Serve ads from DFP.', 'text_domain' ), ) // Args
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

		global $DoubleClick;
		
		echo $args['before_widget'];

		$instance['hidden_desktop'];
  		$instance['hidden_tablet'];
    	$instance['hidden_phone'];

    	$width = ! empty( $instance['width'] ) ? $instance['width'] : '300';
		$height = ! empty( $instance['height'] ) ? $instance['height'] : '250';
    	$identifier = ! empty( $instance['identifier'] ) ? $instance['identifier'] : 'ident';
    	$size = $width . "x" . $height;
    	$breakpoints = array();

    	if(!$instance['hidden_desktop']) {
    		$breakpoints[] = 'desktop';
    	} 
    	if(!$instance['hidden_tablet']) {
    		$breakpoints[] = 'tablet';
    	}
    	if(!$instance['hidden_phone']) {
    		$breakpoints[] = 'phone';
    	}

    	$DoubleClick->place_ad($identifier,$size,$breakpoints);

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$identifier = ! empty( $instance['identifier'] ) ? $instance['identifier'] : "";
		$width = ! empty( $instance['width'] ) ? $instance['width'] : '300';
		$height = ! empty( $instance['height'] ) ? $instance['height'] : '250';

		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'identifier' ); ?>"><?php _e( 'Identifier:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'identifier' ); ?>" name="<?php echo $this->get_field_name( 'identifier' ); ?>" type="text" value="<?php echo esc_attr( $identifier ); ?>">
		</p>	

		<p>
		<label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Width:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" type="text" value="<?php echo esc_attr( $width ); ?>">
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'height' ); ?>"><?php _e( 'Height:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" type="text" value="<?php echo esc_attr( $height ); ?>">
		</p>
		<?php 
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
		$instance['width'] = ( ! empty( $new_instance['width'] ) ) ? strip_tags( $new_instance['width'] ) : '300';
		$instance['height'] = ( ! empty( $new_instance['height'] ) ) ? strip_tags( $new_instance['height'] ) : '250';

		return $instance;
	}

}