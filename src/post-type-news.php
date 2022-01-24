<?php
/**
 * News Post Type
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2022-01-23
 */

namespace wpinc\post\news;

require_once __DIR__ . '/post-type.php';
require_once __DIR__ . '/list-table-column.php';

/**
 * Registers news-like post type.
 *
 * @param string $post_type Post type.
 * @param string $slug      Parma struct base.
 * @param array  $labels    Labels.
 * @param array  $args      Arguments for register_post_type().
 */
function register_post_type( string $post_type = 'news', string $slug = '', array $labels = array(), array $args = array() ): void {
	if ( empty( $slug ) ) {
		$slug = $post_type;
	}
	$args += array(
		'public'        => true,
		'show_ui'       => true,
		'menu_position' => 5,
		'menu_icon'     => 'dashicons-admin-post',
		'supports'      => array( 'title', 'editor', 'revisions', 'thumbnail' ),
		'has_archive'   => true,
		'rewrite'       => false,
		'labels'        => $labels + array( 'name' => 'News' ),
	);
	\register_post_type( $post_type, $args );
	\wpinc\post\add_rewrite_rules( $post_type, $slug, 'date', false );
}

/**
 * Sets columns of list table.
 *
 * @param string $post_type Post type.
 * @param bool   $add_cat   Whether to add {$post_type}_category taxonomy.
 * @param bool   $add_tag   Whether to add {$post_type}_tag taxonomy.
 */
function set_admin_column( string $post_type, bool $add_cat, bool $add_tag ): void {
	add_action(
		'wp_loaded',
		function () use ( $post_type, $add_cat, $add_tag ) {
			$cs = array( 'cb', 'title' );
			if ( $add_cat ) {
				$cs[] = array(
					'taxonomy' => "{$post_type}_category",
					'width'    => '10%',
				);
			}
			if ( $add_tag ) {
				$cs[] = array(
					'taxonomy' => "{$post_type}_tag",
					'width'    => '10%',
				);
			}
			$cs[] = 'date';
			set_list_table_column( $post_type, $cs );
		}
	);
}
