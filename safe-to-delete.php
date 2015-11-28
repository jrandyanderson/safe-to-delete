<?php 
/*
Plugin Name: Safe to Delete
Plugin URI: http://wordpress.org/extend/plugins/safe-to-delete/
Description: Shows if a template file is safe to delete or if it's being used by the theme.
Version: 0.1
Author: Randy Anderson
Author URI: http://randyanderson.org
*/

if( !defined( 'ABSPATH' ) ) 
	exit;

// Add admin menu
add_action( 'admin_menu', 'jrastd_plugin_menu' );

// This is the actual plugin page.
function jrastd_plugin_menu() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	// Add page to Tools submenu
	add_management_page( 'Safe to Delete', 'Safe to Delete', 'manage_options', 'jra-stc', 'jra_safe_to_delete' );

}

function jra_safe_to_delete() {
	wp_enqueue_script('jquery');
	echo '<h1>Safe to Delete</h1>
	<p>This tool will help you decide whether or not a template file is safe to delete.</p>
	<h2>Is <input type="text" id="file_to_check" name="template-file" placeholder="template-file.php" /> safe to delete? <a class="std-lets-check" href="#" style="outline: none;">Let\'s Check...</a></h2><div class="ajax-response" style="border-top: 2px solid black;"></div>';
}

function jra_query_database($file) {
	
}

add_action( 'admin_footer', 'jra_std_scripts' );

function jra_std_scripts() { ?>
	<script type="text/javascript" >
	jQuery(document).ready(function($) {
		var stdData = {
			'action': 'jra_safe_to_delete',
			'file': ''
		};
		$('#file_to_check').change(function(event) { stdData['file'] = $(this).val();});
		$('.std-lets-check').click(function(event) {
			jQuery.post(ajaxurl, stdData, function(response) {
				$('.ajax-response').html(response);
			});
			event.preventDefault();
		});
	});
	</script> <?php
}

add_action( 'wp_ajax_jra_safe_to_delete', 'jra_safe_to_delete_callback' );

function jra_safe_to_delete_callback() {
	global $wpdb; // this is how you get access to the database
	$sanitizedFile = sanitize_text_field( $_POST['file'] );
	$file_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_value = %s", $sanitizedFile ) );
	if (is_null($file_count)) {
		echo "<h2 style=\"color: green; font-weight: bold;\">This file [{$sanitizedFile}] is safe to delete! Do it.</h2>";
	} else if ($file_count < 1) {
		echo "<h2 style=\"color: green; font-weight: bold;\">This file [{$sanitizedFile}] is safe to delete! Do it.</h2>";
	} else {
		echo "<h2 style=\"color: red; font-weight: bold;\">This file [{$sanitizedFile}] is not safe to delete!</h2>";
		echo "<p>{$sanitizedFile} is used {$file_count} time";
		if ($file_count != 1) 
			echo "s";
		echo ":</p><ul style=\"margin-left: 0.5em;\">";
		$items = $wpdb->get_col( $wpdb->prepare( "SELECT ALL post_id FROM $wpdb->postmeta WHERE meta_value = %s", $sanitizedFile ) );
		foreach ($items as $item) {
			echo "<li><h3 style=\"display: inline-block; margin: 0 0 0.5em 0\">- " . get_the_title($item) . "</h3><p style=\"text-indent: 0.5em; display: inline-block; margin: 0 0 0.5em 0\"> <a href=\"" . get_permalink($item) . "\">View</a> | <a href=\"" . get_edit_post_link($item) . "\">Edit</a></p></li>";
		}
		echo "</ul>";
	}
	wp_die(); // this is required to terminate immediately and return a proper response
}

?>