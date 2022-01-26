<?php
/**
 * Event Post Type
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2022-01-26
 */

namespace wpinc\post\event;

require_once __DIR__ . '/assets/date.php';
require_once __DIR__ . '/assets/duration-picker.php';
require_once __DIR__ . '/post-type.php';
require_once __DIR__ . '/list-table-column.php';

const PMK_DATE      = '_date';
const PMK_DATE_FROM = '_date_from';
const PMK_DATE_TO   = '_date_to';

/**
 * Registers event-like post type.
 *
 * @param array $args Arguments.
 */
function register_post_type( array $args = array() ): void {
	$def_opts = array(
		'post_type'         => 'event',
		'slug'              => '',
		'do_autofill'       => false,
		'order_by'          => 'from',
		'replace_date_with' => 'from',
	);

	$args += $def_opts;
	$args += array(
		'public'        => true,
		'has_archive'   => true,
		'rewrite'       => false,
		'menu_position' => 5,
		'menu_icon'     => 'dashicons-calendar-alt',
		'supports'      => array( 'title', 'editor', 'revisions', 'thumbnail' ),
		'labels'        => array(),
	);

	$args['labels'] += array(
		'name'      => 'Events',
		'date'      => 'Date',
		'date_from' => 'From',
		'date_to'   => 'To',
	);

	if ( empty( $args['slug'] ) ) {
		$args['slug'] = $args['post_type'];
	}
	\register_post_type( $args['post_type'], array_diff_key( $args, $def_opts ) );
	\wpinc\post\add_rewrite_rules( $args['post_type'], $args['slug'], 'date', false );

	_set_custom_date_order( $args['post_type'], $args['order_by'] );
	_replace_date( $args['post_type'], $args['replace_date_with'] );

	if ( is_admin() ) {
		_set_duration_picker( $args );
	} else {
		add_filter(
			'body_class',
			function ( array $classes ) use ( $args ) {
				return _cb_body_class( $classes, $args['post_type'] );
			}
		);
	}
}

/**
 * Sets custom date order.
 *
 * @access private
 *
 * @param string $post_type Post type.
 * @param string $type      Type.
 */
function _set_custom_date_order( string $post_type, string $type ): void {
	$key = '';
	if ( 'from' === $type ) {
		$key = PMK_DATE_FROM;
	}
	if ( 'to' === $type ) {
		$key = PMK_DATE_TO;
	}
	if ( $key ) {
		\wpinc\post\make_custom_date_sortable( $post_type, 'date', $key );
		\wpinc\post\enable_custom_date_adjacent_post_link( $post_type, $key );
	}
}

/**
 * Adds filter for replacing date.
 *
 * @access private
 *
 * @param string $post_type Post type.
 * @param string $type      Type.
 */
function _replace_date( string $post_type, string $type ): void {
	$key = '';
	if ( 'from' === $type ) {
		$key = PMK_DATE_FROM;
	}
	if ( 'to' === $type ) {
		$key = PMK_DATE_TO;
	}
	if ( $key ) {
		add_filter(
			'get_the_date',
			function ( $the_date, $d, $post ) use ( $post_type, $key ) {
				if ( $post->post_type !== $post_type ) {
					return $the_date;
				}
				$date = get_post_meta( $post->ID, $key, true );
				return mysql2date( empty( $d ) ? get_option( 'date_format' ) : $d, $date );
			},
			10,
			3
		);
	}
}

/**
 * Sets duration picker.
 *
 * @param array $args Arguments.
 */
function _set_duration_picker( array $args ): void {
	if ( ! _is_post_type( $args['post_type'] ) ) {
		return;
	}
	$dp_args = array(
		'key'         => PMK_DATE,
		'do_autofill' => $args['do_autofill'],
		'label_from'  => $args['labels']['date_from'],
		'label_to'    => $args['labels']['date_to'],
	);
	\wpinc\post\duration_picker\initialize( $args );
	add_action(
		'admin_menu',
		function () use ( $args, $dp_args ) {
			\wpinc\post\duration_picker\add_meta_box( $dp_args, $args['labels']['date'], $args['post_type'], 'side' );
		}
	);
	add_action(
		'save_post',
		function ( $post_id ) use ( $dp_args ) {
			\wpinc\post\duration_picker\save_meta_box( $dp_args, $post_id );
		}
	);
}

/**
 * Checks current post type.
 *
 * @param string $post_type Post type.
 * @return bool True on the current post type is $post_type.
 */
function _is_post_type( string $post_type ): bool {
	$post    = $_GET['post'] ?? null;  // phpcs:ignore
	$post_id = $_POST['post_ID'] ?? null;  // phpcs:ignore
	if ( $post || $post_id ) {
		$post_id = $post ? $post : $post_id;
	}
	$post_id = intval( $post_id );

	$p = get_post( $post_id );
	if ( $p ) {
		$pt = $p->post_type;
	} else {
		$pt = $_GET['post_type'] ?? '';  // phpcs:ignore
	}
	return $post_type === $pt;
}

/**
 * Callback function for 'body_class' filter.
 *
 * @param string[] $classes   Array of classes.
 * @param string   $post_type Post type.
 * @return string[] Classes.
 */
function _cb_body_class( array $classes, string $post_type ): array {
	if ( is_singular( $post_type ) ) {
		global $wp_query;
		$post      = $wp_query->get_queried_object();
		$classes[] = _get_duration_state( $post->ID );
	}
	return $classes;
}


// -----------------------------------------------------------------------------


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
			$cs   = add_duration_column( $post_type, $cs );
			$cs[] = 'date';
			set_list_table_column( $post_type, $cs );
		}
	);
}

/**
 * Adds event duration columns.
 *
 * @param string $post_type Post type.
 * @param array  $cs        Columns.
 * @return array Added columns.
 */
function add_duration_column( string $post_type, array $cs = array() ): array {
	$pto       = get_post_type_object( $post_type );
	$label_bgn = $pto->labels->duration_from ?? __( 'From' );
	$label_end = $pto->labels->duration_to ?? __( 'To' );

	$cs[] = array(
		'meta'     => PMK_DATE_FROM,
		'label'    => $label_bgn,
		'width'    => '15%',
		'filter'   => '\wpinc\post\event\_filter_date_val',
		'sortable' => true,
	);
	$cs[] = array(
		'meta'     => PMK_DATE_TO,
		'label'    => $label_end,
		'width'    => '15%',
		'filter'   => '\wpinc\post\event\_filter_date_val',
		'sortable' => true,
	);
	return $cs;
}

/**
 * Filter of duration columns.
 *
 * @param string $val Value.
 */
function _filter_date_val( string $val ): string {
	if ( empty( $val ) ) {
		return '';
	}
	return esc_attr( gmdate( get_option( 'date_format' ), strtotime( $val ) ) );
}


// -----------------------------------------------------------------------------


/**
 * Formats duration date.
 *
 * @param int    $post_id       Post ID.
 * @param array  $formats       Array of duration formats.
 * @param string $date_format   Date format.
 * @param bool   $do_translate  Whether to translate.
 * @return string Formatted duration.
 */
function format_duration( int $post_id, array $formats, string $date_format, bool $do_translate ): string {
	$dd = _get_duration_dates( $post_id );
	$df = implode( "\t", str_split( $date_format, 1 ) );

	if ( $dd['from_ns'] && $dd['to_ns'] ) {
		$from_fd = _split_date_string( $dd['from_raw'], $df, $do_translate );
		$to_fd   = _split_date_string( $dd['to_raw'], $df, $do_translate );

		$type = 'one';
		if ( $dd['from_ns'][0] !== $dd['to_ns'][0] ) {
			$type = 'ymd';
		} elseif ( $dd['from_ns'][1] !== $dd['to_ns'][1] ) {
			$type = 'md';
		} elseif ( $dd['from_ns'][2] !== $dd['to_ns'][2] ) {
			$type = 'd';
		}
		return sprintf( $formats[ $type ], ...$from_fd, ...$to_fd );
	} elseif ( $dd['from_ns'] || $dd['to_ns'] ) {
		$fd = _split_date_string( $dd['from_raw'] ? $dd['from_raw'] : $dd['to_raw'], $df, $do_translate );

		return sprintf( $formats['one'], ...$fd );
	}
	return '';
}

/**
 * Splits date string.
 *
 * @param string $str          String.
 * @param string $df           Date format where each components are separated '\t'.
 * @param bool   $do_translate Whether to translate.
 * @return string[] Date components.
 */
function _split_date_string( string $str, string $df, bool $do_translate ): array {
	$fd = $str ? explode( "\t", mysql2date( $df, $str, $do_translate ) ) : array();
	return array_pad( $fd, 4, '' );
}

/**
 * Retrieves duration date.
 *
 * @param int $post_id Post ID.
 * @return array Array of duration dates.
 */
function _get_duration_dates( int $post_id ): array {
	$from_raw = get_post_meta( $post_id, PMK_DATE_FROM, true );
	$to_raw   = get_post_meta( $post_id, PMK_DATE_TO, true );
	$from_ns  = empty( $from_raw ) ? null : explode( '-', $from_raw );
	$to_ns    = empty( $to_raw ) ? null : explode( '-', $to_raw );
	return compact( 'from_raw', 'to_raw', 'from_ns', 'to_ns' );
}

/**
 * Retrieves duration state.
 *
 * @param int $post_id Post ID.
 * @return string      Duration state.
 */
function _get_duration_state( int $post_id ): string {
	$from_raw = get_post_meta( $post_id, PMK_DATE_FROM, true );
	$to_raw   = get_post_meta( $post_id, PMK_DATE_TO, true );
	$from_ns  = empty( $from_raw ) ? null : explode( '-', $from_raw );
	$to_ns    = empty( $to_raw ) ? null : explode( '-', $to_raw );
	$state    = '';

	if ( $from_ns ) {
		$t      = \wpinc\post\create_date_array_of_today();
		$t_from = \wpinc\post\compare_date_arrays( $t, $from_ns );

		$state = 'ongoing';
		if ( $to_ns ) {
			$t_end = \wpinc\post\compare_date_arrays( $t, $to_ns );
			if ( '<' === $t_from ) {
				$state = 'upcoming';
			} elseif ( '>' === $t_end ) {
				$state = 'finished';
			}
		} else {
			if ( '<' === $t_from ) {
				$state = 'upcoming';
			} elseif ( '>' === $t_from ) {
				$state = 'finished';
			}
		}
	}
	return $state;
}
