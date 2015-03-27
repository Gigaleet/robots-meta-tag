<?php
	/**
	 * Plugin Name: Robots Meta Tag Manager
	 * Plugin URI: https://github.com/Gigaleet/robots-meta-tag
	 * Description: WordPress Robots Meta Tag Manager Plug-in, based on the robots meta tag tool built in to the CKG Blank theme (v2.1+).  <strong>Please DO NOT activate this plugin if you're running CKG Blank Theme version 2.1 or higher</strong>.
	 * Version: 0.1.0 Beta
	 * Author: David Lewis
	 * Author URI: https://github.com/Gigaleet/
	 * License: GPL2
	 */
	
	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


	register_activation_hook(__FILE__, 'robots_meta_tag_activation');
	function robots_meta_tag_activation() {
	// TODO
	}

	/**
	 * Adds a box to the main column on the Post and Page edit screens.
	 */
	function add_robots_meta_box() {
	    $screens = array( 'post', 'page', 'resources' );
	    foreach ( $screens as $screen ) {
	        add_meta_box('robots_meta_sectionid', __( 'Robots Meta Tag', 'robots_meta_textdomain' ), 'robots_meta_box_callback', $screen, 'side', 'default');
	    }
	}
	add_action( 'add_meta_boxes', 'add_robots_meta_box' );

	/**
	 * Prints the box content.
	 */
	function robots_meta_box_callback($post){
	    wp_nonce_field( 'robots_meta_box', 'robots_meta_box_nonce' );
	    $value = get_post_meta($post->ID, '_robots_meta_value_key', true);
	    ?>   
	    <label for="robots_meta_new_field"><?php _e( 'content=', 'robots_meta_textdomain' ); ?></label>
	    <select name="robots_meta_new_field" id="robots_meta_new_field">
	      <option value="noindex, nofollow" <?php selected( $value, 'noindex, nofollow' ); ?>>noindex, nofollow</option>
	      <option value="index, follow" <?php selected( $value, 'index, follow' ); ?>>index, follow</option>
	      <option value="noindex, follow" <?php selected( $value, 'noindex, follow' ); ?>>noindex, follow</option>
	      <option value="index, nofollow" <?php selected( $value, 'index, nofollow' ); ?>>index, nofollow</option>
	    </select>
	    <?php
	}

	/**
	 * When the post is saved, saves our custom data.
	 */
	function save_robots_meta_box_data( $post_id ) {
	    // Check if our nonce is set.
	    if ( ! isset( $_POST['robots_meta_box_nonce'] ) ) {
	        return;
	    }
	    // Verify that the nonce is valid.
	    if ( ! wp_verify_nonce( $_POST['robots_meta_box_nonce'], 'robots_meta_box' ) ) {
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
	    // Make sure that it is set.
	    if ( ! isset( $_POST['robots_meta_new_field'] ) ) {
	        return;
	    }
	    // Sanitize user input.
	    $my_data = sanitize_text_field( $_POST['robots_meta_new_field'] );
	    // Update the meta field in the database.
	    update_post_meta( $post_id, '_robots_meta_value_key', $my_data );
	}
	add_action( 'save_post', 'save_robots_meta_box_data' );

	/**
	 *  Add Robots Meta tag to the top of wp_head 
	**/
	function enqueue_robots_meta() {
	    ?>
<!-- Robots Meta Tag -->
	    <meta name="robots" content="<?php if(get_post_meta(get_the_ID(), '_robots_meta_value_key', true)){echo get_post_meta(get_the_ID(), '_robots_meta_value_key', true);}else{echo 'noindex, nofollow';} ?>">
	<!-- End Robots Meta Tag -->
	    <?php
	}
	add_action( 'wp_head', 'enqueue_robots_meta', 1 );

	/**
	 *  Add Robots Meta tag column to post, pages and CPTs*.
	 *  *except super_content 
	**/
	// Register Column
	function rm_columns_head($defaults) {
	    $defaults['robots_tag'] = 'Robots Meta Tag';
	    return $defaults;
	}
	// Display Content
	function rm_columns_content($column_name, $post_ID) {
	    if ($column_name == 'robots_tag') {
	        $rm_tag = get_post_meta($post_ID, '_robots_meta_value_key', true);
	        echo $rm_tag;
	    }
	}
	// Add Column to post
	add_filter('manage_posts_columns', 'rm_columns_head');
	add_action('manage_posts_custom_column', 'rm_columns_content', 10, 2);
	// Add Comlumn to pages
	add_filter('manage_pages_columns', 'rm_columns_head');
	add_action('manage_pages_custom_column', 'rm_columns_content', 10, 2);

	// Remove Column from super_content CPT
	function remove_rm_from_super( $columns ) {
	   unset($columns['robots_tag']);
	   return $columns;
	}
	add_filter( 'manage_edit-super_content_columns', 'remove_rm_from_super',10, 1 );


	register_deactivation_hook(__FILE__, 'robots_meta_tag_deactivation');
	function robots_meta_tag_deactivation() {
	    //do something
	}

?>