<?php 

/*
*
* Plugin name: wptreehouse-badges
*
*/

function wptreehouse_badges_menu() {


	// using native function http://codex.wordpress.org/Function_Reference/add_options_page
	// add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function);

	add_options_page(
		'Wptreehouse-badges plugin',
		'Treehouse Badges',
		'manage_options',
		'wptreehouse-badges',
		'wptreehouse_badges_options_page'
		);

}

add_action('admin_menu', 'wptreehouse_badges_menu');

function wptreehouse_badges_options_page() {

	if ( !current_user_can('manage_options') ) {

		wp_die('You do not have sufficient permissions to view this page');

	} else {

		echo '<p>Welcome to the Treehouse Badges plugin</p>';

	}


}