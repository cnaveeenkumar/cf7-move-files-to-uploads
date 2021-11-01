<?php
/*
Plugin Name:CF7 Move Files to Uploads
Description:It's a simple plugin move contact form 7 attachment to uploads folder.
Version:1.2
Author:Naveenkumar C
License:GPL2

Copyright 2014-2017 Naveenkumar C (email: cnaveen777 at gmail.com)

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details. 

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA.
*/

// Exit you access directly
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Activate the plugin.
 */
function cf7MoveFilesToUploadsActivate() { 

	// Require parent ( Contact Form 7 ) plugin
    if ( ! is_plugin_active('contact-form-7/wp-contact-form-7.php') and current_user_can('activate_plugins') ) {
        // Stop activation redirect and show error
        wp_die('Sorry, but this plugin requires the Contact Form 7 Plugin to be installed and active. <br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; Return to Plugins</a>');
	}
	
}
register_activation_hook( __FILE__, 'cf7MoveFilesToUploadsActivate' );

/**
 * Deactivation hook.
 */
function cf7MoveFilesToUploadsDeactivate() {
    // Unregister the post type, so the rules are no longer in memory.
    // Clear the permalinks to remove our post type's rules from the database.
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'cf7MoveFilesToUploadsDeactivate' );


add_action('wpcf7_before_send_mail', 'moveFilesToMedia' );

function moveFilesToMedia($wpcf7) {

    $submission = WPCF7_Submission::get_instance();

	if( $submission ){
		$files		= $submission->uploaded_files();
		$media_dir 	= wp_upload_dir();
		$time_now	= time();
		$upload_data= array();
		foreach ($files as $file_key => $file) {
			copy($file, $media_dir['path'].'/'.$time_now.'-'.basename($file));
			$upload_data['name'] = basename($file);
		}
		$filename = $upload_data['name'];
		$file = $media_dir['path'].'/'.$time_now.'-'.basename($file);
		
		$wp_filetype = wp_check_filetype( $filename, null );

		$attachment = array(
		  'post_mime_type' => $wp_filetype['type'],
		  'post_title' => sanitize_file_name( $filename ),
		  'post_content' => '',
		  'post_status' => 'inherit'
		);

		$attach_id = wp_insert_attachment( $attachment, $file );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );
	}

}


?>