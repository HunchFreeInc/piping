<?php

defined('\\ABSPATH') or die('Permission denied');

if ( ! is_admin() ) {
	function piping_setup_styles_and_scripts() {
		$assets_url = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/';
		wp_register_script('icons', 'https://use.fontawesome.com/releases/v5.0.7/js/all.js', null, true);

		wp_register_script('foundation-js', $assets_url . 'js/vendor/foundation.js', array( 'jquery-js' ), null, true);
		wp_register_script('app', $assets_url . 'js/app.js', array( 'jquery' ), null, true);

		wp_register_script('modernizr', $assets_url . 'js/modernizr.custom.js', array( 'jquery' ), null, true);
		wp_register_script('classie', $assets_url . 'js/classie.js', array( 'jquery' ), null, true);
		wp_register_script('demo-1', $assets_url . 'js/demo1.js', array( 'jquery' ), null, true);
		wp_register_script('jquery-js', $assets_url . 'js/vendor/jquery.js', array( 'jquery' ), null, true);

		wp_enqueue_script('icons');
		wp_enqueue_script('foundation-js');
		wp_enqueue_script('modernizr');
		wp_enqueue_script('classie');
		wp_enqueue_script('demo-1');
		wp_enqueue_script('jquery-js');
		wp_enqueue_script('app');

	}
	
	add_action('wp_enqueue_scripts', 'piping_setup_styles_and_scripts' );
	
}

function register_my_menus() {
  register_nav_menus(
    array(
      'main-menu' => __( 'Main Menu' ),
      'utility-menu' => __( 'Utility Menu' ),
      'mobile-menu' => __( 'Mobile Menu' ),
      'footer-menu' => __( 'Footer Menu' )
    )
  );
}
add_action( 'init', 'register_my_menus' );