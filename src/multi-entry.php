<?php
/**
 * Multi-Entries
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2022-06-17
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
	$ids = array_map( 'intval', $ids );
	$ps  = _get_pages_by_ids( $ids );
	the_loop_with_page_template( $ps, $slug, $name, $args );
}

/**
 * Expands multiple entries with titles.
 *
 * @param array       $id_to_title Array of post IDs to their titles.
 * @param string      $slug        The slug name for the generic template.
 * @param string|null $name        (Optional) The name of the specialized template. Default null.
 * @param array       $args        (Optional) Additional arguments passed to the template. Default array().
 */
function expand_entries_with_titles( array $id_to_title, string $slug, ?string $name = null, array $args = array() ): void {
	$id2t = array();
	foreach ( $id_to_title as $id => $title ) {
		$id2t[ (int) $id ] = $title;
	}
	$ps = _get_pages_by_ids( array_keys( $id2t ) );
	foreach ( $ps as $p ) {
		$p->post_title = $id2t[ $p->ID ];
	}
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
	$ps = get_posts(
		array(
			'posts_per_page' => -1,
			'post_type'      => 'page',
			'orderby'        => 'menu_order',
			'order'          => 'asc',
			'post__in'       => $ids,
		)
	);

	$id2p = array_column( $ps, null, 'ID' );
	$ret  = array();
	foreach ( $ids as $id ) {
		if ( isset( $id2p[ $id ] ) ) {
			$ret[] = $id2p[ $id ];
		}
	}
	return $ret;
}
