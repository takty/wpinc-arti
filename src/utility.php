<?php
/**
 * Utilities
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2022-01-17
 */

namespace wpinc\post;

/**
 * Retrieves post type as page title.
 *
 * @param string $prefix  Prefix.
 * @param bool   $display (Optional) Whether to display or retrieve title. Default true.
 */
function post_type_title( string $prefix = '', bool $display = true ) {
	$post_type = get_query_var( 'post_type' );
	if ( is_array( $post_type ) ) {
		$post_type = reset( $post_type );
	}
	$post_type_obj = get_post_type_object( $post_type );
	$title         = apply_filters( 'post_type_archive_title', $post_type_obj->labels->name, $post_type );

	if ( $display ) {
		echo esc_html( $prefix . $title );
	} else {
		return $prefix . $title;
	}
}
