<?php

function create_sites_post_type() {
  register_post_type( 'site',
    array(
      'labels' => array(
        'name' => __( 'Sites' ),
        'singular_name' => __( 'Site' )
      ),
      'public' => true,
      'has_archive' => true,
      'menu_position' => 4,
      'menu_icon' => 'dashicons-hammer',
      'publicly_queryable' => false,
      'supports' => array('title', 'editor', 'thumbnail', 'excerpt')
    )
  );
}
add_action( 'init', 'create_sites_post_type' );

// Create Site URL box

add_action( 'add_meta_boxes', 'add_url_box' );

function add_url_box() {
    add_meta_box(
        'site_url', // ID, should be a string.
        'Site URL', // Meta Box Title.
        'site_url_meta_box', // Your call back function, this is where your form field will go.
        'site', // The post type you want this to show up on, can be post, page, or custom post type.
        'normal', // The placement of your meta box, can be normal or side.
        'low' // The priority in which this will be displayed.
    );
}

function site_url_meta_box( $post ) {

	// Add a nonce field so we can check for it later.
	wp_nonce_field( 'site_url_save_meta_box_data', 'site_url_meta_box_nonce' );
	$URLValue = get_post_meta( $post->ID, 'site_url', true );

	echo '<input type="text" id="site_url" name="site_url" style="width:100%;" value="' . esc_attr( $URLValue ) . '" />';
}

function site_url_save_meta_box_data( $post_id ) {

	// Check if our nonce is set.
	if ( ! isset( $_POST['site_url_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['site_url_meta_box_nonce'], 'site_url_save_meta_box_data' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	/* OK, it's safe for us to save the data now. */

	// Make sure that it is set.
	if ( ! isset( $_POST['site_url'] ) ) {
		return;
	}

	// Sanitize user input.
	$site_url = sanitize_text_field( $_POST['site_url'] );

	// Update the meta field in the database.
	update_post_meta( $post_id, 'site_url', $site_url );
}
add_action( 'save_post', 'site_url_save_meta_box_data' );
