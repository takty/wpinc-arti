<?php
/**
 * Segmenter
 *
 * @package Wpinc Post
 * @author Takuto Yanagida
 * @version 2022-12-08
 */

namespace wpinc\post\ja;

/**
 * Gets segments.
 *
 * @param string $text Text.
 * @return array Segments.
 */
function get_segment( string $text ): array {
	$pairs  = array(
		'S*' => 1,
		'*E' => 1,
		'II' => 1,
		'KK' => 1,
		'HH' => 1,
		'HI' => 1,
	);
	$parts  = array();
	$t_prev = '';
	$word   = '';

	for ( $i = 0, $len = mb_strlen( $text ); $i < $len; ++$i ) {
		$c = mb_substr( $text, $i, 1 );
		$t = _get_ctype( $c );
		if ( isset( $pairs[ $t_prev . $t ] ) || isset( $pairs[ '*' . $t ] ) || isset( $pairs[ $t_prev . '*' ] ) ) {
			$word .= $c;
		} elseif ( 'O' === $t ) {
			if ( 'O' === $t_prev ) {
				$word .= $c;
			} else {
				if ( ! empty( $word ) ) {
					$parts[] = array( $word, true );
				}
				$word = $c;
			}
		} else {
			if ( ! empty( $word ) ) {
				$parts[] = array( $word, ( 'O' !== $t_prev ) );
			}
			$word = $c;
		}
		$t_prev = $t;
	}
	if ( ! empty( $word ) ) {
		$parts[] = array( $word, ( 'O' !== $t_prev ) );
	}
	return $parts;
}

/**
 * Gets character type.
 *
 * @access private
 *
 * @param string $c A character.
 * @return string Type.
 */
function _get_ctype( string $c ): string {
	$cpats = array(
		'S' => '/[「『（［｛〈《【〔〖〘〚＜]/u',
		'E' => '/[」』）］｝〉》】〕〗〙〛＞、，。．？！を：]/u',
		'I' => '/[ぁ-んゝ]/u',
		'K' => '/[ァ-ヴーｱ-ﾝﾞｰ]/u',
		'H' => '/[一-龠々〆ヵヶ]/u',
	);
	foreach ( $cpats as $t => $p ) {
		if ( preg_match( $p, $c ) === 1 ) {
			return $t;
		}
	}
	return 'O';
}
