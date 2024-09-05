<?php
/**
 * Custom Date
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2024-09-05
 */

declare(strict_types=1);

namespace wpinc\post;

/**
 * Makes custom date sortable.
 *
 * @global \wpdb $wpdb
 *
 * @param string $post_type    Post type.
 * @param string $slug         Slug.
 * @param string $meta_key     Meta key.
 * @param bool   $pass_through Whether or not pass through posts without custom date.
 */
function make_custom_date_sortable( string $post_type, string $slug, string $meta_key, bool $pass_through = false ): void {
	add_action(
		'pre_get_posts',
		function ( \WP_Query $query ) use ( $post_type, $slug, $meta_key, $pass_through ) {
			if ( is_admin() ) {
				return;
			}
			if ( isset( $query->query_vars['post_type'] ) && $query->query_vars['post_type'] === $post_type ) {
				if ( '' !== $query->get( $slug ) ) {
					$year = $query->get( 'year' );
					if ( '' !== $year ) {
						$query->set( $slug . '_year', $year );
						$query->set( 'year', null );
					}
					$monthnum = $query->get( 'monthnum' );
					if ( '' !== $monthnum ) {
						$query->set( $slug . '_monthnum', $monthnum );
						$query->set( 'monthnum', null );
					}
					$day = $query->get( 'day' );
					if ( '' !== $day ) {
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

				if ( $pass_through ) {
					$mq['relation'] = 'OR';
					$mq[]           = array(
						'key'     => $meta_key,
						'compare' => 'NOT EXISTS',
					);
				}

				$query->set( 'meta_query', $mq );

				$order = $query->get( 'order', 'DESC' );
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
			if ( $post_type === $query->get( 'post_type' ) && '' !== $query->get( $slug ) ) {
				$year = $query->get( $slug . '_year' );
				if ( '' !== $year ) {
					$where .= $wpdb->prepare( " AND ( YEAR( CAST( $wpdb->postmeta.meta_value AS DATE ) ) = %d )", $year );
				}
				$monthnum = $query->get( $slug . '_monthnum' );
				if ( '' !== $monthnum ) {
					$where .= $wpdb->prepare( " AND ( MONTH( CAST( $wpdb->postmeta.meta_value AS DATE ) ) = %d )", $monthnum );
				}
				$day = $query->get( $slug . '_day' );
				if ( '' !== $day ) {
					$where .= $wpdb->prepare( " AND ( DAY( CAST( $wpdb->postmeta.meta_value AS DATE ) ) = %d )", $day );
				}
			}
			return $where;
		},
		10,
		2
	);
	if ( $pass_through ) {
		add_filter(
			'posts_orderby',
			function ( $orderby, $query ) use ( $post_type ) {
				global $wpdb;
				if ( is_admin() || ! $query->is_main_query() ) {
					return $orderby;
				}
				if ( $post_type === $query->get( 'post_type' ) ) {
					$order   = $query->get( 'order', 'DESC' );
					$orderby = "CASE WHEN CAST($wpdb->postmeta.meta_value AS DATE) IS NULL THEN $wpdb->posts.post_date ELSE CAST($wpdb->postmeta.meta_value AS DATE) END $order, $wpdb->posts.post_date $order";
				}
				return $orderby;
			},
			10,
			2
		);
	}
}

/**
 * Enables adjacent post links by custom date
 *
 * @global \wpdb $wpdb
 *
 * @param string $post_type    Post type.
 * @param string $meta_key     Meta key.
 * @param bool   $pass_through Whether or not pass through posts without custom date.
 */
function enable_custom_date_adjacent_post_link( string $post_type, string $meta_key, bool $pass_through = false ): void {
	add_filter(
		'get_next_post_join',
		function ( $join, $_in_same_term, $_excluded_terms, $_tx, $post ) use ( $post_type, $pass_through, $meta_key ) {
			global $wpdb;
			if ( $post->post_type === $post_type ) {
				if ( $pass_through ) {
					$join .= " LEFT JOIN $wpdb->postmeta ON ( p.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key = '$meta_key' )";
				} else {
					$join .= " INNER JOIN $wpdb->postmeta ON ( p.ID = $wpdb->postmeta.post_id )";
				}
			}
			return $join;
		},
		10,
		5
	);
	add_filter(
		'get_next_post_where',
		function ( $where, $_in_same_term, $_excluded_terms, $_tx, $post ) use ( $post_type, $pass_through, $meta_key ) {
			global $wpdb;
			if ( $post->post_type === $post_type ) {
				$m = get_post_meta( $post->ID, $meta_key, true );
				$m = is_string( $m ) ? $m : '';

				if ( $pass_through ) {
					if ( ! $m ) {
						$m     = explode( ' ', $post->post_date )[0];
						$where = preg_replace( '/(p.post_date [><] \'.*\') AND/U', "( ( $wpdb->postmeta.meta_value = '$m' AND $1 ) OR ( $wpdb->postmeta.meta_value > '$m' ) OR ( CAST($wpdb->postmeta.meta_value AS DATE) IS NULL AND p.post_date > '$post->post_date' ) ) AND", $where ) ?? $where;
					} else {
						$where = preg_replace( '/(p.post_date [><] \'.*\') AND/U', "( ( $wpdb->postmeta.meta_value = '$m' AND $1 ) OR ( $wpdb->postmeta.meta_value > '$m' ) OR ( CAST($wpdb->postmeta.meta_value AS DATE) IS NULL AND p.post_date > '$m' ) ) AND", $where ) ?? $where;
					}
				} else {
					$where = preg_replace( '/(p.post_date [><] \'.*\') AND/U', "( $wpdb->postmeta.meta_key = '$meta_key' ) AND ( ( $wpdb->postmeta.meta_value = '$m' AND $1 ) OR ( $wpdb->postmeta.meta_value > '$m' ) ) AND", $where ) ?? $where;
				}
			}
			return $where;
		},
		10,
		5
	);
	add_filter(
		'get_next_post_sort',
		function ( $sort, $post ) use ( $post_type, $pass_through ) {
			global $wpdb;
			if ( $post->post_type === $post_type ) {
				if ( $pass_through ) {
					$sort = "GROUP BY p.ID ORDER BY CASE WHEN CAST($wpdb->postmeta.meta_value AS DATE) IS NULL THEN p.post_date ELSE CAST($wpdb->postmeta.meta_value AS DATE) END ASC, p.post_date ASC";
				} else {
					$sort = str_replace( 'ORDER BY', "ORDER BY CAST($wpdb->postmeta.meta_value AS DATE) ASC,", $sort );
				}
			}
			return $sort;
		},
		10,
		2
	);

	add_filter(
		'get_previous_post_join',
		function ( $join, $_in_same_term, $_excluded_terms, $_tx, $post ) use ( $post_type, $pass_through, $meta_key ) {
			global $wpdb;
			if ( $post->post_type === $post_type ) {
				if ( $pass_through ) {
					$join .= " LEFT JOIN $wpdb->postmeta ON ( p.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key = '$meta_key' )";
				} else {
					$join .= " INNER JOIN $wpdb->postmeta ON ( p.ID = $wpdb->postmeta.post_id )";
				}
			}
			return $join;
		},
		10,
		5
	);
	add_filter(
		'get_previous_post_where',
		function ( $where, $_in_same_term, $_excluded_terms, $_tx, $post ) use ( $post_type, $pass_through, $meta_key ) {
			global $wpdb;
			if ( $post->post_type === $post_type ) {
				$m = get_post_meta( $post->ID, $meta_key, true );
				$m = is_string( $m ) ? $m : '';

				if ( $pass_through ) {
					if ( ! $m ) {
						$m     = explode( ' ', $post->post_date )[0];
						$where = preg_replace( '/(p.post_date [><] \'.*\') AND/U', "( ( $wpdb->postmeta.meta_value = '$m' AND $1 ) OR ( $wpdb->postmeta.meta_value < '$m' ) OR ( CAST( $wpdb->postmeta.meta_value AS DATE ) IS NULL AND p.post_date < '$post->post_date' ) ) AND", $where ) ?? $where;
					} else {
						$where = preg_replace( '/(p.post_date [><] \'.*\') AND/U', "( ( $wpdb->postmeta.meta_value = '$m' AND $1 ) OR ( $wpdb->postmeta.meta_value < '$m' ) OR ( CAST( $wpdb->postmeta.meta_value AS DATE ) IS NULL AND p.post_date < '$m' ) ) AND", $where ) ?? $where;
					}
				} else {
					$where = preg_replace( '/(p.post_date [><] \'.*\') AND/U', "( $wpdb->postmeta.meta_key = '$meta_key' ) AND ( ( $wpdb->postmeta.meta_value = '$m' AND $1 ) OR ( $wpdb->postmeta.meta_value < '$m' ) ) AND", $where ) ?? $where;
				}
			}
			return $where;
		},
		10,
		5
	);
	add_filter(
		'get_previous_post_sort',
		function ( $sort, $post ) use ( $post_type, $pass_through ) {
			global $wpdb;
			if ( $post->post_type === $post_type ) {
				if ( $pass_through ) {
					$sort = "GROUP BY p.ID ORDER BY CASE WHEN CAST($wpdb->postmeta.meta_value AS DATE) IS NULL THEN p.post_date ELSE CAST($wpdb->postmeta.meta_value AS DATE) END DESC, p.post_date DESC";
				} else {
					$sort = str_replace( 'ORDER BY', "ORDER BY CAST($wpdb->postmeta.meta_value AS DATE) DESC,", $sort );
				}
			}
			return $sort;
		},
		10,
		2
	);
}
