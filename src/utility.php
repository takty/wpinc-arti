<?php
/**
 * Utilities
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2022-01-27
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

/**
 * Enables simple default slugs.
 *
 * @param string|string[] $post_type_s Post types.
 */
function enable_simple_default_slug( $post_type_s = array() ) {
	$pts = is_array( $post_type_s ) ? $post_type_s : array( $post_type_s );
	add_filter(
		'wp_unique_post_slug',
		function ( $slug, $post_ID, $post_status, $post_type ) use ( $pts ) {
			$post = get_post( $post_ID );
			if ( '0000-00-00 00:00:00' === $post->post_date_gmt ) {
				if ( empty( $pts ) || in_array( $post_type, $pts, true ) ) {
					$slug = $post_ID;
				}
			}
			return $slug;
		},
		10,
		4
	);
}
