<?php
/**
 * News Post Type
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2024-02-21
 */

declare(strict_types=1);

namespace wpinc\post\news;

require_once __DIR__ . '/post-type.php';
require_once __DIR__ . '/list-table-column.php';

/** phpcs:ignore
 * Registers news-like post type.
 *
 * @psalm-suppress ArgumentTypeCoercion
 * phpcs:ignore
 * @param string|array{
 *     post_type?   : string,
 *     slug?        : string,
 *     by_post_name?: bool,
 *     labels?      : array{ name: string },
 * } $args Arguments.
 * @param string                $slug   (Deprecated) Parma struct base.
 * @param array<string, string> $labels (Deprecated) Labels.
 * @param array<string, mixed>  $a      (Deprecated) Arguments for register_post_type().
 */
function register_post_type( $args = array(), string $slug = '', array $labels = array(), array $a = array() ): void {
	if ( is_string( $args ) ) {
		$args = $a + array(
			'post_type' => $args,
			'slug'      => $slug,
			'labels'    => $labels,
		);
	}

	$def_opts = array(  // Keys removed when $args is passed to register_post_type.
		'post_type'    => 'news',
		'slug'         => '',
		'by_post_name' => false,
	);

	$args += $def_opts;
	$args += array(
		'public'        => true,
		'show_in_rest'  => true,
		'has_archive'   => true,
		'rewrite'       => false,
		'menu_position' => 5,
		'menu_icon'     => 'dashicons-admin-post',
		'supports'      => array( 'title', 'editor', 'revisions', 'thumbnail', 'custom-fields' ),
		'labels'        => array(),
	);

	$args['labels'] += array(
		'name' => _x( 'News', 'post type news', 'wpinc_post' ),
	);

	if ( empty( $args['slug'] ) ) {
		$args['slug'] = $args['post_type'];
	}
	\register_post_type( $args['post_type'], array_diff_key( $args, $def_opts ) );  // @phpstan-ignore-line
	\wpinc\post\add_rewrite_rules( $args['post_type'], $args['slug'], 'date', $args['by_post_name'] );  // @phpstan-ignore-line
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
			\wpinc\post\set_list_table_column( $post_type, $cs );
		}
	);
}
