<?php
/**
 * Utilities
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2022-10-30
 */

namespace wpinc\post;

require_once __DIR__ . '/assets/url.php';

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

/**
 * Retrieves page ID corresponding to the current URL.
 *
 * @return int Page ID.
 */
function get_corresponding_page_id(): int {
	$url = \wpinc\get_request_url( true );
	$pid = url_to_postid( $url );
	if ( $pid ) {
		if ( 'page' === get_post_type( $pid ) ) {
			$link = get_permalink( $pid );
			if ( trim( $link, '/' ) === trim( $url, '/' ) ) {
				return $pid;
			}
		}
	}
	return 0;
}
