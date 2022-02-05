<?php
/**
 * Post Type
 *
 * @package Sample
 * @author Takuto Yanagida
 * @version 2022-02-05
 */

namespace sample {
	require_once __DIR__ . '/post/content.php';
	require_once __DIR__ . '/post/custom-date.php';
	require_once __DIR__ . '/post/list-table-column.php';
	require_once __DIR__ . '/post/multi-entry.php';
	require_once __DIR__ . '/post/post-type.php';
	require_once __DIR__ . '/post/query.php';
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


	// -------------------------------------------------------------------------


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


	// -------------------------------------------------------------------------


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


	// -------------------------------------------------------------------------


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


	// -------------------------------------------------------------------------


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


	// -------------------------------------------------------------------------


	/**
	 * Displays the post loop.
	 *
	 * @param \WP_Post[]  $ps   Array of post objects.
	 * @param string      $slug The slug name for the generic template.
	 * @param string|null $name The name of the specialized template. Default null.
	 * @param array       $args Additional arguments passed to the template. Default array().
	 */
	function the_loop( array $ps, string $slug, string $name = null, array $args = array() ): void {
		\wpinc\post\the_loop( $ps, $slug, $name, $args );
	}

	/**
	 * Displays each page with custom page template.
	 *
	 * @param \WP_Post[]  $ps   Array of post objects.
	 * @param string      $slug The slug name for the generic template.
	 * @param string|null $name (Optional) The name of the specialized template. Default null.
	 * @param array       $args (Optional) Additional arguments passed to the template. Default array().
	 */
	function the_loop_with_page_template( array $ps, string $slug, ?string $name = null, array $args = array() ): void {
		\wpinc\post\the_loop_with_page_template( $ps, $slug, $name, $args );
	}

	/**
	 * Adds post type query.
	 *
	 * @param string $post_type     Post type.
	 * @param int    $post_per_page Posts per page.
	 * @param array  $args          Arguments for get_posts.
	 * @return array Arguments.
	 */
	function add_post_type_query( string $post_type, int $post_per_page, array $args = array() ): array {
		return \wpinc\post\add_post_type_query( $post_type, $post_per_page, $args );
	}

	/**
	 * Adds taxonomy query.
	 *
	 * @param string          $taxonomy    Taxonomy.
	 * @param string|string[] $term_slug_s Array of term slugs or a term slug.
	 * @param array           $args        Arguments for get_posts.
	 * @return array Arguments.
	 */
	function add_tax_query( string $taxonomy, $term_slug_s, array $args = array() ): array {
		return \wpinc\post\add_tax_query( $taxonomy, $term_slug_s, $args );
	}

	/**
	 * Adds taxonomy query with terms of a specific post.
	 *
	 * @param string       $taxonomy Taxonomy.
	 * @param int|\WP_Post $post     Post ID or object.
	 * @param array        $args     Arguments for get_posts.
	 * @return array Arguments.
	 */
	function add_tax_query_with_term_of( string $taxonomy, $post, array $args = array() ): array {
		return \wpinc\post\add_tax_query_with_term_of( $taxonomy, $post, $args );
	}

	/**
	 * Adds custom sticky post query.
	 *
	 * @param array $args Arguments for get_posts.
	 * @return array Arguments.
	 */
	function add_custom_sticky_query( array $args = array() ): array {
		return \wpinc\post\add_custom_sticky_query( $args );
	}

	/**
	 * Adds upcoming event post query.
	 *
	 * @param int   $offset_year  Offset of year. Default 0.
	 * @param int   $offset_month Offset of month. Default 0.
	 * @param int   $offset_day   Offset of day. Default 0.
	 * @param array $args         Arguments for get_posts.
	 * @return array Arguments.
	 */
	function add_upcoming_post_query( int $offset_year = 1, int $offset_month = 0, int $offset_day = 0, array $args = array() ): array {
		return \wpinc\post\add_upcoming_post_query( $offset_year, $offset_month, $offset_day, $args );
	}

	/**
	 * Adds page query.
	 *
	 * @param array $args Arguments for get_posts.
	 * @return array Arguments.
	 */
	function add_page_query( array $args = array() ): array {
		return \wpinc\post\add_page_query( $args );
	}

	/**
	 * Adds child page query.
	 *
	 * @param int|null $parent_id Page ID of the parent page.
	 * @param array    $args      Arguments for get_posts.
	 * @return array Arguments.
	 */
	function add_child_page_query( ?int $parent_id = null, array $args = array() ): array {
		return \wpinc\post\add_child_page_query( $parent_id, $args );
	}

	/**
	 * Adds sibling page query.
	 *
	 * @param int|null $sibling_id Page ID of the sibling page.
	 * @param array    $args       Arguments for get_posts.
	 * @return array Arguments.
	 */
	function add_sibling_page_query( ?int $sibling_id = null, array $args = array() ): array {
		return \wpinc\post\add_sibling_page_query( $sibling_id, $args );
	}

	/**
	 * Adds post objects.
	 *
	 * @param \WP_Post[] $augend Array of post objects to which others are added.
	 * @param \WP_Post[] $addend Array of post objects which are added to others.
	 * @param int|null   $count  Counts of total number.
	 * @return \WP_Post[] Array of post objects.
	 */
	function add_posts( array $augend, array $addend, ?int $count = null ): array {
		return \wpinc\post\add_posts( $augend, $addend, $count );
	}


	// -------------------------------------------------------------------------


	/**
	 * Checks current post type.
	 *
	 * @param string $post_type Post type.
	 * @return bool True if the current post type is $post_type.
	 */
	function is_post_type( string $post_type ): bool {
		return \wpinc\post\is_post_type( $post_type );
	}

	/**
	 * Retrieves post type title.
	 */
	function get_post_type_title() {
		return \wpinc\post\get_post_type_title();
	}
}

namespace sample\event {
	require_once __DIR__ . '/post/post-type-event.php';

	/**
	 * Registers event-like post type.
	 *
	 * @param array $args Arguments.
	 */
	function register_post_type( array $args = array() ): void {
		\wpinc\post\event\register_post_type( $args );
	}

	/**
	 * Sets columns of list table.
	 *
	 * @param string $post_type Post type.
	 * @param bool   $add_cat   Whether to add {$post_type}_category taxonomy.
	 * @param bool   $add_tag   Whether to add {$post_type}_tag taxonomy.
	 */
	function set_admin_column( string $post_type, bool $add_cat, bool $add_tag ): void {
		\wpinc\post\event\set_admin_column( $post_type, $add_cat, $add_tag );
	}

	/**
	 * Adds event duration columns.
	 *
	 * @param string $post_type Post type.
	 * @param array  $cs        Columns.
	 * @return array Added columns.
	 */
	function add_duration_column( string $post_type, array $cs = array() ): array {
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
	function format_duration( int $post_id, array $formats, string $date_format, bool $do_translate ): string {
		return \wpinc\post\event\format_duration( $post_id, $formats, $date_format, $do_translate );
	}
}

namespace sample\news {
	require_once __DIR__ . '/post/post-type-news.php';

	/**
	 * Registers news-like post type.
	 *
	 * @param string $post_type Post type.
	 * @param string $slug      Parma struct base.
	 * @param array  $labels    Labels.
	 * @param array  $args      Arguments for register_post_type().
	 */
	function register_post_type( string $post_type = 'news', string $slug = '', array $labels = array(), array $args = array() ): void {
		\wpinc\post\news\register_post_type( $post_type, $slug, $labels, $args );
	}

	/**
	 * Sets columns of list table.
	 *
	 * @param string $post_type Post type.
	 * @param bool   $add_cat   Whether to add {$post_type}_category taxonomy.
	 * @param bool   $add_tag   Whether to add {$post_type}_tag taxonomy.
	 */
	function set_admin_column( string $post_type, bool $add_cat, bool $add_tag ): void {
		\wpinc\post\news\set_admin_column( $post_type, $add_cat, $add_tag );
	}
}
