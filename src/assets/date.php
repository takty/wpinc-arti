<?php
/**
 * Date
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2023-08-31
 */

namespace wpinc\post;

/**
 * Creates date string of today.
 *
 * @param int    $offset_year  Offset of year. Default 0.
 * @param int    $offset_month Offset of month. Default 0.
 * @param int    $offset_day   Offset of day. Default 0.
 * @param string $format       Format of date string. Default 'Y-m-d'.
 * @return string Date string.
 */
function create_date_string_of_today( int $offset_year = 0, int $offset_month = 0, int $offset_day = 0, string $format = 'Y-m-d' ): string {
	if ( 0 === $offset_year && 0 === $offset_month && 0 === $offset_day ) {
		return (string) wp_date( $format );
	}
	$y  = (int) gmdate( 'Y' ) + $offset_year;
	$m  = (int) gmdate( 'm' ) + $offset_month;
	$d  = (int) gmdate( 'd' ) + $offset_day;
	$od = mktime( 0, 0, 0, $m, $d, $y );  // The order must be month, day, and year!
	if ( false === $od ) {
		return '';
	}
	return (string) wp_date( $format, $od );
}

/**
 * Makes date array of today.
 *
 * @param int $offset_year  Year offset.
 * @param int $offset_month Month offset.
 * @param int $offset_day   Day offset.
 * @return string[] Date array.
 */
function create_date_array_of_today( int $offset_year = 0, int $offset_month = 0, int $offset_day = 0 ): array {
	$date_str = create_date_string_of_today( $offset_year, $offset_month, $offset_day );
	return explode( '-', $date_str );
}


// -----------------------------------------------------------------------------


/**
 * Compares date arrays.
 *
 * @param string[] $d1 Date array.
 * @param string[] $d2 Date array.
 * @return string Comparison result.
 */
function compare_date_array( array $d1, array $d2 ): string {
	if ( $d1[0] === $d2[0] && $d1[1] === $d2[1] && $d1[2] === $d2[2] ) {
		return '=';
	}
	if ( $d1[0] > $d2[0] ) {
		return '>';
	}
	if ( $d1[0] === $d2[0] && $d1[1] > $d2[1] ) {
		return '>';
	}
	if ( $d1[0] === $d2[0] && $d1[1] === $d2[1] && $d1[2] > $d2[2] ) {
		return '>';
	}
	return '<';
}
