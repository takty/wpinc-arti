<?php
/**
 * News Post Type
 *
 * @author Takuto Yanagida
 * @version 2021-03-23
 */

namespace st\news;

require_once __DIR__ . '/post-type.php';
require_once __DIR__ . '/../admin/list-table-column.php';


function register_post_type( $post_type = 'news', $slug = false, $labels = array(), $args = array(), ?callable $home_url = null ) {
	$labels = array_merge( array( 'name' => 'News' ), $labels );
	$args   = array_merge(
		array(
			'labels'        => $labels,
			'public'        => true,
			'show_ui'       => true,
			'menu_position' => 5,
			'menu_icon'     => 'dashicons-admin-post',
			'supports'      => array( 'title', 'editor', 'revisions', 'thumbnail' ),
			'has_archive'   => true,
			'rewrite'       => false,
		),
		$args
	);
	if ( false === $slug ) {
		$slug = $post_type;
	}
	\register_post_type( $post_type, $args );
	\st\post_type\add_rewrite_rules( $post_type, $slug, 'date', false, $home_url );
}

function set_admin_columns( $post_type, $add_cat, $add_tag, $tax ) {
	add_action(
		'wp_loaded',
		function () use ( $post_type, $add_cat, $add_tag, $tax )  {
			$cs = \st\list_table_column\insert_default_columns();
			$cs = \st\list_table_column\insert_common_taxonomy_columns( $post_type, $add_cat, $add_tag, -1, $cs );
			array_splice( $cs, -1, 0, array( array( 'name' => $tax, 'width' => '10%' ) ) );
			\st\list_table_column\set_admin_columns( $post_type, $cs );
		}
	);
}
