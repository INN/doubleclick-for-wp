<?php
/**
 * Functions related to the options page
 */

/**
 * Register option page.
 *
 * @since v0.1
 */
function dfw_plugin_menu() {
	add_options_page(
		'Super Cool Ad Manager Plugin', // $page_title title of the page.
		'DoubleClick/Ad Manager',       // $menu_title the text to be used for the menu.
		'manage_options',            // $capability required capability for display.
		'doubleclick-for-wordpress', // $menu_slug unique slug for menu.
		'dfw_option_page_html'       // $function callback.
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
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'dfw' ) );
	}

	echo '<div class="wrap">';
	echo '<h2>Super Cool Ad Manager Options</h2>';
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
	);

	// Add a section for network option
	add_settings_section(
		'dfw_breakpoint_options',
		'Breakpoints',
		'dfw_breakpoints_section_intro',
		'doubleclick-for-wordpress'
	);

	// Add a section for network option
	add_settings_section(
		'dfw_documentation_options',
		'Documentation',
		'dfw_documentation_section_intro',
		'doubleclick-for-wordpress'
	);

	// Network Code
	add_settings_field(
		'dfw_network_code',
		'Google Ad Manager Network Code',
		'dfw_network_code_input',
		'doubleclick-for-wordpress',
		'dfw_network_options'
	);

	// Breakpoints
	add_settings_field(
		'dfw_breakpoints',
		'Breakpoints',
		'dfw_breakpoints_input',
		'doubleclick-for-wordpress',
		'dfw_breakpoint_options'
	);

	register_setting( 'doubleclick-for-wordpress', 'dfw_network_code' );
	register_setting( 'doubleclick-for-wordpress', 'dfw_breakpoints', 'dfw_breakpoints_save' );

}
add_action( 'admin_init', 'dfw_register_options' );

function dfw_settings_section_intro() {
	echo "Enter a Google Ad Manager Network Code ( <a href='https://developers.google.com/doubleclick-publishers/docs/start#signup' target='_blank'>?</a> )";
}

function dfw_breakpoints_section_intro() {
	echo 'Enter breakpoints below<br />';
	echo '<em>Example: phone: 0 to 480, tablet 480 to 768, desktop 768 to 9999.</em>';
}

function dfw_documentation_section_intro() {
	echo '<a href="https://github.com/INN/DoubleClick-for-WordPress/blob/master/docs/readme.md">Available on GitHub</a>';
}

function dfw_network_code_input() {
	global $doubleclick;

	if ( isset( $doubleclick->network_code ) ) {
		echo '<input value="' . esc_attr( $doubleclick->network_code ) . ' (set in theme)" type="text" class="regular-text" disabled/>';
	} else {
		echo '<input name="dfw_network_code" id="dfw_network_code" type="text" value="' . esc_attr( get_option( 'dfw_network_code' ) ) . '" class="regular-text" />';
	}
}

function dfw_breakpoints_input() {
	global $doubleclick;

	foreach ( $doubleclick->breakpoints as $breakpoint ) {
		if ( ! $breakpoint->option ) {
			echo '<input value="' . esc_attr( $breakpoint->identifier ) . '" type="text" class="medium-text" disabled />';
			echo '<label> min-width</label><input value="' . esc_attr( $breakpoint->min_width ) . '" type="number" class="small-text" disabled />';
			echo '<label> max-width</label><input value="' . esc_attr( $breakpoint->max_width ) . '" type="number" class="small-text" disabled /> (set in theme)<br/>';
		}
	}

	$breakpoints = maybe_unserialize( get_option( 'dfw_breakpoints' ) );

	/*
	 * Note here that these are not individually named inputs!
	 * These are all alike, forming a singular array that dfw_breakpoints_save()
	 * breaks into an associative array using array_chunk()
	 *
	 * @todo: instead of $i being 5, count $breakpoints, then output a foreach of $breakpoints plus ( 5-n, minimum 1 ) additional fields.
	 */
	$i = 0;
	while ( $i < 5 ) {
		$identifier = ( isset( $breakpoints[ $i ]['identifier'] ) )? $breakpoints[ $i ]['identifier'] : '';
		$min_width = ( isset( $breakpoints[ $i ]['min-width'] ) )? $breakpoints[ $i ]['min-width'] : '';
		$max_width = ( isset( $breakpoints[ $i ]['max-width'] ) )? $breakpoints[ $i ]['max-width'] : '';

		// fallbacks for post-https://github.com/INN/doubleclick-for-wp/pull/46 compatibility.
		if ( empty( $min_width ) && isset( $breakpoints[ $i ][ 'minWidth' ] ) ) {
			$min_width = ( isset( $breakpoints[ $i ]['minWidth'] ) )? $breakpoints[ $i ]['minWidth'] : '';
		}
		if ( empty( $max_width ) && isset( $breakpoints[ $i ][ 'maxWidth' ] ) ) {
			$max_width = ( isset( $breakpoints[ $i ]['maxWidth'] ) )? $breakpoints[ $i ]['maxWidth'] : '';
		}

		// fallbacks, continued.
		if ( empty( $min_width ) && isset( $breakpoints[ $i ][ 'min-width' ] ) ) {
			$min_width = ( isset( $breakpoints[ $i ]['min-width'] ) )? $breakpoints[ $i ]['min-width'] : '';
		}
		if ( empty( $max_width ) && isset( $breakpoints[ $i ][ 'max-width' ] ) ) {
			$max_width = ( isset( $breakpoints[ $i ]['max-width'] ) )? $breakpoints[ $i ]['max-width'] : '';
		}

		?>
		<input value="<?php echo esc_attr( $identifier ); ?>"
				placeholder="Name"
				name="dfw_breakpoints[]"
				type="text"
				class="medium-text" />
		<label> min-width
			<input
				value="<?php echo esc_attr( $min_width ); ?>" placeholder="0"
				name="dfw_breakpoints[]"
				type="number"
				class="small-text" />
		</label>
		<label> max-width
			<input
				value="<?php echo esc_attr( $max_width ); ?>"
				placeholder="9999"
				name="dfw_breakpoints[]"
				type="number"
				class="small-text" />
		</label><br/><?php
		$i++;
	}

}

function dfw_breakpoints_save( $value ) {
	$breakpoints = array();
	$groups = array_chunk( $value, 3 );

	foreach ( $groups as $group ) {
		if ( isset( $group[0] ) && $group[0] ) {
			$breakpoints[] = array(
				'identifier' => $group[0],
				'min-width' => $group[1],
				'max-width' => $group[2],
			);
		}
	}

	return $breakpoints;
}
