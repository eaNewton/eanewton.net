<?php
add_action( 'wp_enqueue_scripts', 'eanewton_styles' );
function eanewton_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'styles', get_stylesheet_directory_uri().'/css/dist/style.css', array(), date(YmdHis) );
}

add_action('wp_enqueue_scripts', 'eanewton_scripts');
function eanewton_scripts() {
  wp_enqueue_script( 'main', get_stylesheet_directory_uri().'/js/dist/app.min.js', array('jquery'), date(YmdHis) );
}

require_once locate_template('/inc/sites.php');

?>
