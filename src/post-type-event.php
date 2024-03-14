<?php
/**
 * Event Post Type
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2024-03-14
 */

declare(strict_types=1);

namespace wpinc\post\event;

require_once __DIR__ . '/assets/asset-url.php';
require_once __DIR__ . '/assets/admin-current-post.php';
require_once __DIR__ . '/assets/date.php';
require_once __DIR__ . '/custom-date.php';
require_once __DIR__ . '/post-type.php';
require_once __DIR__ . '/list-table-column.php';

const PMK_DATE      = '_date';
const PMK_DATE_FROM = '_date_from';
const PMK_DATE_TO   = '_date_to';

/** phpcs:ignore
 * Registers event-like post type.
 *
 * @psalm-suppress ArgumentTypeCoercion
 * phpcs:ignore
 * @param array{
 *     post_type?        : string,
 *     slug?             : string,
 *     by_post_name?     : bool,
 *     do_autofill?      : bool,
 *     order_by?         : 'from'|'to',
 *     replace_date_with?: 'from'|'to',
 *     labels?           : array{  name?: string, date?: string, date_from?: string, date_to?: string  },
 * } $args Arguments.
 */
function register_post_type( array $args = array() ): void {
	$def_opts = array(  // Keys removed when $args is passed to register_post_type.
		'post_type'         => 'event',
		'slug'              => '',
		'by_post_name'      => false,
		'do_autofill'       => false,
		'order_by'          => 'from',
		'replace_date_with' => 'from',
	);

	$args += $def_opts;
	$args += array(
		'public'        => true,
		'show_in_rest'  => true,
		'has_archive'   => true,
		'rewrite'       => false,
		'menu_position' => 5,
		'menu_icon'     => 'dashicons-calendar-alt',
		'supports'      => array( 'title', 'editor', 'revisions', 'thumbnail', 'custom-fields' ),
		'labels'        => array(),
	);

	$args['labels'] += array(
		'name'      => _x( 'Events', 'post type event', 'wpinc_post' ),
		'date'      => _x( 'Duration', 'post type event', 'wpinc_post' ),
		'date_from' => _x( 'From', 'post type event', 'wpinc_post' ),
		'date_to'   => _x( 'To', 'post type event', 'wpinc_post' ),
	);

	if ( '' === $args['slug'] ) {
		$args['slug'] = $args['post_type'];
	}
	/** @psalm-suppress InvalidArgument */  // phpcs:ignore
	\register_post_type( $args['post_type'], array_diff_key( $args, $def_opts ) );  // @phpstan-ignore-line
	\wpinc\post\add_rewrite_rules( $args['post_type'], $args['slug'], 'date', $args['by_post_name'] );

	_set_custom_date_order( $args['post_type'], $args['order_by'] );
	_replace_date( $args['post_type'], $args['replace_date_with'] );

	foreach ( array( PMK_DATE_FROM, PMK_DATE_TO ) as $key ) {
		register_post_meta(
			$args['post_type'],
			$key,
			array(
				'type'          => 'string',
				'single'        => true,
				'show_in_rest'  => true,
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
	/** @psalm-suppress InvalidArgument */  // phpcs:ignore
	_initialize_hooks( $args );
}

/** phpcs:ignore
 * Initializes hooks.
 *
 * @access private
 * @global string $pagenow
 * phpcs:ignore
 * @param array{
 *     post_type  : string,
 *     do_autofill: bool,
 *     labels     : array{ date: string, date_from: string, date_to: string }
 * } $args Arguments.
 */
function _initialize_hooks( array $args ): void {
	if ( ! is_admin() ) {
		// For adding duration state to the body classes.
		add_filter(
			'body_class',
			function ( array $classes ) use ( $args ) {
				return _cb_body_class( $classes, $args['post_type'] );
			}
		);
		// For adding duration state to the post classes.
		add_filter(
			'post_class',
			function ( array $classes, array $cls, int $post_id ) use ( $args ) {
				return _cb_post_class( $classes, $cls, $post_id, $args['post_type'] );
			},
			10,
			3
		);
	}

	if ( is_admin() ) {
		add_action(
			'current_screen',  // For using is_block_editor().
			function () use ( $args ) {
				global $pagenow;
				if ( 'post-new.php' === $pagenow || 'post.php' === $pagenow ) {
					$cs = get_current_screen();
					if ( $cs && $cs->is_block_editor() ) {
						add_action(
							'enqueue_block_editor_assets',
							function () use ( $args ) {
								/** @psalm-suppress InvalidArgument */  // phpcs:ignore
								_cb_enqueue_block_editor_assets( $args );
							},
							10,
							0
						);
					} else {
						_set_duration_picker( $args );
					}
				}
			}
		);
	}
	add_action(
		'rest_after_insert_' . $args['post_type'],
		function ( \WP_Post $post ) use ( $args ) {
			/** @psalm-suppress InvalidArgument */  // phpcs:ignore
			_cb_rest_after_insert( $args, $post );
		}
	);
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
			function ( $the_date, string $df, \WP_Post $post ) use ( $post_type, $key ) {
				if ( $post->post_type !== $post_type ) {
					return $the_date;
				}
				if ( '' === $df ) {
					$df = get_option( 'date_format' );
					if ( ! is_string( $df ) ) {
						$df = '';
					}
				}
				$date = get_post_meta( $post->ID, $key, true );
				if ( is_string( $date ) ) {
					$date = mysql2date( $df, $date );
					if ( is_string( $date ) ) {
						return $date;
					}
				}
				return $the_date;
			},
			10,
			3
		);
	}
}

/**
 * Callback function for 'body_class' filter.
 *
 * @param string[] $classes   Array of classes.
 * @param string   $post_type Post type.
 * @return string[] Classes.
 */
function _cb_body_class( array $classes, string $post_type ): array {
	global $wp_query;
	if ( is_singular( $post_type ) ) {
		$post      = $wp_query->get_queried_object();
		$classes[] = _get_duration_state( $post->ID );
	}
	return $classes;
}

/**
 * Callback function for 'post_class' filter.
 *
 * @access private
 *
 * @param string[] $classes   An array of post class names.
 * @param string[] $_class    An array of additional class names added to the post.
 * @param int      $post_id   The post ID.
 * @param string   $post_type Post type.
 * @return string[] Classes.
 */
function _cb_post_class( array $classes, array $_class, int $post_id, string $post_type ): array {
	if ( get_post_type( $post_id ) === $post_type ) {
		$classes[] = _get_duration_state( $post_id );
	}
	return $classes;
}


// ---------------------------------------- Callback Functions for Block Editor.


/** phpcs:ignore
 * Callback function for 'enqueue_block_editor_assets' action.
 *
 * @access private
 * phpcs:ignore
 * @param array{
 *     post_type: string,
 *     labels   : array{
 *         date     : string,
 *         date_from: string,
 *         date_to  : string,
 *     }
 * } $args Arguments.
 */
function _cb_enqueue_block_editor_assets( array $args ): void {
	$cs = get_current_screen();
	if ( $cs && $cs->id === $args['post_type'] ) {
		$url_to = untrailingslashit( \wpinc\get_file_uri( __DIR__ ) );
		wp_enqueue_script(
			'wpinc-duration-picker',
			\wpinc\abs_url( $url_to, './assets/js/duration-picker.min.js' ),
			array( 'wp-element', 'wp-i18n', 'wp-data', 'wp-components', 'wp-edit-post', 'wp-plugins' ),
			(string) filemtime( __DIR__ . '/assets/js/duration-picker.min.js' ),
			true
		);
		$params = array(
			'pmk_from' => PMK_DATE_FROM,
			'pmk_to'   => PMK_DATE_TO,
			'labels'   => array(
				'panel'        => $args['labels']['date'],
				'date_from'    => $args['labels']['date_from'],
				'date_to'      => $args['labels']['date_to'],
				'default_from' => '0000-00-00',
				'default_to'   => '0000-00-00',
			),
		);
		wp_localize_script( 'wpinc-duration-picker', 'wpinc_duration_picker', $params );
	}
}

/** phpcs:ignore
 * Callback function for 'rest_after_insert_{$post_type}' action.
 *
 * @access private
 * @psalm-suppress InvalidDocblock
 * phpcs:ignore
 * @param array{ do_autofill: bool } $args Arguments.
 * @param \WP_Post                   $post Inserted or updated post object.
 */
function _cb_rest_after_insert( array $args, \WP_Post $post ): void {
	$from = get_post_meta( $post->ID, PMK_DATE_FROM, true );
	$to   = get_post_meta( $post->ID, PMK_DATE_TO, true );
	if ( is_string( $from ) && is_string( $to ) ) {
		$from_val = (int) str_replace( '-', '', $from );
		$to_val   = (int) str_replace( '-', '', $to );
		if ( $to_val < $from_val ) {
			update_post_meta( $post->ID, PMK_DATE_TO, $from );
			update_post_meta( $post->ID, PMK_DATE_FROM, $to );
		}
	}
	if ( $args['do_autofill'] ) {
		if ( $from && ! $to ) {
			update_post_meta( $post->ID, PMK_DATE_TO, $from );
		} elseif ( ! $from && $to ) {
			update_post_meta( $post->ID, PMK_DATE_FROM, $to );
		}
	}
}


// -------------------------------------- Callback Functions for Classic Editor.


/** phpcs:ignore
 * Sets duration picker.
 *
 * @psalm-suppress UndefinedFunction, InvalidDocblock
 * phpcs:ignore
 * @param array{
 *     post_type  : string,
 *     do_autofill: bool,
 *     labels     : array{
 *         date     : string,
 *         date_from: string,
 *         date_to  : string,
 *     },
 *     url_to?    : string,
 *     locale?    : string
 * } $args Arguments.
 */
function _set_duration_picker( array $args ): void {
	if ( ! \wpinc\is_admin_post_type( $args['post_type'] ) ) {
		return;
	}
	$dp_args = array(
		'key'         => PMK_DATE,
		'do_autofill' => $args['do_autofill'],
		'label_from'  => $args['labels']['date_from'],
		'label_to'    => $args['labels']['date_to'],
	);
	if ( isset( $args['url_to'] ) ) {
		$dp_args['url_to'] = $args['url_to'];
	}
	if ( isset( $args['locale'] ) ) {
		$dp_args['locale'] = $args['locale'];
	}
	\wpinc\dia\duration_picker\initialize( $dp_args );
	add_action(
		'add_meta_boxes',
		function () use ( $args, $dp_args ) {
			\wpinc\dia\duration_picker\add_meta_box( $dp_args, $args['labels']['date'], $args['post_type'], 'side' );
		}
	);
	add_action(
		'save_post',
		function ( $post_id ) use ( $dp_args ) {
			\wpinc\dia\duration_picker\save_meta_box( $dp_args, $post_id );
		}
	);
}


// -----------------------------------------------------------------------------


/**
 * Sets columns of list table.
 *
 * @psalm-suppress ArgumentTypeCoercion
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
			\wpinc\post\set_list_table_column( $post_type, $cs );  // @phpstan-ignore-line
		},
		10,
		0
	);
}

/**
 * Adds event duration columns.
 *
 * @param string                             $post_type Post type.
 * @param array<array<string, mixed>|string> $cs        Columns.
 * @return array<array<string, mixed>|string> Added columns.
 */
function add_duration_column( string $post_type, array $cs = array() ): array {
	$pto       = get_post_type_object( $post_type );
	$label_bgn = $pto->labels->date_from ?? _x( 'From', 'post type event', 'wpinc_post' );
	$label_end = $pto->labels->date_to ?? _x( 'To', 'post type event', 'wpinc_post' );

	$cs[] = array(
		'meta'     => PMK_DATE_FROM,
		'filter'   => '\wpinc\post\event\_filter_date_val',
		'sortable' => true,
		'label'    => $label_bgn,
		'width'    => '15%',
	);
	$cs[] = array(
		'meta'     => PMK_DATE_TO,
		'filter'   => '\wpinc\post\event\_filter_date_val',
		'sortable' => true,
		'label'    => $label_end,
		'width'    => '15%',
	);
	return $cs;
}

/**
 * Filter of duration columns.
 *
 * @param string $val Value.
 */
function _filter_date_val( string $val ): string {
	if ( '' === $val ) {
		return '';
	}
	$t = strtotime( $val );
	if ( false === $t ) {
		return '';
	}
	$df = get_option( 'date_format' );
	$df = is_string( $df ) ? $df : '';
	return esc_attr( gmdate( $df, $t ) );
}


// -----------------------------------------------------------------------------


/**
 * Formats duration date.
 *
 * @param int                   $post_id       Post ID.
 * @param array<string, string> $formats       Array of duration formats.
 * @param string                $date_format   Date format.
 * @param bool                  $do_translate  Whether to translate.
 * @return string Formatted duration.
 */
function format_duration( int $post_id, array $formats, string $date_format, bool $do_translate ): string {
	$dd = _get_duration_dates( $post_id );
	$df = implode( "\t", str_split( $date_format, 1 ) );

	if ( is_array( $dd['from_ns'] ) && is_array( $dd['to_ns'] ) ) {
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
	} elseif ( is_array( $dd['from_ns'] ) || is_array( $dd['to_ns'] ) ) {
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
	$fd = $str ? explode( "\t", (string) mysql2date( $df, $str, $do_translate ) ) : array();
	return array_pad( $fd, 4, '' );
}

/**
 * Retrieves duration date.
 *
 * @param int $post_id Post ID.
 * @return array{
 *     from_raw: string,
 *     to_raw  : string,
 *     from_ns : string[]|null,
 *     to_ns   : string[]|null
 * } Array of duration dates.
 */
function _get_duration_dates( int $post_id ): array {
	$from_raw = get_post_meta( $post_id, PMK_DATE_FROM, true );
	$from_raw = is_string( $from_raw ) ? $from_raw : '';
	$to_raw   = get_post_meta( $post_id, PMK_DATE_TO, true );
	$to_raw   = is_string( $to_raw ) ? $to_raw : '';
	$from_ns  = ( '' === $from_raw ) ? null : explode( '-', $from_raw );
	$to_ns    = ( '' === $to_raw ) ? null : explode( '-', $to_raw );
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
	$from_ns  = ( ! is_string( $from_raw ) || '' === $from_raw ) ? null : explode( '-', $from_raw );  // Check for non-empty-string.
	$to_ns    = ( ! is_string( $to_raw ) || '' === $to_raw ) ? null : explode( '-', $to_raw );  // Check for non-empty-string.
	$state    = '';

	if ( $from_ns ) {
		$t      = \wpinc\post\create_date_array_of_today();
		$t_from = \wpinc\post\compare_date_array( $t, $from_ns );

		$state = 'ongoing';
		if ( $to_ns ) {
			$t_end = \wpinc\post\compare_date_array( $t, $to_ns );
			if ( '<' === $t_from ) {
				$state = 'upcoming';
			} elseif ( '>' === $t_end ) {
				$state = 'finished';
			}
		} else {  // phpcs:ignore
			if ( '<' === $t_from ) {
				$state = 'upcoming';
			} elseif ( '>' === $t_from ) {
				$state = 'finished';
			}
		}
	}
	return $state;
}
