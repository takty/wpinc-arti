<?php
/**
 * Multi-Entries
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2022-02-03
 */

namespace wpinc\post;

require_once __DIR__ . '/query.php';

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
	the_loop_with_page_template( $ps, $slug, $name, $args );
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
	$id2p = array();
	$ps   = get_posts(
		array(
			'posts_per_page' => -1,
			'post_type'      => 'page',
			'orderby'        => 'menu_order',
			'order'          => 'asc',
			'post__in'       => $ids,
		)
	);
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
