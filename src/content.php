<?php
/**
 * Contents
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2022-06-22
 */

namespace wpinc\post;

require_once __DIR__ . '/assets/text.php';

/**
 * Enables custom excerpt.
 *
 * @param int    $length Number of characters. Default 220.
 * @param string $more   (Optional) What to append if $text needs to be trimmed. Default '...'.
 */
function enable_custom_excerpt( int $length = 220, string $more = '...' ): void {
	if ( is_admin() ) {
		return;
	}
	add_filter(
		'excerpt_length',
		function () use ( $length ) {
			return $length;
		}
	);
	add_filter(
		'excerpt_more',
		function () use ( $more ) {
			return $more;
		}
	);
	add_filter(
		'wp_trim_words',
		function ( string $text, int $num_words, string $more, string $original_text ) {
			$allowed_html = array(
				'sub' => array(),
				'sup' => array(),
			);

			$orig = wp_kses( $original_text, $allowed_html );
			$orig = mb_trim( remove_continuous_spaces( $orig ) );
			$text = mb_trim( mb_strimwidth( $orig, 0, $num_words ) );
			if ( ! empty( $text ) && $orig !== $text ) {
				$text = $text . $more;
			}
			return $text;
		},
		10,
		4
	);
}


// -----------------------------------------------------------------------------


/**
 * Checks whether the string contains any content.
 *
 * @param bool $str String.
 * @return bool Whether the string contains any content.
 */
function has_content( bool $str = false ): bool {
	if ( false === $str ) {
		$str = get_the_content();
	}
	// phpcs:disable
	// $allowed_tags = array( 'img', 'hr', 'br', 'iframe' );  // For PHP 7.4.
	// phpcs:enable
	$allowed_tags = '<img><hr><br><iframe>';  // For PHP 7.3.

	$str = strip_tags( $str, $allowed_tags );
	$str = str_replace( '&nbsp;', '', $str );
	return ! empty( trim( $str ) );
}

/**
 * Display the current post title with optional markup.
 *
 * @param string $before (Optional) Markup to prepend to the title. Default ''.
 * @param string $after  (Optional) Markup to append to the title. Default ''.
 * @param array  $args {
 *     Arguments.
 *
 *     @type int      'short'  Length at which the title is considered short. Default 8.
 *     @type int      'long'   Length at which the title is considered long. Default 32.
 *     @type string   'word'   (For 'separate_text') Segment type: 'ja' or 'none'. Default 'none'.
 *     @type string   'line'   (For 'separate_text') Line wrapping type: 'raw', 'br', 'span', 'div', or 'array'. Default 'div'.
 *     @type callable 'filter' (For 'separate_text') Filter function.
 *     @type bool     'small'  (For 'separate_text') Whether to handle 'small' elements.
 * }
 */
function the_title( string $before = '', string $after = '', array $args = array() ): void {
	$args += array(
		'short'  => 8,
		'long'   => 32,

		'word'   => 'none',
		'line'   => 'div',
		'filter' => 'esc_html',
		'small'  => true,
	);
	$title = get_the_title();
	if ( empty( $title ) ) {
		return;
	}
	$len = mb_strlen( $title );
	if ( $args['long'] <= $len ) {
		$option = ' long';
	} elseif ( $len <= $args['short'] ) {
		$option = ' short';
	} else {
		$option = '';
	}
	$before = str_replace( '%class', $option, $before );
	$title  = separate_text( $title, $args );
	echo wp_kses_post( $before . $title . $after );
}
