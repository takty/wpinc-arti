<?php
/**
 * Event Post Type
 *
 * @author Takuto Yanagida
 * @version 2021-03-26
 */

namespace st\event;

require_once __DIR__ . '/post-type.php';
require_once __DIR__ . '/../admin/list-table-column.php';
require_once __DIR__ . '/../admin/misc.php';
require_once __DIR__ . '/../metabox/duration-picker.php';
require_once __DIR__ . '/../util/date.php';


const PMK_DATE_BGN = '_date_bgn';
const PMK_DATE_END = '_date_end';


function register_post_type( $post_type = 'event', $slug = false, $opts = array(), $labels = array(), $args = array(), ?callable $home_url = null ) {
	$opts = array_merge(
		array(
			'is_autofill_enabled'   => false,
			'order_by_date'         => 'begin',
			'date_replaced_by_date' => false,
		),
		$opts
	);
	$labels = array_merge(
		array(
			'name'               => 'Events',
			'period_label'       => 'Date',
			'period_begin_label' => 'Begin',
			'period_end_label'   => 'End',
			'year_label'         => '',
		),
		$labels
	);
	$args = array_merge(
		array(
			'labels'        => $labels,
			'public'        => true,
			'show_ui'       => true,
			'menu_position' => 5,
			'menu_icon'     => 'dashicons-calendar-alt',
			'supports'      => [ 'title', 'editor', 'revisions', 'thumbnail' ],
			'has_archive'   => true,
			'rewrite'       => false,
		),
		$args
	);
	if ( $slug === false ) {
		$slug = $post_type;
	}
	\register_post_type( $post_type, $args );
	\st\post_type\add_rewrite_rules( $post_type, $slug, 'date', false, $home_url );

	$pmk_o = ( 'begin' === $opts['order_by_date'] ) ? PMK_DATE_BGN : ( ( 'end' === $opts['order_by_date'] ) ? PMK_DATE_END : false );
	if ( $pmk_o ) {
		\st\post_type\make_custom_date_sortable( $post_type, 'date', $pmk_o );
		\st\post_type\enable_custom_date_adjacent_post_link( $post_type, $pmk_o );
	}

	$pmk_d = ( 'begin' === $opts['date_replaced_by_date'] ) ? PMK_DATE_BGN : ( ( 'end' === $opts['date_replaced_by_date'] ) ? PMK_DATE_END : false );
	if ( $pmk_d ) {
		add_filter(
			'get_the_date',
			function ( $the_date, $d, $post ) use ( $post_type, $pmk_d ) {
				if ( $post->post_type !== $post_type ) {
					return $the_date;
				}
				$date = get_post_meta( $post->ID, $pmk_d, true );
				return mysql2date( empty( $d ) ? get_option( 'date_format' ) : $d, $date );
			},
			10,
			3
		);
	}
	if ( is_admin() ) {
		_set_duration_picker( $post_type, $opts, $labels );
	}

	add_filter( 'body_class', function ( array $classes ) use ( $post_type ) {
		if ( is_singular( $post_type ) ) {
			global $wp_query;
			$post      = $wp_query->get_queried_object();
			$classes[] = \st\event\_get_duration_state( $post->ID );
		}
		return $classes;
	} );
}

function _set_duration_picker( $post_type, $opts, $labels ) {
	if ( \st\is_post_type( $post_type ) ) {
		add_action(
			'admin_print_scripts',
			function () {
				\st\DurationPicker::enqueue_script();
			}
		);
	}
	add_action(
		'admin_menu',
		function () use ( $post_type, $labels, $opts ) {
			\st\DurationPicker::set_year_label( $labels['year_label'] );
			$dp = \st\DurationPicker::get_instance( '' );
			$dp->set_duration_labels( $labels['period_begin_label'], $labels['period_end_label'] );
			$dp->set_autofill_enabled( $opts['is_autofill_enabled'] );
			$dp->add_meta_box( $labels['period_label'], $post_type, 'side' );
		}
	);
	add_action(
		'save_post',
		function ( $post_id ) {
			$dp = \st\DurationPicker::get_instance( '' );
			$dp->save_meta_box( $post_id );
		}
	);
}

function set_admin_columns( $post_type, $add_cat, $add_tag, $tax ) {
	add_action(
		'wp_loaded',
		function () use ( $post_type, $add_cat, $add_tag, $tax ) {
			$cs = \st\list_table_column\insert_default_columns();
			$cs = \st\list_table_column\insert_common_taxonomy_columns( $post_type, $add_cat, $add_tag, -1, $cs );
			$cs = insert_date_columns( $post_type, -1, $cs );
			array_splice( $cs, -1, 0, array( array( 'name' => $tax, 'width' => '10%' ) ) );
			$scs = insert_date_sortable_columns();
			\st\list_table_column\set_admin_columns( $post_type, $cs, $scs );
		}
	);
}


// -----------------------------------------------------------------------------


function insert_date_columns( $post_type, $pos = false, $cs = array() ) {
	$pto       = get_post_type_object( $post_type );
	$label_bgn = isset( $pto->labels->period_begin_label ) ? $pto->labels->period_begin_label : __( 'Begin' );
	$label_end = isset( $pto->labels->period_end_label ) ? $pto->labels->period_end_label : __( 'End' );
	$ns = array(
		array(
			'name'  => PMK_DATE_BGN,
			'label' => $label_bgn,
			'width' => '15%',
			'value' => '\st\event\_echo_date_val'
		),
		array(
			'name'  => PMK_DATE_END,
			'label' => $label_end,
			'width' => '15%',
			'value' => '\st\event\_echo_date_val'
		),
	);
	if ( false === $pos ) {
		return array_merge( $cs, $ns );
	}
	array_splice( $cs, $pos, 0, $ns );
	return $cs;
}

function _echo_date_val( $val ) {
	if ( empty( $val ) ) {
		return;
	}
	echo esc_attr( date( get_option( 'date_format' ), strtotime( $val ) ) );
}

function insert_date_sortable_columns( $pos = false, $scs = array() ) {
	$ns = array( PMK_DATE_BGN, PMK_DATE_END );
	if ( false === $pos ) {
		return array_merge( $scs, $ns );
	}
	array_splice( $scs, $pos, 0, $ns );
	return $scs;
}


// -----------------------------------------------------------------------------


function format_duration( $post_id, array $formats, string $date_format, bool $is_translated ) {
	$dd   = _get_duration_dates( $post_id );
	$df   = implode( "\t", str_split( $date_format, 1 ) );
	$type = 'one';

	if ( $dd['bgn_ns'] && $dd['end_ns'] ) {
		if ( $dd['bgn_ns'][0] !== $dd['end_ns'][0] ) {
			$type = 'ymd';
		} elseif ( $dd['bgn_ns'][1] !== $dd['end_ns'][1] ) {
			$type = 'md';
		} elseif ( $dd['bgn_ns'][2] !== $dd['end_ns'][2] ) {
			$type = 'd';
		}
		$bgn_fd = $dd['bgn_raw'] ? explode( "\t", mysql2date( $df, $dd['bgn_raw'], $is_translated ) ) : array();
		$bgn_fd = array_pad( $bgn_fd, 4, '' );
		$end_fd = $dd['end_raw'] ? explode( "\t", mysql2date( $df, $dd['end_raw'], $is_translated ) ) : array();
		$end_fd = array_pad( $end_fd, 4, '' );

		return sprintf( $formats[ $type ], ...$bgn_fd, ...$end_fd );
	} elseif ( $dd['bgn_ns'] || $dd['end_ns'] ) {
		$d  = $dd['bgn_raw'] ? $dd['bgn_raw'] : $dd['end_raw'];
		$fd = $d ? explode( "\t", mysql2date( $df, $d, $is_translated ) ) : array();
		$fd = array_pad( $fd, 4, '' );
		return sprintf( $formats[ $type ], ...$fd );
	}
	return '';
}

function _get_duration_dates( $post_id ) {
	$bgn_raw = get_post_meta( $post_id, PMK_DATE_BGN, true );
	$end_raw = get_post_meta( $post_id, PMK_DATE_END, true );
	$bgn_ns  = empty( $bgn_raw ) ? null : explode( '-', $bgn_raw );
	$end_ns  = empty( $end_raw ) ? null : explode( '-', $end_raw );
	return compact( 'bgn_raw', 'end_raw', 'bgn_ns', 'end_ns' );
}

function _get_duration_state( $post_id ) {
	$bgn_raw = get_post_meta( $post_id, PMK_DATE_BGN, true );
	$end_raw = get_post_meta( $post_id, PMK_DATE_END, true );
	$bgn_ns  = empty( $bgn_raw ) ? null : explode( '-', $bgn_raw );
	$end_ns  = empty( $end_raw ) ? null : explode( '-', $end_raw );
	$state   = '';

	if ( $bgn_ns ) {
		$t     = \st\create_date_array_of_today();
		$t_bgn = \st\compare_date_arrays( $t, $bgn_ns );

		if ( $end_ns ) {
			$t_end = \st\compare_date_arrays( $t, $end_ns );
			$state = 'ongoing';
			if ( '<' === $t_bgn ) {
				$state = 'upcoming';
			} elseif ( '>' === $t_end ) {
				$state = 'finished';
			}
		} else {
			$state = 'ongoing';
			if ( '<' === $t_bgn ) {
				$state = 'upcoming';
			} elseif ( '>' === $t_bgn ) {
				$state = 'finished';
			}
		}
	}
	return $state;
}
