<?php
/**
 * Plugin Name: WP Tunning
 * Plugin URI: https://codetheworld.info
 * Description: Differents hooks and functions for improve WordPress and basic customize in a plugin.
 * Version: 1.0
 * Author: lriaudel
 * Contributors: 
 * Author URI: http://ludovic.riaudel.net
 * Licence: GPLv2
 *
 * Copyright 2013-2017 Ludovic Riaudel
 * */

/**
* Security
*/

/**
 * Head cleaning
 */
remove_action('wp_head', 'wp_generator');					// Deactivate WordPress version
remove_action('wp_head', 'wlwmanifest_link');				// Deactivate Windows Live Writer Manifest Link 
remove_action('wp_head', 'rsd_link');						// Deactivate RSD
add_filter( 'xmlrpc_enabled', '__return_false' );			// Deactivate XML-RPC

remove_action('wp_head', 'start_post_rel_link');			// Supprime le lien vers le premier post
// remove_action('wp_head', 'feed_links', 2 );				// Supprime le flux RSS général
remove_action('wp_head', 'feed_links_extra', 3 );			// Supprime le flux RSS des catégories
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0 ); 	// Supprime la balise lien court <link rel=shortlink
remove_action('wp_head', 'index_rel_link' );  				// Supprime la balise <link rel=index
remove_action('wp_head', 'parent_post_rel_link', 10, 0);  	// Supprime le lien vers la catégorie parente


/**
 * Deactivate file editor
 */
if( isset($DISALLOW_FILE_EDIT) ) {
	define('DISALLOW_FILE_EDIT', true);
}		

/**
 * Hide connections errors in wp-login.php
 */
add_filter( 'login_errors', create_function('$a', "return null;") );


/**
 * Source : https://wordpress.org/plugins/user-name-security/
 * Author : Daniel Roch
 * Filter body_class in order to hide User ID and User nicename
 * @param array $wp_classes holds every default classes for body_class function
 * @param array $extra_classes holds every extra classes for body_class function
 */
function seomix_sx_security_body_class( $wp_classes, $extra_classes ) {

	if ( is_author() ) {
		// Getting author Information
		$curauth = get_query_var( 'author_name' ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );
		// Blacklist author-ID class
		$blacklist[] = 'author-'.$curauth->ID;
		// Blacklist author-nicename class
		$blacklist[] = 'author-'.$curauth->user_nicename;
		// Delete useless classes
		$wp_classes = array_diff( $wp_classes, $blacklist );
	}
	// Return all classes
	return array_merge( $wp_classes, (array)$extra_classes );
}
add_filter( 'body_class', 'seomix_sx_security_body_class', 10, 2 );


/**
 * Plugin Name:  No french punctuation and accents for filename
 * Description:  Remove all french punctuation and accents from the filename of upload for client limitation (Safari Mac/IOS)
 * Plugin URI:   https://gist.github.com/herewithme/7704370
 * Version:      1.0
 * Author:       BeAPI
 * Author URI:   http://www.beapi.fr
 */
add_filter( 'sanitize_file_name', 'remove_accents', 10, 1 );
add_filter( 'sanitize_file_name_chars', 'sanitize_file_name_chars', 10, 1 );
function sanitize_file_name_chars( $special_chars = array() ) {
	$special_chars = array_merge( array( '’', '‘', '“', '”', '«', '»', '‹', '›', '—', 'æ', 'œ', '€' ), $special_chars );
	return $special_chars;
}

/**
 * @source https://gist.github.com/thierrypigot/90a97fdf84b033b72b32ca3ebfced2c1#file-username-admin-php
 * Disallow "admin" as username
 */
add_filter('validate_username' , 'tp_deny_admin_username', 10, 2);
function tp_deny_admin_username($valid, $username ) {
	if( 'admin' == $username ) {
		$valid = false;
	}
	return $valid;
}


/**
* Other tunning
*/

/**
* Revision setting limited to 5
* @source https://codex.wordpress.org/Revisions
*/ 
if(!defined('WP_POST_REVISIONS')){
	define('WP_POST_REVISIONS', 5);
}


/**
* Dashboard cleaning
* Deactivate useless metaboxes on dashboard
*/
function remove_dashboard_widgets() {
	remove_action('welcome_panel', 'wp_welcome_panel',99);

	global $wp_meta_boxes;
	//unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
	//unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
	//unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_activity']);
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']); 
	//unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
}
add_action('wp_dashboard_setup', 'remove_dashboard_widgets' );


/**
* Add medium format `medium_large` to media in admin
* This format is by default since version 4.4 but not appear in media
* 
* @param array $format Format list
* @return array $format
*/
function add_medium_large( $format ){
	$format['medium_large'] = __('Medium Large'); 
	return $format;
}
add_filter( 'image_size_names_choose', 'add_medium_large');


/*========================= emoji ============================= */

/**
 * Disable the emoji's
 * @source https://www.keycdn.com/blog/website-performance-optimization/#http
 */
function disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
}
add_action( 'init', 'disable_emojis' );

/**
 * Filter function used to remove the tinymce emoji plugin.
 *
 * @param    array  $plugins
 * @return   array  Difference betwen the two arrays
 */
function disable_emojis_tinymce( $plugins ) {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	} else {
		return array();
	}
}

/*========================= end emoji ============================= */


/**
 * Remove H1 from the WordPress editor.
 * H1 is only for page titles
 *
 * @param   array  $init   The array of editor settings
 * @return  array		 	The modified edit settings
 */
function modify_editor_buttons( $init ) {
	$init['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6;Preformatted=pre;';
	return $init;
}
add_filter( 'tiny_mce_before_init', 'modify_editor_buttons' );

