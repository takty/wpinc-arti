<?php
/**
 * Date
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2022-01-17
 */

namespace wpinc\post;

const DATE_STRING_FORMAT = 'Y-m-d';

/**
 * Makes date string of today.
 *
 * @param int $offset_year  Year offset.
 * @param int $offset_month Month offset.
 * @param int $offset_day   Day offset.
 * @return string Date string.
 */
function create_date_string_of_today( int $offset_year = 0, int $offset_month = 0, int $offset_day = 0 ): string {
	if ( 0 === $offset_year && 0 === $offset_month && 0 === $offset_day ) {
		return date_i18n( DATE_STRING_FORMAT );
	}
	$y  = date_i18n( 'Y' ) + $offset_year;
	$m  = date_i18n( 'm' ) + $offset_month;
	$d  = date_i18n( 'd' ) + $offset_day;
	$od = mktime( 0, 0, 0, $m, $d, $y );  // The order must be month, day, and year!
	return date_i18n( DATE_STRING_FORMAT, $od );
}

/**
 * Makes date array of today.
 *
 * @param int $offset_year  Year offset.
 * @param int $offset_month Month offset.
 * @param int $offset_day   Day offset.
 * @return array Date array.
 */
function create_date_array_of_today( int $offset_year = 0, int $offset_month = 0, int $offset_day = 0 ): array {
	$date_str = create_date_string_of_today( $offset_year, $offset_month, $offset_day );
	return explode( '-', $date_str );
}


// -----------------------------------------------------------------------------


/**
 * Compares date arrays.
 *
 * @param array $d1 Date array.
 * @param array $d2 Date array.
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
