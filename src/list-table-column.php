<?php
/**
 * List Table Columns
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2024-03-14
 */

declare(strict_types=1);

namespace wpinc\post;

/**
 * Enables to show page slug column.
 * Call this in 'load-edit.php' or 'admin_init' action.
 *
 * @psalm-suppress HookNotFound
 *
 * @param int|null $pos (Optional) Column position.
 */
function enable_page_slug_column( ?int $pos = null ): void {
	if ( ! is_admin() ) {
		return;
	}
	if ( 'page' !== _get_current_post_type() ) {
		return;
	}
	add_filter(
		'manage_pages_columns',
		function ( $cs ) use ( $pos ) {
			if ( ! isset( $cs['slug'] ) ) {
				if ( null === $pos ) {
					$cs['slug'] = __( 'Slug', 'default' );
				} else {
					$i  = array( 'slug', __( 'Slug', 'default' ) );
					$cs = _splice_key_value( $cs, $pos, 0, array( $i ) );
				}
			}
			return $cs;
		}
	);
	add_action(
		'manage_pages_custom_column',
		function ( $col_name, $post_id ) {
			if ( 'slug' === $col_name ) {
				$post = get_post( $post_id );
				if ( $post instanceof \WP_Post ) {
					echo esc_attr( $post->post_name );
				}
			}
		},
		10,
		2
	);
	add_action(
		'admin_print_styles-edit.php',
		function () {
			echo '<style>.fixed .column-slug{width:20%;}</style>';
		},
		10,
		0
	);
}

/**
 * Enables to show menu order column.
 * Call this in 'load-edit.php' or 'admin_init' action.
 *
 * @psalm-suppress HookNotFound
 *
 * @param int|null $pos (Optional) Column position.
 */
function enable_menu_order_column( ?int $pos = null ): void {
	if ( ! is_admin() ) {
		return;
	}
	$pt = _get_current_post_type();
	if ( ! is_string( $pt ) || ! post_type_supports( $pt, 'page-attributes' ) ) {
		return;
	}
	add_filter(
		"manage_edit-{$pt}_columns",
		function ( array $cs ) use ( $pos ) {
			if ( ! isset( $cs['order'] ) ) {
				if ( null === $pos ) {
					$cs['order'] = __( 'Order', 'default' );
				} else {
					$i  = array( 'order', __( 'Order', 'default' ) );
					$cs = _splice_key_value( $cs, $pos, 0, array( $i ) );
				}
			}
			return $cs;
		}
	);
	add_filter(
		"manage_edit-{$pt}_sortable_columns",
		function ( array $cs ) {
			$cs['order'] = 'menu_order';
			return $cs;
		}
	);
	add_action(
		"manage_{$pt}_posts_custom_column",
		function ( string $col_name, int $post_id ) {
			if ( 'order' === $col_name ) {
				$post = get_post( $post_id );
				if ( $post instanceof \WP_Post ) {
					echo esc_attr( (string) $post->menu_order );
				}
			}
		},
		10,
		2
	);
	add_action(
		'admin_print_styles-edit.php',
		function () {
			?>
			<style>
				.fixed .column-order{width:7%;}
				@media screen and (max-width:1100px) {.fixed .column-order{width:12%;}}
			</style>
			<?php
		},
		10,
		0
	);
}

/**
 * Remove a portion of the array and replace it with key-value pairs.
 *
 * @param array<string, mixed>        $arr    The input array.
 * @param int                         $offset Offset.
 * @param int|null                    $length Length.
 * @param array<array{string, mixed}> $pairs  Key-value pairs.
 * @return array<string, mixed> Array consisting of the extracted elements.
 */
function _splice_key_value( array $arr, int $offset, ?int $length = null, array $pairs = array() ): array {
	$kvs = array();
	foreach ( $arr as $key => $val ) {
		$kvs[] = array( $key, $val );
	}
	array_splice( $kvs, $offset, $length ?? count( $kvs ), $pairs );
	$new = array();
	foreach ( $kvs as $kv ) {
		$new[ $kv[0] ] = $kv[1];
	}
	return $new;
}

/**
 * Gets current post type.
 *
 * @global \WP_Post|null $post
 *
 * @return string|null Post type.
 */
function _get_current_post_type(): ?string {
	global $post, $typenow, $current_screen;

	if ( $post && $post->post_type ) {
		return $post->post_type;
	} elseif ( $typenow ) {
		return $typenow;
	} elseif ( $current_screen && $current_screen->post_type ) {
		return $current_screen->post_type;
	} elseif ( is_admin() && isset( $_REQUEST['post_type'] ) && is_string( $_REQUEST['post_type'] ) ) {  // phpcs:ignore
		return sanitize_key( $_REQUEST['post_type'] );  // phpcs:ignore
	}
	return null;
}


// -----------------------------------------------------------------------------


/** phpcs:ignore
 * Sets list table columns for admin.
 * Example,
 * array(
 *     'cb',
 *     'title',
 *     'author',
 *     'date',
 *     'order',
 *     'comments',
 *     array(
 *         'name'  => 'slug',
 *         'label' => 'Slug',
 *         'width' => '10%',
 *     )
 *     array(
 *         'taxonomy' => 'category',
 *         'label'    => 'Category',
 *         'width'    => '10%',
 *     ),
 *     array(
 *         'meta'     > '_date_from',
 *         'type'     > 'NUMERIC',  // meta_type
 *         'filter'   > 'esc_html',
 *         'sortable' => true,
 *         'label'    => 'Date (From)',
 *         'width'    => '10%',
 *     ),
 * )
 *
 * @global string $pagenow
 *
 * @param string $post_type Post type.
 * phpcs:ignore
 * @param (
 *     array{ name: string, label?: string, width?: string }|
 *     array{ taxonomy: string, label?: string, width?: string }|
 *     array{ meta: string, type?: string, filter?: string, sortable?: bool, label?: string, width?: string }|
 *     'cb'|'title'|'author'|'date'|'order'|'comments'
 * )[] $columns Columns.
 */
function set_list_table_column( string $post_type, array $columns ): void {  // phpcs:ignore
	global $pagenow;
	if ( ! is_admin() || ( 'edit.php' !== $pagenow && ! wp_doing_ajax() ) ) {
		return;
	}

	$def_cols = array(
		'cb'       => '<input type="checkbox">',
		'title'    => _x( 'Title', 'column name', 'default' ),
		'author'   => __( 'Author', 'default' ),
		'date'     => __( 'Date', 'default' ),
		'order'    => __( 'Order', 'default' ),
		'slug'     => __( 'Slug', 'default' ),
		'comments' => sprintf(
			'<span class="vers comment-grey-bubble" title="%1$s" aria-hidden="true"></span><span class="screen-reader-text">%2$s</span>',
			esc_attr__( 'Comments', 'default' ),
			__( 'Comments', 'default' )
		),
	);

	$cols    = array();
	$styles  = array();
	$val_fns = array();

	$sortable = array();
	$types    = array();

	foreach ( $columns as $c ) {
		if ( is_array( $c ) ) {
			$name  = '';
			$label = $c['label'] ?? '';

			if ( isset( $c['meta'] ) ) {
				$name = "meta-{$c['meta']}";
				if ( is_callable( $c['filter'] ?? '' ) ) {
					$val_fns[ $name ] = isset( $c['filter'] ) ? $c['filter'] : '';  // @phpstan-ignore-line
				} else {
					$val_fns[ $name ] = '\esc_html';
				}
				if ( $c['sortable'] ?? false ) {
					$sortable[ $name ]   = $c['meta'];
					$types[ $c['meta'] ] = $c['type'] ?? '';
				}
			} elseif ( isset( $c['taxonomy'] ) ) {
				if ( taxonomy_exists( $c['taxonomy'] ) ) {
					$name = "taxonomy-{$c['taxonomy']}";
					if ( '' === $label ) {
						$tx = get_taxonomy( $c['taxonomy'] );
						if ( $tx ) {
							$label = $tx->labels->name;
						}
					}
				}
			} elseif ( isset( $c['name'] ) ) {
				if ( isset( $def_cols[ $c['name'] ] ) ) {
					$name = $c['name'];
				}
			}
			if ( '' !== $name ) {
				$cols[ $name ] = $label;
				if ( isset( $c['width'] ) ) {  // Column Styles.
					$styles[] = ".column-$name {width: {$c['width']} !important;}";
				}
			}
		} else {  // phpcs:ignore
			if ( taxonomy_exists( $c ) ) {
				$tx = get_taxonomy( $c );
				if ( $tx ) {
					$cols[ "taxonomy-$c" ] = $tx->labels->name;
				}
			} else {
				$cols[ $c ] = $def_cols[ $c ];
			}
		}
	}
	/** @psalm-suppress HookNotFound */  // phpcs:ignore
	add_filter(
		"manage_edit-{$post_type}_columns",
		function () use ( $cols ) {
			return $cols;
		}
	);
	add_action(
		'admin_head',
		function () use ( $post_type, $styles ) {
			if ( get_query_var( 'post_type' ) === $post_type ) {
				?>
				<style><?php echo implode( "\n", $styles );  // phpcs:ignore ?></style>
				<?php
			}
		},
		10,
		0
	);
	add_action(
		"manage_{$post_type}_posts_custom_column",
		function ( string $name, int $post_id ) use ( $val_fns ) {
			if ( isset( $val_fns[ $name ] ) ) {
				$val = get_post_meta( $post_id, substr( $name, 5 /* 'meta-' */ ), true );
				$fn  = $val_fns[ $name ];
				echo call_user_func( $fn, $val );  // phpcs:ignore
			}
		},
		10,
		2
	);
	if ( ! empty( $sortable ) ) {
		/** @psalm-suppress HookNotFound */  // phpcs:ignore
		add_filter(
			"manage_edit-{$post_type}_sortable_columns",
			function ( array $cols ) use ( $sortable ) {
				return array_merge( $cols, $sortable );
			}
		);
		add_filter(
			'request',
			function ( $vars ) use ( $types ) {
				if ( ! isset( $vars['orderby'] ) ) {
					return $vars;
				}
				$key = $vars['orderby'];  // Value of cols.
				if ( isset( $types[ $key ] ) ) {
					$orderby = array(
						'meta_key' => $key,  // phpcs:ignore
						'orderby'  => 'meta_value',
					);
					if ( '' !== $types[ $key ] ) {
						$orderby['meta_type'] = $types[ $key ];
					}
					$vars = array_merge( $vars, $orderby );
				}
				return $vars;
			}
		);
	}
}
