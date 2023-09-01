<?php
/**
 * Custom Date
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2023-09-01
 */

namespace wpinc\post;

/**
 * Makes custom date sortable.
 *
 * @param string $post_type Post type.
 * @param string $slug      Slug.
 * @param string $meta_key  Meta key.
 */
function make_custom_date_sortable( string $post_type, string $slug, string $meta_key ): void {
	add_action(
		'pre_get_posts',
		function ( \WP_Query $query ) use ( $post_type, $slug, $meta_key ) {
			if ( is_admin() ) {
				return;
			}
			if ( isset( $query->query_vars['post_type'] ) && $query->query_vars['post_type'] === $post_type ) {
				if ( $query->get( $slug, false ) !== false ) {
					$year = $query->get( 'year' );
					if ( ! empty( $year ) ) {
						$query->set( $slug . '_year', $year );
						$query->set( 'year', null );
					}
					$monthnum = $query->get( 'monthnum' );
					if ( ! empty( $monthnum ) ) {
						$query->set( $slug . '_monthnum', $monthnum );
						$query->set( 'monthnum', null );
					}
					$day = $query->get( 'day' );
					if ( ! empty( $day ) ) {
						$query->set( $slug . '_day', $day );
						$query->set( 'day', null );
					}
				}
				$mq_key = "meta_$meta_key";

				$mq = $query->get( 'meta_query' );
				if ( ! is_array( $mq ) ) {
					$mq = array();
				}
				$mq[ $mq_key ] = array(
					'key'  => $meta_key,
					'type' => 'date',
				);
				$query->set( 'meta_query', $mq );

				$order = $query->get( 'order' );
				$query->set(
					'orderby',
					array(
						$mq_key => $order,
						'date'  => $order,
					)
				);
			}
		}
	);
	add_filter(
		'posts_where',
		function ( $where, $query ) use ( $post_type, $slug ) {
			global $wpdb;
			if ( is_admin() || ! $query->is_main_query() ) {
				return $where;
			}
			if ( $post_type === $query->get( 'post_type' ) && $query->get( $slug, false ) !== false ) {
				$year = $query->get( $slug . '_year', false );
				if ( false !== $year ) {
					$where .= $wpdb->prepare( " AND ( YEAR( CAST( $wpdb->postmeta.meta_value AS DATE ) ) = %d )", $year );
				}
				$monthnum = $query->get( $slug . '_monthnum', false );
				if ( false !== $monthnum ) {
					$where .= $wpdb->prepare( " AND ( MONTH( CAST( $wpdb->postmeta.meta_value AS DATE ) ) = %d )", $monthnum );
				}
				$day = $query->get( $slug . '_day', false );
				if ( false !== $day ) {
					$where .= $wpdb->prepare( " AND ( DAY( CAST( $wpdb->postmeta.meta_value AS DATE ) ) = %d )", $day );
				}
			}
			return $where;
		},
		10,
		2
	);
}

/**
 * Enables adjacent post links by custom date
 *
 * @param string $post_type Post type.
 * @param string $meta_key  Meta key.
 */
function enable_custom_date_adjacent_post_link( string $post_type, string $meta_key ): void {
	add_filter(
		'get_next_post_join',
		function ( $join, $in_same_term, $excluded_terms, $tx, $post ) use ( $post_type ) {
			global $wpdb;
			if ( $post->post_type === $post_type ) {
				$join .= " INNER JOIN $wpdb->postmeta ON ( p.ID = $wpdb->postmeta.post_id )";
			}
			return $join;
		},
		10,
		5
	);
	add_filter(
		'get_next_post_where',
		function ( $where, $in_same_term, $excluded_terms, $tx, $post ) use ( $post_type, $meta_key ) {
			global $wpdb;
			if ( $post->post_type === $post_type ) {
				$m     = get_post_meta( $post->ID, $meta_key, true );
				$where = preg_replace( '/(p.post_date [><] \'.*\') AND/U', "( $wpdb->postmeta.meta_key = '$meta_key' ) AND ( ( $wpdb->postmeta.meta_value = '$m' AND $1 ) OR ( $wpdb->postmeta.meta_value > '$m' ) ) AND", $where ) ?? $where;
			}
			return $where;
		},
		10,
		5
	);
	add_filter(
		'get_next_post_sort',
		function ( $sort, $post ) use ( $post_type ) {
			global $wpdb;
			if ( $post->post_type === $post_type ) {
				$sort = str_replace( 'ORDER BY', "ORDER BY CAST($wpdb->postmeta.meta_value AS DATE) ASC,", $sort );
			}
			return $sort;
		},
		10,
		2
	);

	add_filter(
		'get_previous_post_join',
		function ( $join, $in_same_term, $excluded_terms, $tx, $post ) use ( $post_type ) {
			global $wpdb;
			if ( $post->post_type === $post_type ) {
				$join .= " INNER JOIN $wpdb->postmeta ON ( p.ID = $wpdb->postmeta.post_id )";
			}
			return $join;
		},
		10,
		5
	);
	add_filter(
		'get_previous_post_where',
		function ( $where, $in_same_term, $excluded_terms, $tx, $post ) use ( $post_type, $meta_key ) {
			global $wpdb;
			if ( $post->post_type === $post_type ) {
				$m     = get_post_meta( $post->ID, $meta_key, true );
				$where = preg_replace( '/(p.post_date [><] \'.*\') AND/U', "( $wpdb->postmeta.meta_key = '$meta_key' ) AND ( ( $wpdb->postmeta.meta_value = '$m' AND $1 ) OR ( $wpdb->postmeta.meta_value < '$m' ) ) AND", $where ) ?? $where;
			}
			return $where;
		},
		10,
		5
	);
	add_filter(
		'get_previous_post_sort',
		function ( $sort, $post ) use ( $post_type ) {
			global $wpdb;
			if ( $post->post_type === $post_type ) {
				$sort = str_replace( 'ORDER BY', "ORDER BY CAST($wpdb->postmeta.meta_value AS DATE) DESC,", $sort );
			}
			return $sort;
		},
		10,
		2
	);
}
