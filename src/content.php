<?php
/**
 * Contents
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2023-11-02
 */

declare(strict_types=1);

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
		function ( string $_text, int $num_words, string $more, string $original_text ) {
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


/** phpcs:ignore
 * Checks whether the string contains any title.
 *
 * @param string|null $str  String.
 * phpcs:ignore
 * @param array{
 *     post?    : int|\WP_Post|null,
 *     meta_key?: string,
 * } $args Arguments.
 * $args {
 *     Arguments.
 *
 *     @type int|\WP_Post|null 'post'     WP_Post instance or Post ID/object. Default null.
 *     @type string            'meta_key' (Optional) Post meta key.
 * }
 * @return bool Whether the string contains any title.
 */
function has_title( ?string $str = null, $args = array() ): bool {
	if ( null === $str ) {
		$str = get_the_title( $args );
	}
	return ! empty( trim( $str ) );
}

/** phpcs:ignore
 * Checks whether the string contains any content.
 *
 * @param string|null $str  String.
 * phpcs:ignore
 * @param array{
 *     post?    : int|\WP_Post|null,
 *     meta_key?: string,
 * } $args Arguments.
 * $args {
 *     Arguments.
 *
 *     @type int|\WP_Post|null 'post'     WP_Post instance or Post ID/object. Default null.
 *     @type string            'meta_key' (Optional) Post meta key.
 * }
 * @return bool Whether the string contains any content.
 */
function has_content( ?string $str = null, $args = array() ): bool {
	if ( null === $str ) {
		$str = get_the_content( null, false, $args );
	}
	// phpcs:disable
	// $allowed_tags = array( 'hr', 'br', 'img', 'audio', 'video', 'canvas', 'iframe' );  // For PHP 7.4.
	// phpcs:enable
	$allowed_tags = '<hr><br><img><audio><video><canvas><iframe>';  // For PHP 7.3.

	$str = strip_tags( $str, $allowed_tags );
	$str = str_replace( '&nbsp;', '', $str );
	return ! empty( trim( $str ) );
}


// -----------------------------------------------------------------------------


/** phpcs:ignore
 * Display the current post title with optional markup.
 *
 * @param string $before (Optional) Markup to prepend to the title. Default ''.
 * @param string $after  (Optional) Markup to append to the title. Default ''.
 * phpcs:ignore
 * @param array{
 *     post?    : int|\WP_Post|null,
 *     meta_key?: string,
 *     short?   : int,
 *     long?    : int,
 *     word?    : string,
 *     line?    : string,
 *     filter?  : callable,
 *     small?   : bool,
 * } $args Arguments.
 * $args {
 *     Arguments.
 *
 *     @type int|\WP_Post|null 'post'     WP_Post instance or Post ID/object. Default null.
 *     @type string            'meta_key' (Optional) Post meta key.
 *     @type int               'short'    Length at which the title is considered short. Default 8.
 *     @type int               'long'     Length at which the title is considered long. Default 32.
 *     @type string            'word'     (For 'separate_text') Segment type: 'ja' or 'none'. Default 'none'.
 *     @type string            'line'     (For 'separate_text') Line wrapping type: 'raw', 'br', 'span', 'div', or 'array'. Default 'div'.
 *     @type callable          'filter'   (For 'separate_text') Filter function.
 *     @type bool              'small'    (For 'separate_text') Whether to handle 'small' elements.
 * }
 */
function the_title( string $before = '', string $after = '', array $args = array() ): void {
	$str = get_the_title( $args );
	if ( empty( $str ) ) {
		return;
	}
	echo process_title( $str, $before, $after, $args );  // phpcs:ignore
}

/** phpcs:ignore
 * Displays the post content.
 *
 * @param string|null $more_link_text Content for when there is more text. Default: null.
 * @param bool        $strip_teaser   Strip teaser content before the more text. Default: false.
 * phpcs:ignore
 * @param array{
 *     post?    : int|\WP_Post|null,
 *     meta_key?: string,
 * } $args Arguments.
 * $args {
 *     Arguments.
 *
 *     @type int|\WP_Post|null 'post'     WP_Post instance or Post ID/object. Default null.
 *     @type string            'meta_key' (Optional) Post meta key.
 * }
 */
function the_content( ?string $more_link_text = null, bool $strip_teaser = false, array $args = array() ): void {
	$str = get_the_content( $more_link_text, $strip_teaser, $args );
	echo process_content( $str );  // phpcs:ignore
}


// -----------------------------------------------------------------------------


/** phpcs:ignore
 * Apply title filters to string.
 *
 * @param string $str    String.
 * @param string $before (Optional) Markup to prepend to the title. Default ''.
 * @param string $after  (Optional) Markup to append to the title. Default ''.
 * phpcs:ignore
 * @param array{
 *     short? : int,
 *     long?  : int,
 *     word?  : string,
 *     line?  : string,
 *     filter?: callable,
 *     small? : bool,
 * } $args Arguments.
 * $args {
 *     Arguments.
 *
 *     @type int      'short'  Length at which the title is considered short. Default 8.
 *     @type int      'long'   Length at which the title is considered long. Default 32.
 *     @type string   'word'   (For 'separate_text') Segment type: 'ja' or 'none'. Default 'none'.
 *     @type string   'line'   (For 'separate_text') Line wrapping type: 'raw', 'br', 'span', 'div', or 'array'. Default 'div'.
 *     @type callable 'filter' (For 'separate_text') Filter function.
 *     @type bool     'small'  (For 'separate_text') Whether to handle 'small' elements.
 * }
 * @return string Filtered string.
 */
function process_title( string $str, string $before = '', string $after = '', array $args = array() ): string {
	$args += array(
		'short'  => 8,
		'long'   => 32,

		'word'   => 'none',
		'line'   => 'div',
		'filter' => 'esc_html',
		'small'  => true,
	);

	$len = mb_strlen( $str );
	$cls = ( $args['long'] <= $len ) ? 'long' : ( ( $len <= $args['short'] ) ? 'short' : '' );

	$temp = separate_text( $str, $args );
	if ( is_string( $temp ) ) {
		$str = $temp;
	}
	$str = str_replace( '%class', " $cls", $before ) . $str . $after;
	return $str;
}

/**
 * Apply content filters to string.
 *
 * @param string $str String.
 * @return string Filtered string.
 */
function process_content( string $str ): string {
	$str = apply_filters( 'the_content', $str );  // Shortcodes are expanded here.
	$str = str_replace( ']]>', ']]&gt;', $str );
	return $str;
}


// -----------------------------------------------------------------------------


/** phpcs:ignore
 * Retrieves the post title.
 *
 * phpcs:ignore
 * @param array{
 *     post?    : int|\WP_Post|null,
 *     meta_key?: string,
 * } $args Arguments.
 * $args {
 *     Arguments.
 *
 *     @type int|\WP_Post|null 'post'     WP_Post instance or Post ID/object. Default null.
 *     @type string            'meta_key' (Optional) Post meta key.
 * }
 * @return string Post content.
 */
function get_the_title( array $args = array() ): string {
	$args += array(
		'post'     => null,
		'meta_key' => null,  // phpcs:ignore
	);
	if ( is_string( $args['meta_key'] ) ) {
		$post = get_post( $args['post'] );
		if ( $post instanceof \WP_Post ) {
			$val = get_post_meta( $post->ID, $args['meta_key'], true );
			if ( is_string( $val ) ) {
				return $val;
			}
		}
		return '';
	} else {
		return \get_the_title( $args['post'] ?? 0 );
	}
}

/** phpcs:ignore
 * Retrieves the post content.
 *
 * @param string|null $more_link_text Content for when there is more text. Default: null.
 * @param bool        $strip_teaser   Strip teaser content before the more text. Default: false.
 * phpcs:ignore
 * @param array{
 *     post?    : int|\WP_Post|null,
 *     meta_key?: string,
 * } $args Arguments.
 * $args {
 *     Arguments.
 *
 *     @type int|\WP_Post|null 'post'     WP_Post instance or Post ID/object. Default null.
 *     @type string            'meta_key' (Optional) Post meta key.
 * }
 * @return string Post content.
 */
function get_the_content( ?string $more_link_text = null, bool $strip_teaser = false, array $args = array() ): string {
	$args += array(
		'post'     => null,
		'meta_key' => null,  // phpcs:ignore
	);
	if ( is_string( $args['meta_key'] ) ) {
		$post = get_post( $args['post'] );
		if ( $post instanceof \WP_Post ) {
			$val = get_post_meta( $post->ID, $args['meta_key'], true );
			if ( is_string( $val ) ) {
				return $val;
			}
		}
		return '';
	} else {
		return \get_the_content( $more_link_text, $strip_teaser, $args['post'] );
	}
}
