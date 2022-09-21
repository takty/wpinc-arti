<?php
/**
 * Utilities
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2022-09-21
 */

namespace wpinc\post;

/**
 * Checks current post type.
 *
 * @param string $post_type Post type.
 * @return bool True if the current post type is $post_type.
 */
function is_post_type( string $post_type ): bool {
	$pt = null;

	$id_g = $_GET['post']     ?? null;  // phpcs:ignore
	$id_p = $_POST['post_ID'] ?? null;  // phpcs:ignore

	if ( $id_g || $id_p ) {
		$p = get_post( intval( $id_g ? $id_g : $id_p ) );
		if ( $p ) {
			$pt = $p->post_type;
		}
	}
	if ( ! $pt ) {
		$pt = $_GET['post_type'] ?? null;  // phpcs:ignore
	}
	return $post_type === $pt;
}

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
