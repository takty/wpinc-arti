<?php
/**
 * Text Processing Functions
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2023-09-01
 */

namespace wpinc\post;

require_once __DIR__ . '/ja.php';

/**
 * Removes continuous spaces.
 *
 * @param string $str String.
 * @return string Modified string.
 */
function remove_continuous_spaces( string $str ): string {
	$str = preg_replace( '/　/', ' ', $str ) ?? $str;
	$str = preg_replace( '/\s+/', ' ', $str ) ?? $str;
	return $str;
}

/**
 * Trims multi-byte string.
 *
 * @param string $str String.
 * @return string Modified string.
 */
function mb_trim( string $str ): string {
	return preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $str ) ?? $str;
}


// -----------------------------------------------------------------------------


/**
 * Separates text.
 *
 * @param string               $str  String.
 * @param array<string, mixed> $args {
 *     Arguments.
 *
 *     @type string   'word'   Segment type: 'ja' or 'none'. Default 'none'.
 *     @type string   'line'   Line wrapping type: 'raw', 'br', 'span', 'div', or 'array'. Default 'div'.
 *     @type callable 'filter' Filter function. Default 'esc_html'.
 *     @type bool     'small'  Whether to handle 'small' elements. Default true.
 * }
 * @return string|string[] Separated text or an array of separation.
 */
function separate_text( string $str, array $args = array() ) {
	$args += array(
		'word'   => 'none',
		'line'   => 'div',
		'filter' => 'esc_html',
		'small'  => true,
	);

	$new_ls = null;
	$lines  = preg_split( '/　　|<\s*br\s*\/?>/ui', $str );

	if ( $lines ) {
		switch ( $args['word'] ) {
			case 'ja':
				$new_ls = array();
				if ( $args['small'] ) {
					foreach ( $lines as $l ) {
						$ms = array();
						$ss = preg_split( '/(<small>[\s\S]*?<\/small>)/iu', $l, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
						if ( $ss ) {
							foreach ( $ss as $s ) {
								preg_match( '/<small>([\s\S]*?)<\/small>/iu', $s, $matches );
								if ( empty( $matches ) ) {
									$ms[] = _segment_and_wrap( $s, $args['filter'] );
								} else {
									$ms[] = '<small>' . _segment_and_wrap( $matches[1], $args['filter'] ) . '</small>';
								}
							}
						}
						$new_ls[] = implode( '', $ms );
					}
				} else {
					foreach ( $lines as $l ) {
						$new_ls[] = _segment_and_wrap( $l, $args['filter'] );
					}
				}
				break;
			default:  // 'none'
				$new_ls = $lines;
				break;
		}
	}
	if ( empty( $new_ls ) ) {
		$new_ls = (array) $str;
	}
	switch ( $args['line'] ) {
		case 'raw':
			return implode( '', $new_ls );
		case 'br':
			return implode( '<br>', $new_ls );
		case 'span':
			return '<span>' . implode( '</span><span>', $new_ls ) . '</span>';
		case 'div':
			return '<div>' . implode( '</div><div>', $new_ls ) . '</div>';
		default:  // 'array'
			return $new_ls;
	}
}


// -----------------------------------------------------------------------------


/**
 * Segments and wrap string.
 *
 * @param string        $l      String.
 * @param callable|null $filter Filter function.
 * @return string Segmented string.
 */
function _segment_and_wrap( string $l, $filter = 'esc_html' ): string {
	$ps = ja\get_segment( $l );
	$ws = array();
	foreach ( $ps as $p ) {
		$w    = $filter ? call_user_func( $filter, $p[0] ) : $p[0];
		$ws[] = $p[1] ? "<span>$w</span>" : $w;
	}
	return implode( '', $ws );
}
