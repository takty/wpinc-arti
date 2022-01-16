<?php
/**
 * Custom Post Type Utilities
 *
 * @author Takuto Yanagida
 * @version 2021-04-18
 */

namespace st\post_type;

require_once __DIR__ . '/taxonomy.php';
require_once __DIR__ . '/sticky.php';


function post_type_title( $prefix = '', $display = true ) {
	$post_type = get_query_var( 'post_type' );
	if ( is_array( $post_type ) ) {
		$post_type = reset( $post_type );
	}
	$post_type_obj = get_post_type_object( $post_type );
	$title         = apply_filters( 'post_type_archive_title', $post_type_obj->labels->name, $post_type );

	if ( $display ) {
		echo $prefix . $title;
	} else {
		return $prefix . $title;
	}
}


// -----------------------------------------------------------------------------


function add_rewrite_rules( $post_type, $struct = '', $date_slug = 'date', $by_post_name = false, ?callable $home_url = null ) {
	add_post_type_rewrite_rules( $post_type, $struct, $by_post_name );
	add_post_type_link_filter( $post_type, $by_post_name );
	add_archive_rewrite_rules( $post_type, $struct );
	add_archive_link_filter( $post_type, $struct );
	add_date_archive_rewrite_rules( $post_type, $struct, $date_slug );
	add_date_archive_link_filter( $post_type, $struct, $date_slug, $home_url );
}

function add_post_type_rewrite_rules( $post_type, $struct = '', $by_post_name = false ) {
	if ( empty( $struct ) ) {
		$struct = $post_type;
	}
	$tag_paging = '%paging%';
	add_rewrite_tag( $tag_paging, '([0-9]+)', 'page=' );

	if ( $by_post_name ) {
		$tag_slug  = "%{$post_type}_post_slug%";
		$name_slug = "{$post_type}_single_slug";
		add_rewrite_tag( $tag_slug, '(.?.+?)', "post_type=$post_type&name=" );
		add_permastruct( $name_slug, "/$struct/$tag_slug", array( 'with_front' => false ) );
		add_permastruct( "{$name_slug}_page", "/$struct/$tag_slug/$tag_paging", array( 'with_front' => false ) );
	} else {
		$tag_id  = "%{$post_type}_post_id%";
		$name_id = "{$post_type}_single_id";
		add_rewrite_tag( $tag_id, '([0-9]+)', "post_type=$post_type&p=" );
		add_permastruct( $name_id, "/$struct/$tag_id", array( 'with_front' => false ) );
		add_permastruct( "{$name_id}_page", "/$struct/$tag_id/$tag_paging", array( 'with_front' => false ) );
	}
}

function add_post_type_link_filter( $post_type, $by_post_name = false ) {  // for making pretty link of custom post types.
	add_filter(
		'post_type_link',
		function ( $post_link, $id = 0 ) use ( $post_type, $by_post_name ) {
			global $wp_rewrite;

			$post = get_post( $id );
			if ( is_wp_error( $post ) ) {
				return $post;
			}
			if ( $post->post_type !== $post_type ) {
				return $post_link;
			}
			if ( $by_post_name ) {
				$tag_slug  = "%{$post_type}_post_slug%";
				$name_slug = "{$post_type}_single_slug";
				$ps        = $wp_rewrite->get_extra_permastruct( $name_slug );
				$post_link = str_replace( $tag_slug, $post->post_name, $ps );
			} else {
				$tag_id    = "%{$post_type}_post_id%";
				$name_id   = "{$post_type}_single_id";
				$ps        = $wp_rewrite->get_extra_permastruct( $name_id );
				$post_link = str_replace( $tag_id, $post->ID, $ps );
			}
			return home_url( user_trailingslashit( $post_link ) );
		},
		1,
		2
	);
}

function add_archive_rewrite_rules( $post_type, $struct = '' ) {  // need to set 'has_archive => true' when registering the post type.
	global $wp_rewrite;

	if ( empty( $struct ) ) {
		$struct = $post_type;
	}
	$struct = $wp_rewrite->root . $struct;

	add_rewrite_rule( "{$struct}/?$", "index.php?post_type=$post_type", 'top' );
	add_rewrite_rule( "{$struct}/{$wp_rewrite->pagination_base}/([0-9]{1,})/?$", "index.php?post_type=$post_type" . '&paged=$matches[1]', 'top' );

	if ( $wp_rewrite->feeds ) {
		$feeds = '(' . trim( implode( '|', $wp_rewrite->feeds ) ) . ')';
		add_rewrite_rule( "{$struct}/feed/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]', 'top' );
		add_rewrite_rule( "{$struct}/$feeds/?$", "index.php?post_type=$post_type" . '&feed=$matches[1]', 'top' );
	}
}

function add_archive_link_filter( $post_type, $struct = '' ) {
	global $wp_rewrite;

	if ( empty( $struct ) ) {
		$struct = $post_type;
	}
	$struct       = $wp_rewrite->root . $struct;
	$archive_link = home_url( user_trailingslashit( $struct, 'post_type_archive' ) );

	add_filter(
		'post_type_archive_link',
		function ( $link, $pt ) use ( $post_type, $archive_link ) {
			if ( $pt === $post_type ) {
				return $archive_link;
			}
			return $link;
		},
		10,
		2
	);
}

function add_date_archive_rewrite_rules( $post_type, $struct = '', $slug = 'date' ) {
	if ( empty( $struct ) ) {
		$struct = $post_type;
	}
	$tag  = "%{$post_type}_{$slug}_year%";
	$name = "{$post_type}_{$slug}";

	add_rewrite_tag( $tag, '([0-9]{4})', "post_type=$post_type&$slug=1&year=" );
	add_permastruct( $name, "/$struct/$slug/$tag/%monthnum%/%day%", array( 'with_front' => false ) );

	add_filter(
		'query_vars',
		function ( $qvars ) use ( $slug ) {
			if ( ! isset( $qvars[ $slug ] ) ) {
				$qvars[] = $slug;
			}
			return $qvars;
		}
	);
}

function add_date_archive_link_filter( $post_type, $struct = '', $slug = 'date', ?callable $home_url = null ) {
	if ( empty( $struct ) ) {
		$struct = $post_type;
	}
	add_filter(
		'get_archives_link',
		function ( $link_html, $url, $text, $format, $before, $after ) use ( $post_type, $struct, $slug, $home_url ) {
			$url_post_type = '';

			$qps = explode( '&', parse_url( $url, PHP_URL_QUERY ) );
			foreach ( $qps as $qp ) {
				$key_val = explode( '=', $qp );
				if ( count( $key_val ) === 2 && 'post_type' === $key_val[0] ) {
					$url_post_type = $key_val[1];
				}
			}
			if ( $post_type !== $url_post_type ) {
				return $link_html;
			}
			global $wp_rewrite;
			$front   = substr( $wp_rewrite->front, 1 );
			$new_url = str_replace( $front, '', $url );

			$ret_link = str_replace( "/$slug/", "/%link_dir%/$slug/", $new_url );
			$ret_link = str_replace( "%link_dir%/$slug", '%link_dir%', $ret_link );
			$link_dir = $struct . '/' . $slug;

			$new_url = str_replace( '%link_dir%', $link_dir, $ret_link );
			$new_url = remove_query_arg( 'post_type', $new_url );

			return str_replace( $url, $new_url, $link_html );
		},
		10,
		6
	);
}


// -----------------------------------------------------------------------------


function make_custom_date_sortable( $post_type, $slug, $meta_key ) {
	add_action(
		'pre_get_posts',
		function ( $query ) use ( $post_type, $slug, $meta_key ) {
			if ( is_admin() ) {
				return;
			}
			if ( isset( $query->query_vars['post_type'] ) && $query->query_vars['post_type'] === $post_type ) {
				if ( $query->get( $slug, false ) !== false ) {
					$year = $query->get( 'year' );
					if ( ! empty( $year ) ) {
						$query->set( $slug.'_year', $year );
						$query->set( 'year', null );
					}
					$monthnum = $query->get( 'monthnum' );
					if ( ! empty( $monthnum ) ) {
						$query->set( $slug.'_monthnum', $monthnum );
						$query->set( 'monthnum', null );
					}
					$day = $query->get( 'day' );
					if ( ! empty( $day ) ) {
						$query->set( $slug.'_day', $day );
						$query->set( 'day', null );
					}
				}
				$mq_key = "meta_$meta_key";

				$mq = $query->get( 'meta_query' );
				if ( ! is_array( $mq ) ) {
					$mq = array();
				}
				$mq[ $mq_key ] = array( 'key' => $meta_key, 'type' => 'date' );
				$query->set( 'meta_query', $mq );

				$order = $query->get( 'order' );
				$query->set( 'orderby', array( $mq_key => $order, 'date' => $order ) );
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

function enable_custom_date_adjacent_post_link( $post_type, $meta_key ) {
	add_filter(
		'get_next_post_join',
		function ( $join, $in_same_term, $excluded_terms, $taxonomy, $post ) use ( $post_type, $meta_key ) {
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
		function ( $where, $in_same_term, $excluded_terms, $taxonomy, $post ) use ( $post_type, $meta_key ) {
			global $wpdb;
			if ( $post->post_type === $post_type ) {
				$m     = get_post_meta( $post->ID, $meta_key, true );
				$where = preg_replace( '/(p.post_date [><] \'.*\') AND/U', "( $wpdb->postmeta.meta_key = '$meta_key' ) AND ( ( $wpdb->postmeta.meta_value = '$m' AND $1 ) OR ( $wpdb->postmeta.meta_value > '$m' ) ) AND", $where );
			}
			return $where;
		},
		10,
		5
	);
	add_filter(
		'get_next_post_sort',
		function ( $sort, $post ) use ( $post_type, $meta_key ) {
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
		function ( $join, $in_same_term, $excluded_terms, $taxonomy, $post ) use ( $post_type, $meta_key ) {
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
		function ( $where, $in_same_term, $excluded_terms, $taxonomy, $post ) use ( $post_type, $meta_key ) {
			global $wpdb;
			if ( $post->post_type === $post_type ) {
				$m     = get_post_meta( $post->ID, $meta_key, true );
				$where = preg_replace( '/(p.post_date [><] \'.*\') AND/U', "( $wpdb->postmeta.meta_key = '$meta_key' ) AND ( ( $wpdb->postmeta.meta_value = '$m' AND $1 ) OR ( $wpdb->postmeta.meta_value < '$m' ) ) AND", $where );
			}
			return $where;
		},
		10,
		5
	);
	add_filter(
		'get_previous_post_sort',
		function ( $sort, $post ) use ( $post_type, $meta_key ) {
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
