<?php
add_action( 'wp_enqueue_scripts', 'eanewton_enqueue_styles' );
function eanewton_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'styles', get_stylesheet_directory_uri().'/css/dist/style.css', array(), date(YmdHis) );
}
?>
