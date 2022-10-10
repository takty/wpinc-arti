<?php
/**
 * Utilities
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2022-10-11
 */

namespace wpinc\post;

/**
 * Retrieves post type title.
 */
function get_post_type_title() {
	$post_type = get_query_var( 'post_type' );
	if ( is_array( $post_type ) ) {
		$post_type = reset( $post_type );
	}
	$post_type_obj = get_post_type_object( $post_type );
	return apply_filters( 'post_type_archive_title', $post_type_obj->labels->name, $post_type );
}
