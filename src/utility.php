<?php
/**
 * Utilities
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2023-08-31
 */

namespace wpinc\post;

require_once __DIR__ . '/assets/url.php';

/**
 * Retrieves post type title.
 *
 * @return string Post type title.
 */
function get_post_type_title(): string {
	$post_type = get_query_var( 'post_type' );
	if ( is_array( $post_type ) ) {
		$post_type = reset( $post_type );
	}
	$post_type_obj = get_post_type_object( $post_type );
	if ( ! $post_type_obj ) {
		return '';
	}
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
			$link = (string) get_permalink( $pid );
			if ( trim( $link, '/' ) === trim( $url, '/' ) ) {
				return $pid;
			}
		}
	}
	return 0;
}


// -----------------------------------------------------------------------------


/**
 * Computes the difference of arrays of posts.
 *
 * @param \WP_Post[] $array     The array to compare from.
 * @param \WP_Post[] ...$arrays Arrays to compare against.
 * @return \WP_Post[] Posts.
 */
function post_array_diff( array $array, array ...$arrays ): array {
	$ids = array();
	foreach ( $arrays as $ps ) {
		foreach ( $ps as $p ) {
			$ids[] = $p->ID;
		}
	}
	$ret = array();
	foreach ( $array as $p ) {
		if ( ! in_array( $p->ID, $ids, true ) ) {
			$ret[] = $p;
		}
	}
	return $ret;
}

/**
 * Sorts posts.
 *
 * @param array<string, mixed> $args {
 *     Arguments.
 *
 *     @type string 'order' Order of sorting: 'asc' or 'desc'. Default 'desc'.
 * }
 * @param \WP_Post[]           ...$arrays Array of post arrays.
 * @return \WP_Post[] Posts.
 */
function sort_post_array( array $args, array ...$arrays ): array {
	$args += array(
		'order' => 'desc',
	);

	$date_ps = array();
	foreach ( $arrays as $ps ) {
		foreach ( $ps as $p ) {
			$d = get_post_time( 'Ymd', false, $p );
			if ( ! isset( $date_ps[ $d ] ) ) {
				$date_ps[ $d ] = array();
			}
			$date_ps[ $d ][ $p->ID ] = $p;
		}
	}
	$order = strtolower( $args['order'] );
	if ( 'asc' === $order ) {
		ksort( $date_ps );
	} elseif ( 'desc' === $order ) {
		krsort( $date_ps );
	}
	$pss = array_values( $date_ps );
	return array_merge( array(), ...array_map( 'array_values', $pss ) );
}
