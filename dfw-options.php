<?php


global $DoubleClick;

/**
 * Register option page.
 *
 * @since v0.1
 */
function dfw_plugin_menu() {
	add_options_page( 
		'DoubleClick for WordPress', 	// $page_title title of the page.
		'DoubleClick for WordPress', 	// $menu_title the text to be used for the menu.
		'manage_options', 				// $capability required capability for display.
		'doubleclick-for-wordpress', 	// $menu_slug unique slug for menu.
		'dfw_option_page_html' 			// $function callback.
		);
}
add_action( 'admin_menu', 'dfw_plugin_menu' );

/**
 * Output the HTML for the option page.
 *
 * @since v0.1
 */
function dfw_option_page_html() {

	// Nice try.
	if ( !current_user_can( 'manage_options' ) )
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );

	echo '<div class="wrap">';
	echo '<h2>DoubleClick for WordPress Options</h2>';
	echo '<form method="post" action="options.php">';
	
	settings_fields( 'doubleclick-for-wordpress' );
	do_settings_sections( 'doubleclick-for-wordpress' );
	submit_button();
	
	echo '</form>';
	echo '</div>'; // div.wrap

}



/**
 * Registers options for the plugin.
 *
 * @since v0.1
 */
function dfw_register_options() {

	// Add a section for network option
	add_settings_section(
		'dfw_network_options',
		'Network Settings',
		'dfw_settings_section_intro',
		'doubleclick-for-wordpress'
	); // ($id, $title, $callback, $page)

		// Add a section for network option
	add_settings_section(
		'dfw_breakpoint_options',
		'Breakpoints',
		'dfw_breakpoints_section_intro',
		'doubleclick-for-wordpress'
	); // ($id, $title, $callback, $page)
	
	// Network Code
	add_settings_field(
		'dfw_network_code',
		'DoubleClick Network Code',
		'dfw_network_code_input',
		'doubleclick-for-wordpress',
		'dfw_network_options'
	); // ($id, $title, $callback, $page, $section, $args)

	// Breakpoints
	add_settings_field(
		'dfw_breakpoints',
		'Breakpoints',
		'dfw_breakpoints_input',
		'doubleclick-for-wordpress',
		'dfw_breakpoint_options'
	); // ($id, $title, $callback, $page, $section, $args)

	register_setting( 'doubleclick-for-wordpress', 'dfw_network_code' );
	register_setting( 'doubleclick-for-wordpress', 'dfw_breakpoints', 'dfw_breakpoints_save' );

}
add_action('admin_init', 'dfw_register_options');

function dfw_settings_section_intro() {
	echo "Enter your Network Code from DFP";
}

function dfw_breakpoints_section_intro() {
	echo "Enter your breakpoints below";
}

function dfw_network_code_input() {

	global $DoubleClick;

	if( isset($DoubleClick->networkCode) )
		echo '<input value="' . $DoubleClick->networkCode . ' (set by theme)" type="text" class="regular-text" disabled/>'; // "The network code is already defined";
	else {
		echo '<input name="dfw_network_code" id="dfw_network_code" type="text" value="' . get_option('dfw_network_code') . '" class="regular-text" />';
	}
}

function dfw_breakpoints_input() {

	global $DoubleClick;

	foreach($DoubleClick->breakpoints as $b) {
		if( !$b->option ) {
			echo '<input value="' . $b->identifier . '" type="text" class="medium-text" disabled />';
			echo '<label> min-width</label><input value="' . $b->minWidth . '" type="number" class="small-text" disabled />';
			echo '<label> max-width</label><input value="' . $b->maxWidth . '" type="number" class="small-text" disabled /> (set by theme)<br/>';
		}
	}

	$breakpoints = unserialize( get_option('dfw_breakpoints') );

	$i = 0;

	while( $i < 5 ) {
		echo '<input value="' . $breakpoints[$i]['identifier'] . '" placeholder="Name" name="dfw_breakpoints[]" type="text" class="medium-text" />';
		echo '<label> min-width</label><input value="' . $breakpoints[$i]['min-width'] . '" placeholder="0" name="dfw_breakpoints[]" type="number" class="small-text" /> ';
		echo '<label> max-width</label><input value="' . $breakpoints[$i]['max-width'] . '"placeholder="9999" name="dfw_breakpoints[]" type="number" class="small-text" /> <br/>';	
		$i++;
	}	

}

function dfw_breakpoints_save($value,$option) {

	$breakpoints = array();

	if(isset($value[0]) && $value[0]) {
		$breakpoints[] = array(
			'identifier' => $value[0],
			'min-width' => $value[1],
			'max-width' => $value[2]
			);
	}
	if(isset($value[3]) && $value[3]) {
		$breakpoints[] = array(
			'identifier' => $value[3],
			'min-width' => $value[4],
			'max-width' => $value[5]
			);
	}
	if(isset($value[6]) && $value[6]) {
		$breakpoints[] = array(
			'identifier' => $value[6],
			'min-width' => $value[7],
			'max-width' => $value[8]
			);
	}
	if(isset($value[9]) && $value[9]) {
		$breakpoints[] = array(
			'identifier' => $value[9],
			'min-width' => $value[10],
			'max-width' => $value[11]
			);
	}
	if(isset($value[12]) && $value[12]) {
		$breakpoints[] = array(
			'identifier' => $value[13],
			'min-width' => $value[14],
			'max-width' => $value[15]
			);
	}

	error_log(sizeof($breakpoints));

	return serialize($breakpoints);

}

