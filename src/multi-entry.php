<?php
/**
 * Multi-Entries
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2022-01-26
 */

namespace wpinc\post;

/**
 * Expands multiple entries.
 *
 * @param array       $ids  Post IDs.
 * @param string      $slug The slug name for the generic template.
 * @param string|null $name (Optional) The name of the specialized template. Default null.
 * @param array       $args (Optional) Additional arguments passed to the template. Default array().
 */
function expand_entries( array $ids, string $slug, ?string $name = null, array $args = array() ): void {
	$ids = array_map(
		function ( $id ) {
			return (int) $id;
		},
		$ids
	);
	$ps  = _get_pages_by_ids( $ids );
	_the_loop_posts_with_custom_page_template( $ps, $slug, $name, $args );
}

/**
 * Retrieves pages by post IDs.
 *
 * @access private
 *
 * @param array $ids Post IDs.
 * @return array Pages.
 */
function _get_pages_by_ids( array $ids ): array {
	$args = array(
		'posts_per_page' => -1,
		'post_type'      => 'page',
		'orderby'        => 'menu_order',
		'order'          => 'asc',
		'post__in'       => $ids,
	);

	$ps   = get_posts( $args );
	$id2p = array();
	foreach ( $ps as $p ) {
		$id2p[ $p->ID ] = $p;
	}
	$ret = array();
	foreach ( $ids as $id ) {
		if ( isset( $id2p[ $id ] ) ) {
			$ret[] = $id2p[ $id ];
		}
	}
	return $ret;
}
/**
 * Display each page with custom page template.
 *
 * @param \WP_Post[]  $ps   Array of post objects.
 * @param string      $slug The slug name for the generic template.
 * @param string|null $name (Optional) The name of the specialized template. Default null.
 * @param array       $args (Optional) Additional arguments passed to the template. Default array().
 */
function _the_loop_posts_with_custom_page_template( array $ps, string $slug, ?string $name = null, array $args = array() ): void {
	global $post;
	foreach ( $ps as $post ) {  // phpcs:ignore
		setup_postdata( $post );

		$n  = $name;
		$pt = get_page_template_slug();
		if ( ! empty( $pt ) && 'default' !== $pt ) {
			$n = basename( $pt, '.php' );
		}
		get_template_part( $slug, $n, $args );
	}
	wp_reset_postdata();
}
