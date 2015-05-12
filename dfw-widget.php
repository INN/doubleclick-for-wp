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
			__( 'DoubleClick Ad', 'dfw' ), // Name
			array( 'description' => __( 'Serve ads from DFP.', 'dfw' ), ) // Args
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

		// prepare identifier parameter.
    	$identifier = ! empty( $instance['identifier'] ) ? $instance['identifier'] : 'ident';
    	
    	// prepare size parameter.
    	$sizes = $instance['sizes'];
    	foreach($sizes as $b=>$s) {
    		if( empty($sizes[$b]) ) {
    			unset($sizes[$b]);
    		} 
    	}

    	// prepare args parameter.
    	$args = null;
    	if($instance['lazyLoad']) {
    		$args = array( 'lazyLoad' => true );
    	}

    	// print (optional) title.
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}

		// and finally, place the ad.
		$DoubleClick->place_ad($identifier,$sizes,$args);

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

		global $DoubleClick;

		$identifier = ! empty( $instance['identifier'] ) ? $instance['identifier'] : "";
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'identifier' ); ?>"><?php _e( 'Identifier:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'identifier' ); ?>" name="<?php echo $this->get_field_name( 'identifier' ); ?>" type="text" value="<?php echo esc_attr( $identifier ); ?>">
		</p>	

		<?php if( sizeof($DoubleClick->breakpoints) > 0 ) : $i = 0; ?>

			<p><strong>Size for breakpoints:</strong></p>

			<?php foreach($DoubleClick->breakpoints as $b) : ?>
				<p>
					<label><?php echo $b->identifier; ?> <em>(<?php echo $b->minWidth; ?>px+)</em></label><br/>
					<input 
						class="widefat" 
						type="text" 
						name="<?php echo $this->get_field_name( 'sizes' ); ?>[<?php echo $b->identifier ?>]" 
						value="<?php echo $instance['sizes'][$b->identifier]; ?>" 
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
					name="<?php echo $this->get_field_name( 'size' ); ?>" 
					value="<?php echo $instance['size']; ?>" 
					>
			</p>

		<?php endif; ?>

		<p><strong>Lazy Load?</strong></p>
		<p>
			<input 
				class="checkbox" 
				type="checkbox" 
				name="<?php echo $this->get_field_name( 'lazyLoad' ); ?>"
				value="1"
				<?php if( $instance['lazyLoad'] ) echo "checked"; ?>
				><label>Only load ad once it comes into view on screen.</label><br/>
		</p>
		<hr/><br>



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
		$instance['lazyLoad'] = ( ! empty( $new_instance['lazyLoad'] ) ) ? $new_instance['lazyLoad'] : 0 ;
		$instance['breakpoints'] = $new_instance['breakpoints'];
		$instance['sizes'] = $new_instance['sizes'];
		$instance['size'] = $new_instance['size'];

		return $instance;
	}

}

function dfw_register_widget() {

	register_widget( 'DoubleClick_Widget' );
}

add_action( 'widgets_init', 'dfw_register_widget');

