<?php 

/*
*
* Plugin name: wptreehouse-badges
*
*/

/*
 *	Assign global variables
 *
*/

$plugin_url = WP_PLUGIN_URL . '/wptreehouse-badges';
$options = array();
$display_json = true;

/*
 *	Add a link to our plugin in the admin menu
 *	under 'Settings > Treehouse Badges'
 *
*/

// Creates the menu in the admin area.

function wptreehouse_badges_menu() {

	/*
	 * 	Use the add_options_page function
	 * 	add_options_page( $page_title, $menu_title, $capability, $menu-slug, $function ) 
	 *
	*/

	add_options_page(
		'Official Treehouse Badges Plugin',
		'Treehouse Badges',
		'manage_options',
		'wptreehouse-badges',
		'wptreehouse_badges_options_page'
	);

}
add_action( 'admin_menu', 'wptreehouse_badges_menu' );


// Main plugin logic. Included file deals with the markup and layout

function wptreehouse_badges_options_page() {

	if( !current_user_can( 'manage_options' ) ) {

		wp_die( 'You do not have sufficient permissions to access this page.' );

	}

	global $plugin_url;
	global $options;
	global $display_json;

	if ( isset($_POST['wptreehouse_username_submit']) ) {

		$wptreehouse_username = esc_html( $_POST['wptreehouse_username']);
		$wptreehouse_profile = wptreehouse_profile_get( $wptreehouse_username );

		$options['wptreehouse_username'] = $wptreehouse_username;
		$options['wptreehouse_profile'] = $wptreehouse_profile;
		$options['last_updated'] = time();

		update_option( 'wptreehouse_badges', $options );

	}

	$options = get_option( 'wptreehouse_badges' );

	if ( $options != '' ) {

		$wptreehouse_username = $options['wptreehouse_username'];
		$wptreehouse_profile = $options['wptreehouse_profile'];

	}

	require( 'inc/options-page-wrapper.php' );

}

// Get the json feed from the Treehouse profile

function wptreehouse_profile_get( $wptreehouse_username) {

	$url = 'http://teamtreehouse.com/' . $wptreehouse_username . '.json';
	$args = array('timeout' => 120);

	$response = wp_remote_get( $url, $args );

	$response = json_decode($response['body']);

	return $response;

}

class Wptreehouse_Badges_Widget extends WP_Widget {

	function wptreehouse_badges_widget() {
		// Instantiate the parent object
		parent::__construct( 
			'treehouse-badges-widget', 
			'Treehouse Badges Widget',
			array(

				'description' => 'The official Treehouse Badges plugin. Created by Andrew Chappell.',
				'classname'   => 'treehouse-badges-widget'

			) 
		);
	}

	// Takes care of how the widget looks on the front end of the site.

	function widget( $args, $instance ) {

		extract( $args );

		$title = apply_filters( 'widget_title' , $instance['title'] );
		$num_badges = $instance[ 'num_badges' ];
		$display_tooltip = $instance[ 'display_tooltip' ];

		$options = get_option( 'wptreehouse_badges' );
		$wptreehouse_profile = $options['wptreehouse_profile'];

		require('inc/front-end.php');
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

	function update( $new_instance, $old_instance ) {
		// Save widget options

		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['num_badges'] = strip_tags($new_instance['num_badges']);
		$instance['display_tooltip'] = strip_tags($new_instance['display_tooltip']);

		return $instance;

	}

	// Displays the actual form in the backend. 

	function form( $instance ) {
		// Output admin widget options form

		$title = ! empty( $instance['title'] ) ? esc_attr( $instance['title'] ) : __( 'New title', 'text_domain' );
		$num_badges = esc_attr($instance['num_badges']);
		$display_tooltip = esc_attr($instance['display_tooltip']);

		$options = get_option( 'wptreehouse_badges' );
		$wptreehouse_profile = $options['wptreehouse_profile'];

		require( 'inc/widget-fields.php' );

	}

}

function wptreehouse_badges_refresh_profile() {

	$options = get_option('wptreehouse_badges');
	$last_updated = $options['last_updated'];
	$current_time = time(); 


	$update_difference = $current_time - $last_updated;

	if ($update_difference > 84600) {

		$wptreehouse_username = $options['wptreehouse_username'];

		$wptreehouse_profile = wptreehouse_profile_get( $wptreehouse_username );

		$options['last_updated'] = time();

		update_option('wptreehouse_badges', $options);

	}

	die();
}

add_action('wp_ajax_wptreehouse_badges_refresh_profile', 'wptreehouse_badges_refresh_profile' );
add_action( 'wp_ajax_nopriv_wptreehouse_badges_refresh_profile', 'wptreehouse_badges_refresh_profile' );

// enqueue the scripts to enable ajax to work.

add_action( 'wp_enqueue_scripts', 'wptreehouse_badges_admin_scripts' );

function wptreehouse_badges_admin_scripts($hook) {
        
	wp_enqueue_script( 'wptreehouse-ajax', plugins_url( '/wptreehouse-ajax.js', __FILE__ ), array('jquery'), '', true );

	// ajax_object is the name of the object we are creating. 
	// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
	wp_localize_script( 'wptreehouse-ajax', 'ajax_object',

            array( 
            	'ajax_url' => admin_url( 'admin-ajax.php' )
            )
    );
}

function wptreehouse_badges_register_widgets() {

	register_widget( 'Wptreehouse_Badges_Widget' );

}

add_action( 'widgets_init', 'wptreehouse_badges_register_widgets' );

// plugin styles

function wptreehouse_badges_style() {

	wp_enqueue_style('wptreehouse_badges_style', plugins_url('wptreehouse-badges.css', __FILE__) );

}

add_action('admin_head', 'wptreehouse_badges_style' );

function wptreehouse_badges_scripts_and_styles() {

	wp_enqueue_style('wptreehouse_badges_style', plugins_url('wptreehouse-badges.css', __FILE__) );
	wp_enqueue_script('wptreehouse_badges_script', plugins_url('wptreehouse-badges.js', __FILE__), array('jquery'), '', true );

}

add_action('wp_enqueue_scripts', 'wptreehouse_badges_scripts_and_styles' );
