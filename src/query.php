<?php
/**
 * Query
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2022-11-15
 */

namespace wpinc\post;

/**
 * Displays the post loop.
 *
 * @param \WP_Post[]  $ps   Array of post objects.
 * @param string      $slug The slug name for the generic template.
 * @param string|null $name The name of the specialized template. Default null.
 * @param array       $args Additional arguments passed to the template. Default array().
 */
function the_loop( array $ps, string $slug, string $name = null, array $args = array() ): void {
	global $post;
	$orig_post = $post;
	foreach ( $ps as $post ) {  // phpcs:ignore
		setup_postdata( $post );
		get_template_part( $slug, $name, $args );
	}
	if ( $orig_post ) {
		$post = $orig_post;  // phpcs:ignore
		setup_postdata( $post );
	} else {
		wp_reset_postdata();
	}
}

/**
 * Displays each page with custom page template.
 *
 * @param \WP_Post[]  $ps   Array of post objects.
 * @param string      $slug The slug name for the generic template.
 * @param string|null $name (Optional) The name of the specialized template. Default null.
 * @param array       $args (Optional) Additional arguments passed to the template. Default array().
 */
function the_loop_with_page_template( array $ps, string $slug, ?string $name = null, array $args = array() ): void {
	global $post;
	$orig_post = $post;
	foreach ( $ps as $post ) {  // phpcs:ignore
		setup_postdata( $post );
		$t = get_page_template_slug();
		$n = ( empty( $t ) || 'default' === $t ) ? $name : basename( $t, '.php' );
		get_template_part( $slug, $n, $args );
	}
	if ( $orig_post ) {
		$post = $orig_post;  // phpcs:ignore
		setup_postdata( $post );
	} else {
		wp_reset_postdata();
	}
}


// -----------------------------------------------------------------------------


/**
 * Adds post type query.
 *
 * @param string $post_type     Post type.
 * @param int    $post_per_page Posts per page.
 * @param array  $args          Arguments for get_posts.
 * @return array Arguments.
 */
function add_post_type_query( string $post_type, int $post_per_page, array $args = array() ): array {
	return $args + array(
		'posts_per_page' => $post_per_page,
		'post_type'      => $post_type,
	);
}

/**
 * Adds taxonomy query.
 *
 * @param string          $taxonomy    Taxonomy.
 * @param string|string[] $term_slug_s Array of term slugs or a term slug.
 * @param array           $args        Arguments for get_posts.
 * @return array Arguments.
 */
function add_tax_query( string $taxonomy, $term_slug_s, array $args = array() ): array {
	if ( ! is_array( $term_slug_s ) ) {
		$term_slug_s = array_map( '\trim', explode( ',', $term_slug_s ) );
	}
	$args['tax_query']   = $args['tax_query'] ?? array();  // phpcs:ignore
	$args['tax_query'][] = array(
		'taxonomy' => $taxonomy,
		'field'    => 'slug',
		'terms'    => $term_slug_s,
	);
	return $args;
}

/**
 * Adds taxonomy query with terms of a specific post.
 *
 * @param string       $taxonomy Taxonomy.
 * @param int|\WP_Post $post     Post ID or object.
 * @param array        $args     Arguments for get_posts.
 * @return array Arguments.
 */
function add_tax_query_with_term_of( string $taxonomy, $post, array $args = array() ): array {
	$ts = get_the_terms( $post, $taxonomy );
	if ( ! is_array( $ts ) ) {
		return $args;
	}
	$args['tax_query']   = $args['tax_query'] ?? array();  // phpcs:ignore
	$args['tax_query'][] = array(
		'taxonomy' => $taxonomy,
		'field'    => 'slug',
		'terms'    => array_column( $ts, 'slug' ),
	);
	return $args;
}

/**
 * Adds custom sticky post query.
 *
 * @param array $args Arguments for get_posts.
 * @return array Arguments.
 */
function add_custom_sticky_query( array $args = array() ): array {
	$args['meta_query']   = $args['meta_query'] ?? array();  // phpcs:ignore
	$args['meta_query'][] = array(
		'key'   => '_sticky',
		'value' => '1',
	);
	return $args;
}

/**
 * Adds upcoming event post query.
 *
 * @param int   $offset_year  Offset of year. Default 0.
 * @param int   $offset_month Offset of month. Default 0.
 * @param int   $offset_day   Offset of day. Default 0.
 * @param array $args         Arguments for get_posts.
 * @return array Arguments.
 */
function add_upcoming_post_query( int $offset_year = 1, int $offset_month = 0, int $offset_day = 0, array $args = array() ): array {
	$today = create_date_string_of_today();
	$limit = create_date_string_of_today( $offset_year, $offset_month, $offset_day );

	$qs   = $args['meta_query'] ?? array();
	$qs[] = array(
		'key'     => event\PMK_DATE_TO,
		'value'   => $today,
		'type'    => 'DATE',
		'compare' => '>=',
	);
	$qs[] = array(
		'key'     => event\PMK_DATE_FROM,
		'value'   => $limit,
		'type'    => 'DATE',
		'compare' => '<=',
	);

	$args['meta_query'] = $qs + array( 'relation' => 'AND' );  // phpcs:ignore
	$args['order']      = 'ASC';
	return $args;
}


// -----------------------------------------------------------------------------


/**
 * Adds page query.
 *
 * @param array $args Arguments for get_posts.
 * @return array Arguments.
 */
function add_page_query( array $args = array() ): array {
	return $args + array(
		'posts_per_page' => -1,
		'post_type'      => 'page',
		'orderby'        => 'menu_order',
		'order'          => 'asc',
	);
}

/**
 * Adds child page query.
 *
 * @param int|null $parent_id Page ID of the parent page.
 * @param array    $args      Arguments for get_posts.
 * @return array Arguments.
 */
function add_child_page_query( ?int $parent_id = null, array $args = array() ): array {
	$args = add_page_query( $args );
	return $args + array( 'post_parent' => $parent_id ?? get_the_ID() );
}

/**
 * Adds sibling page query.
 *
 * @param int|null $sibling_id Page ID of the sibling page.
 * @param array    $args       Arguments for get_posts.
 * @return array Arguments.
 */
function add_sibling_page_query( ?int $sibling_id = null, array $args = array() ): array {
	$post      = get_post( $sibling_id ?? get_the_ID() );
	$parent_id = $post ? $post->post_parent : 0;
	$args      = add_page_query( $args );
	return $args + array( 'post_parent' => $parent_id );
}


// -----------------------------------------------------------------------------


/**
 * Adds post objects.
 *
 * @param \WP_Post[] $augend Array of post objects to which others are added.
 * @param \WP_Post[] $addend Array of post objects which are added to others.
 * @param int|null   $count  Counts of total number.
 * @return \WP_Post[] Array of post objects.
 */
function add_posts( array $augend, array $addend, ?int $count = null ): array {
	$augend_ips = array_column( $augend, null, 'ID' );
	$addend_ips = array_column( $addend, null, 'ID' );

	$ret = array_values( $augend_ips + $addend_ips );
	if ( 0 < $count ) {
		array_splice( $ret, $count );
	}
	return $ret;
}
