<?php
/**
 * Custom Post Type Utilities
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2024-03-13
 */

declare(strict_types=1);

namespace wpinc\post;

/**
 * Adds rewrite rules for custom post types.
 *
 * @param string $post_type    Post type.
 * @param string $slug         Parma struct base.
 * @param string $date_slug    Date archive slug.
 * @param bool   $by_post_name Whether to use post name for URL.
 */
function add_rewrite_rules( string $post_type, string $slug = '', string $date_slug = 'date', bool $by_post_name = false ): void {
	add_post_type_rewrite_rules( $post_type, $slug, $by_post_name );
	add_post_type_link_filter( $post_type, $by_post_name );
	add_archive_rewrite_rules( $post_type, $slug );
	add_archive_link_filter( $post_type, $slug );
	add_date_archive_rewrite_rules( $post_type, $slug, $date_slug );
	add_date_archive_link_filter( $post_type, $slug, $date_slug );
}


// -----------------------------------------------------------------------------


/**
 * Adds single page rewrite rules.
 *
 * @param string $post_type    Post type.
 * @param string $slug         Struct base.
 * @param bool   $by_post_name Whether to use post name for URL.
 */
function add_post_type_rewrite_rules( string $post_type, string $slug = '', bool $by_post_name = false ): void {
	if ( $by_post_name ) {
		$regex = '([^/]+)';
		$query = "post_type=$post_type&name=";
	} else {
		$regex = '([0-9]+)';
		$query = "post_type=$post_type&p=";
	}
	add_rewrite_tag( "%$post_type%", $regex, $query );

	$slug = ( '' === $slug ) ? $post_type : $slug;
	add_permastruct( $post_type, "/$slug/%{$post_type}%", array( 'with_front' => false ) );

	if ( post_type_supports( $post_type, 'comments' ) ) {
		$cr = 'comment-page-([0-9]{1,})';
		add_rewrite_rule( "$slug/$regex/$cr/?$", "index.php?$query" . '$matches[1]&cpage=$matches[2]', 'top' );
	}
}

/**
 * Adds filter for post type links.
 * For making pretty link of custom post types.
 *
 * @global \WP_Rewrite $wp_rewrite
 *
 * @param string $post_type    Post type.
 * @param bool   $by_post_name Whether to use post name for URL.
 */
function add_post_type_link_filter( string $post_type, bool $by_post_name = false ): void {
	add_filter(
		'post_type_link',
		function ( string $link, \WP_Post $post ) use ( $post_type, $by_post_name ) {
			global $wp_rewrite;

			if ( $post->post_type !== $post_type ) {
				return $link;
			}
			$ps = $wp_rewrite->get_extra_permastruct( $post_type );
			if ( $by_post_name ) {
				$link = str_replace( "%$post_type%", $post->post_name, $ps );
			} else {
				$link = str_replace( "%$post_type%", (string) $post->ID, $ps );
			}
			/**
			 * Link URL.
			 *
			 * @var string $link
			 */
			return home_url( user_trailingslashit( $link ) );
		},
		1,
		2
	);
}


// -----------------------------------------------------------------------------


/**
 * Adds archive rewrite rules.
 * Need to set 'has_archive => true' when registering the post type.
 *
 * @global \WP_Rewrite $wp_rewrite
 *
 * @param string $post_type Post type.
 * @param string $slug      Archive slug.
 */
function add_archive_rewrite_rules( string $post_type, string $slug = '' ): void {
	global $wp_rewrite;
	$slug = $wp_rewrite->root . ( ( '' === $slug ) ? $post_type : $slug );

	add_rewrite_rule( "$slug/?$", "index.php?post_type=$post_type", 'top' );
	add_rewrite_rule( "$slug/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$", "index.php?post_type=$post_type" . '&paged=$matches[1]', 'top' );

	if ( $wp_rewrite->feeds ) {
		$feeds = '(' . trim( implode( '|', $wp_rewrite->feeds ) ) . ')';
		add_rewrite_rule( "$slug/feed/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]', 'top' );
		add_rewrite_rule( "$slug/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]', 'top' );
	}
}

/**
 * Adds archive link filter.
 *
 * @global \WP_Rewrite $wp_rewrite
 *
 * @param string $post_type Post type.
 * @param string $slug      Archive slug.
 */
function add_archive_link_filter( string $post_type, string $slug = '' ): void {
	global $wp_rewrite;
	$slug = $wp_rewrite->root . ( ( '' === $slug ) ? $post_type : $slug );
	$link = home_url( user_trailingslashit( $slug, 'post_type_archive' ) );

	add_filter(
		'post_type_archive_link',
		function ( string $l, string $pt ) use ( $link, $post_type ) {
			return ( $pt === $post_type ) ? $link : $l;
		},
		10,
		2
	);
}


// -----------------------------------------------------------------------------


/**
 * Adds date archive rewrite rules.
 *
 * @param string $post_type Post type.
 * @param string $slug      Archive slug.
 * @param string $date_slug Date slug.
 */
function add_date_archive_rewrite_rules( string $post_type, string $slug = '', string $date_slug = 'date' ): void {
	$slug = ( '' === $slug ) ? $post_type : $slug;
	$tag  = "%{$post_type}_{$date_slug}%";
	$name = "{$post_type}_{$date_slug}";

	add_rewrite_tag( $tag, '([0-9]{4})', "post_type=$post_type&year=" );
	add_permastruct( $name, "/$slug/$date_slug/$tag/%monthnum%/%day%", array( 'with_front' => false ) );
}

/**
 * Adds date archive link filter.
 *
 * @global \WP_Rewrite $wp_rewrite
 *
 * @param string $post_type Post type.
 * @param string $slug      Archive slug.
 * @param string $date_slug Date slug.
 */
function add_date_archive_link_filter( string $post_type, string $slug = '', string $date_slug = 'date' ): void {
	global $wp_rewrite;
	$slug = $wp_rewrite->root . ( ( '' === $slug ) ? $post_type : $slug );

	add_filter(
		'get_archives_link',
		function ( $link_html, $url ) use ( $post_type, $slug, $date_slug ) {
			$url_post_type = get_query_arg( 'post_type', $url );
			if ( $post_type !== $url_post_type ) {
				return $link_html;
			}
			global $wp_rewrite;
			$url = str_replace( $wp_rewrite->root, '', $url );

			$new_url = remove_query_arg( 'post_type', $url );
			$new_url = str_replace( "/$date_slug/", '/%struct%/', $new_url );
			$new_url = str_replace( '%struct%', "$slug/$date_slug", $new_url );

			return str_replace( $url, $new_url, $link_html );
		},
		10,
		2
	);
}

/**
 * Retrieves an item from a query string.
 *
 * @param string $key Query key.
 * @param string $url URL.
 * @return string A query value.
 */
function get_query_arg( string $key, string $url ): string {
	$query = wp_parse_url( $url, PHP_URL_QUERY );
	if ( is_string( $query ) ) {
		$qps = explode( '&', $query );
		foreach ( $qps as $qp ) {
			$key_val = explode( '=', $qp );
			if ( 2 === count( $key_val ) && $key === $key_val[0] ) {
				return $key_val[1];
			}
		}
	}
	return '';
}
