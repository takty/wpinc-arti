<?php
/**
 * List Table Columns
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2022-01-23
 */

namespace wpinc\post;

/**
 * Set list table columns for admin.
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
				$val = get_post_meta( $post_id, $name, true );
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
			function ( $vars ) use ( $meta_keys, $types ) {
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
