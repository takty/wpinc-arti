<?php
/**
 * List Table Columns
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2022-02-07
 */

namespace wpinc\post;

/**
 * Enables to show page slug column.
 * Call this in 'load-edit.php' or 'admin_init' action.
 *
 * @param int|null $pos (Optional) Column position.
 */
function enable_page_slug_column( ?int $pos = null ) {
	global $typenow;
	if ( 'page' !== $typenow ) {
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
				echo esc_attr( $post->post_name );
			}
		},
		10,
		2
	);
	add_action(
		'admin_print_styles-edit.php',
		function () {
			echo '<style>.fixed .column-slug{width:20%;}</style>';
		}
	);
}

/**
 * Enables to show menu order column.
 * Call this in 'load-edit.php' or 'admin_init' action.
 *
 * @param int|null $pos (Optional) Column position.
 */
function enable_menu_order_column( ?int $pos = null ) {
	global $typenow;
	if ( ! post_type_supports( $typenow, 'page-attributes' ) ) {
		return;
	}
	add_filter(
		"manage_edit-{$typenow}_columns",
		function ( $cs ) use ( $pos ) {
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
		"manage_edit-{$typenow}_sortable_columns",
		function ( $cs ) {
			$cs['order'] = 'menu_order';
			return $cs;
		}
	);
	add_action(
		"manage_{$typenow}_posts_custom_column",
		function ( $col_name, $post_id ) {
			if ( 'order' === $col_name ) {
				$post = get_post( $post_id );
				echo esc_attr( $post->menu_order );
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
		}
	);
}

/**
 * Remove a portion of the array and replace it with key-value pairs.
 *
 * @param array    $array  The input array.
 * @param int      $offset Offset.
 * @param int|null $length Length.
 * @param array    $pairs  Key-value pairs.
 * @return array Array consisting of the extracted elements.
 */
function _splice_key_value( array $array, int $offset, ?int $length = null, array $pairs = array() ): array {
	$kvs = array();
	foreach ( $array as $key => $val ) {
		$kvs[] = array( $key, $val );
	}
	array_splice( $kvs, $offset, $length, $pairs );
	$new = array();
	foreach ( $kvs as $kv ) {
		$new[ $kv[0] ] = $kv[1];
	}
	return $new;
}


// -----------------------------------------------------------------------------


/**
 * Sets list table columns for admin.
 * Example,
 * array(
 *     'cb',
 *     'title',
 *     'date',
 *     array(
 *         'name'  => 'order',
 *         'label' => 'Order',
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
 * @param string $post_type Post type.
 * @param array  $columns   Columns.
 */
function set_list_table_column( string $post_type, array $columns ): void {
	$def_cols = array(
		'cb'     => '<input type="checkbox">',
		'title'  => _x( 'Title', 'column name', 'default' ),
		'author' => __( 'Author', 'default' ),
		'date'   => __( 'Date', 'default' ),
		'order'  => __( 'Order', 'default' ),
		'slug'   => __( 'Slug', 'default' ),
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
					$val_fns[ $name ] = $c['filter'];
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
					if ( empty( $label ) ) {
						$label = get_taxonomy( $c['taxonomy'] )->labels->name;
					}
				}
			} elseif ( isset( $c['name'] ) ) {
				if ( isset( $def_cols[ $c['name'] ] ) ) {
					$name = $c['name'];
				}
			}
			if ( ! empty( $name ) ) {
				$cols[ $name ] = $label;
				if ( isset( $c['width'] ) ) {  // Column Styles.
					$styles[] = ".column-$name {width: {$c['width']} !important;}";
				}
			}
		} else {
			if ( taxonomy_exists( $c ) ) {
				$cols[ "taxonomy-$c" ] = get_taxonomy( $c )->labels->name;
			} else {
				$cols[ $c ] = $def_cols[ $c ];
			}
		}
	}
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
		}
	);
	add_action(
		"manage_{$post_type}_posts_custom_column",
		function ( $name, $post_id ) use ( $val_fns ) {
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
		add_filter(
			"manage_edit-{$post_type}_sortable_columns",
			function ( $cols ) use ( $sortable ) {
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
					if ( ! empty( $types[ $key ] ) ) {
						$orderby['meta_type'] = $types[ $key ];
					}
					$vars = array_merge( $vars, $orderby );
				}
				return $vars;
			}
		);
	}
}
