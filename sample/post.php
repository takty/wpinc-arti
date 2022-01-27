<?php
/**
 * Post Type
 *
 * @package Sample
 * @author Takuto Yanagida
 * @version 2022-01-27
 */

namespace sample;

require_once __DIR__ . '/post/content.php';
require_once __DIR__ . '/post/custom-date.php';
require_once __DIR__ . '/post/list-table-column.php';
require_once __DIR__ . '/post/multi-entry.php';
require_once __DIR__ . '/post/post-type-event.php';
require_once __DIR__ . '/post/post-type-news.php';
require_once __DIR__ . '/post/post-type.php';
require_once __DIR__ . '/post/utility.php';

/**
 * Enables custom excerpt.
 *
 * @param int    $length Number of characters. Default 220.
 * @param string $more   (Optional) What to append if $text needs to be trimmed. Default '...'.
 */
function enable_custom_excerpt( int $length = 220, string $more = '...' ) {
	\wpinc\post\enable_custom_excerpt( $length, $more );
}

/**
 * Checks whether the string contains any content.
 *
 * @param bool $str String.
 * @return bool Whether the string contains any content.
 */
function has_content( bool $str = false ): bool {
	return \wpinc\post\has_content( $str );
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
 *     @type string   'mode'   Mode of separation. Default 'segment_small'.
 *     @type callable 'filter' Filter function. Default 'esc_html'.
 * }
 */
function the_title( string $before = '', string $after = '', array $args = array() ): void {
	\wpinc\post\the_title( $before, $after, $args );
}


// -----------------------------------------------------------------------------


/**
 * Makes custom date sortable.
 *
 * @param string $post_type Post type.
 * @param string $slug      Slug.
 * @param string $meta_key  Meta key.
 */
function make_custom_date_sortable( string $post_type, string $slug, string $meta_key ): void {
	\wpinc\post\make_custom_date_sortable( $post_type, $slug, $meta_key );
}

/**
 * Enables adjacent post links by custom date
 *
 * @param string $post_type Post type.
 * @param string $meta_key  Meta key.
 */
function enable_custom_date_adjacent_post_link( string $post_type, string $meta_key ): void {
	\wpinc\post\enable_custom_date_adjacent_post_link( $post_type, $meta_key );
}


// -----------------------------------------------------------------------------


/**
 * Makes date string of today.
 *
 * @param int $offset_year  Year offset.
 * @param int $offset_month Month offset.
 * @param int $offset_day   Day offset.
 * @return string Date string.
 */
function create_date_string_of_today( int $offset_year = 0, int $offset_month = 0, int $offset_day = 0 ): string {
	return \wpinc\post\create_date_string_of_today( $offset_year, $offset_month, $offset_day );
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
	return \wpinc\post\create_date_array_of_today( $offset_year, $offset_month, $offset_day );
}

/**
 * Compares date arrays.
 *
 * @param array $d1 Date array.
 * @param array $d2 Date array.
 * @return string Comparison result.
 */
function compare_date_array( array $d1, array $d2 ): string {
	return \wpinc\post\compare_date_array( $d1, $d2 );
}


// -----------------------------------------------------------------------------


/**
 * Enables to show page slug column.
 * Call this in 'load-edit.php' or 'admin_init' action.
 *
 * @param int|null $pos (Optional) Column position.
 */
function enable_page_slug_column( ?int $pos = null ) {
	\wpinc\post\enable_page_slug_column( $pos );
}

/**
 * Enables to show menu order column.
 * Call this in 'load-edit.php' or 'admin_init' action.
 *
 * @param int|null $pos (Optional) Column position.
 */
function enable_menu_order_column( ?int $pos = null ) {
	\wpinc\post\enable_menu_order_column( $pos );
}

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
	\wpinc\post\set_list_table_column( $post_type, $columns );
}


// -----------------------------------------------------------------------------


/**
 * Expands multiple entries.
 *
 * @param array       $ids  Post IDs.
 * @param string      $slug The slug name for the generic template.
 * @param string|null $name (Optional) The name of the specialized template. Default null.
 * @param array       $args (Optional) Additional arguments passed to the template. Default array().
 */
function expand_entries( array $ids, string $slug, ?string $name = null, array $args = array() ): void {
	\wpinc\post\expand_entries( $ids, $slug, $name, $args );
}


// -----------------------------------------------------------------------------


/**
 * Registers event-like post type.
 *
 * @param array $args Arguments.
 */
function register_event_post_type( array $args = array() ): void {
	\wpinc\post\event\register_post_type( $args );
}

/**
 * Sets columns of list table.
 *
 * @param string $post_type Post type.
 * @param bool   $add_cat   Whether to add {$post_type}_category taxonomy.
 * @param bool   $add_tag   Whether to add {$post_type}_tag taxonomy.
 */
function set_event_admin_column( string $post_type, bool $add_cat, bool $add_tag ): void {
	\wpinc\post\event\set_admin_column( $post_type, $add_cat, $add_tag );
}

/**
 * Adds event duration columns.
 *
 * @param string $post_type Post type.
 * @param array  $cs        Columns.
 * @return array Added columns.
 */
function add_event_duration_column( string $post_type, array $cs = array() ): array {
	return \wpinc\post\event\add_duration_column( $post_type, $cs );
}

/**
 * Formats duration date.
 *
 * @param int    $post_id       Post ID.
 * @param array  $formats       Array of duration formats.
 * @param string $date_format   Date format.
 * @param bool   $do_translate  Whether to translate.
 * @return string Formatted duration.
 */
function format_event_duration( int $post_id, array $formats, string $date_format, bool $do_translate ): string {
	return \wpinc\post\event\format_duration( $post_id, $formats, $date_format, $do_translate );
}


// -----------------------------------------------------------------------------


/**
 * Registers news-like post type.
 *
 * @param string $post_type Post type.
 * @param string $slug      Parma struct base.
 * @param array  $labels    Labels.
 * @param array  $args      Arguments for register_post_type().
 */
function register_news_post_type( string $post_type = 'news', string $slug = '', array $labels = array(), array $args = array() ): void {
	\wpinc\post\news\register_post_type( $post_type, $slug, $labels, $args );
}

/**
 * Sets columns of list table.
 *
 * @param string $post_type Post type.
 * @param bool   $add_cat   Whether to add {$post_type}_category taxonomy.
 * @param bool   $add_tag   Whether to add {$post_type}_tag taxonomy.
 */
function set_news_admin_column( string $post_type, bool $add_cat, bool $add_tag ): void {
	\wpinc\post\news\set_admin_column( $post_type, $add_cat, $add_tag );
}


// -----------------------------------------------------------------------------


/**
 * Adds rewrite rules for custom post types.
 *
 * @param string $post_type    Post type.
 * @param string $slug         Parma struct base.
 * @param string $date_slug    Date archive slug.
 * @param bool   $by_post_name Whether to use post name for URL.
 */
function add_rewrite_rules( string $post_type, string $slug = '', string $date_slug = 'date', bool $by_post_name = false ): void {
	\wpinc\post\add_rewrite_rules( $post_type, $slug, $date_slug, $by_post_name );
}

/**
 * Adds single page rewrite rules.
 *
 * @param string $post_type    Post type.
 * @param string $slug         Struct base.
 * @param bool   $by_post_name Whether to use post name for URL.
 */
function add_post_type_rewrite_rules( string $post_type, string $slug = '', bool $by_post_name = false ): void {
	\wpinc\post\add_post_type_rewrite_rules( $post_type, $slug, $by_post_name );
}

/**
 * Adds filter for post type links.
 * For making pretty link of custom post types.
 *
 * @param string $post_type    Post type.
 * @param bool   $by_post_name Whether to use post name for URL.
 */
function add_post_type_link_filter( string $post_type, bool $by_post_name = false ): void {
	\wpinc\post\add_post_type_link_filter( $post_type, $by_post_name );
}

/**
 * Adds archive rewrite rules.
 * Need to set 'has_archive => true' when registering the post type.
 *
 * @param string $post_type Post type.
 * @param string $slug      Archive slug.
 */
function add_archive_rewrite_rules( string $post_type, string $slug = '' ): void {
	\wpinc\post\add_archive_rewrite_rules( $post_type, $slug );
}

/**
 * Adds archive link filter.
 *
 * @param string $post_type Post type.
 * @param string $slug      Archive slug.
 */
function add_archive_link_filter( string $post_type, string $slug = '' ): void {
	\wpinc\post\add_archive_link_filter( $post_type, $slug );
}

/**
 * Adds date archive rewrite rules.
 *
 * @param string $post_type Post type.
 * @param string $slug      Archive slug.
 * @param string $date_slug Date slug.
 */
function add_date_archive_rewrite_rules( string $post_type, string $slug = '', string $date_slug = 'date' ): void {
	\wpinc\post\add_date_archive_rewrite_rules( $post_type, $slug, $date_slug );
}

/**
 * Adds date archive link filter.
 *
 * @param string $post_type Post type.
 * @param string $slug      Archive slug.
 * @param string $date_slug Date slug.
 */
function add_date_archive_link_filter( string $post_type, string $slug = '', string $date_slug = 'date' ): void {
	\wpinc\post\add_date_archive_link_filter( $post_type, $slug, $date_slug );
}

/**
 * Retrieves an item from a query string.
 *
 * @param string $key Query key.
 * @param string $url URL.
 * @return string A query value.
 */
function get_query_arg( string $key, string $url ): string {
	return \wpinc\post\get_query_arg( $key, $url );
}


// -----------------------------------------------------------------------------


/**
 * Retrieves post type as page title.
 *
 * @param string $prefix  Prefix.
 * @param bool   $display (Optional) Whether to display or retrieve title. Default true.
 */
function post_type_title( string $prefix = '', bool $display = true ) {
	return \wpinc\post\post_type_title( $prefix, $display );
}

/**
 * Enables simple default slugs.
 *
 * @param string|string[] $post_type_s Post types.
 */
function enable_simple_default_slug( $post_type_s = array() ) {
	\wpinc\post\enable_simple_default_slug( $post_type_s );
}
