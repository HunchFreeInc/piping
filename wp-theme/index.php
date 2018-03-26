<?php
use Com\Hunchfree\Wp\Themes\Hffoundation as Hffoundation;

/**
 * Default template for drawing content when no other template is available.
 *
 * There are 3 distinct output types in this file:
 * 1) When a 404 type request is made
 * 2) When a singular post type is requested
 * 3) When anything else is requested (displaying lists of posts)
 *
 * This can be completely overridden by copying it to your child theme and editing it there.
 *
 * @see https://developer.wordpress.org/themes/basics/template-hierarchy/ Wordpress Template Hierarchy
 */

# Load the header.php file, which takes care of drawing the html header and visible header
# - note: header.php should load visible_header.php
get_header();

# Take care of drawing out everything between the header and the footer.
# - In case of an exception, draw it out.
# - If there are warnings and the user is a logged in admin, draw them
try {

	$renderer = Hffoundation\Theme_Front_End::get_instance();
	if ( !is_object( $renderer ) ) {
		$this->_notices[] = "Failed to get renderer instance";
		throw new \Exception("Failed to get renderer instance", 10001);
	}

	# Set up our placable sidebars, if they are used
	$block_sb_side_one = '';
	$block_sb_side_two = '';
	$extra_main_wrapper_css = '';
	$shell_content_extra_css = '';
	$placeable_sidebar_use = (int)$renderer->get_best_mod('placeable_sidebars_use', 0);
	if ( 1 === $placeable_sidebar_use ) {

		# Placeable Sidebar Area - Side One : left of any content area
		$widgets = $renderer->get_sidebar_widgets("sb_content_side_one", "sb_content_side_one_css", 'small-12' );
		if ( !empty( $widgets ) ) {
			$sidebar_id = $renderer->get_last_used_sidebar_id();
			$block_sb_side_one = <<<HTML
<div id="sidebar-side-one" class="columns small-12 medium-4 large-3 sidebar-wrapper">
	<div class="row sidebar widget-wrapper sidebar-{$sidebar_id}">
		{$widgets}
	</div>
</div>

HTML;

		}

		# Placeable Sidebar Area - Side Two : Right of any content area
		$widgets = $renderer->get_sidebar_widgets("sb_content_side_two", "sb_content_side_two_css", 'small-12' );
		if ( !empty( $widgets ) ) {
			$sidebar_id = $renderer->get_last_used_sidebar_id();
			$block_sb_side_two = <<<HTML
<div id="sidebar-side-two" class="columns small-12 medium-4 large-3 sidebar-wrapper">
	<div class="row sidebar widget-wrapper sidebar-{$sidebar_id}">
		{$widgets}
	</div>
</div>

HTML;

		}

		# Based on known content areas, set up any extra content wrapper styles
		# the left and right sidebars use : small-12 medium-4 large-3
		if ( !empty( $block_sb_side_one ) ) {
			if ( !empty( $block_sb_side_two ) ) {
				$extra_main_wrapper_css = 'medium-4 large-6';
				$shell_content_extra_css = 'two-sidebars';
			} else {
				$extra_main_wrapper_css = 'medium-8 large-9';
				$shell_content_extra_css = 'one-sidebar';
			}
		} else if ( !empty( $block_sb_side_two ) ) {
			$extra_main_wrapper_css = 'medium-8 large-9';
			$shell_content_extra_css = 'one-sidebar';
		}
	}

	if ( is_404() ) {
		theme_draw_four_zero_four();
	} else {

		if ( is_singular() ) {
			theme_draw_singular(
				$renderer,
				$block_sb_side_one, $block_sb_side_two,
				$extra_main_wrapper_css, $shell_content_extra_css
			);
		} else {
			theme_draw_non_singular(
				$renderer,
				$block_sb_side_one, $block_sb_side_two,
				$extra_main_wrapper_css, $shell_content_extra_css
			);
		}
	}
} catch ( \Exception $e ) {
	if ( WP_DEBUG || ( is_user_logged_in() && current_user_can('activate-plugins') ) ) {
		echo "<p>Exception Encountered:</p><pre>" . print_r($e, true) . "</pre>";
		if ( isset( $o_renderer ) && is_object( $o_renderer ) ) {
			$notices = $o_renderer->get_warnings();
			if ( 0 < count($notices) ) {
				echo '<div><h4>Notices:</h4><pre>' . print_r($notices, true) . '</pre></div>';
			}
		}
	}
}


/**
 * Draw out the contents of a 404 page
 */
function theme_draw_four_zero_four() {

	$title_bit = '<h1>404 Not Found</h1>';



	echo <<<HTML
	<div class="grid-container" style="padding-bottom: 3rem;">
	  
	  <div class="grid-x grid-margin-x hero align-center text-center align-middle" style="background: url('/wp-content/uploads/2018/03/240_f_64531819_2uwloiom6jyz6dgqktxjcg7pkofn91h1.jpg '); background-size: cover; background-position: center center;">
	  
	    <div class="large-8 medium-10 small-12 cell content white-text">

	      <h1>Oops!</h1>

	      <p>We couldn't find the content you were looking for.</p>

	      <a href="/" class="button red">Return Home</a>

	    </div>
	  
	  </div>
	
	</div>

HTML;

}

/**
 * Draw out the contents of a singular post type (posts, pages, custom post types)
 *
 * @param Hffoundation\Theme_Front_End $renderer	For utilities related to drawing out content
 * @param string $block_sb_side_one Content of any left sidebar
 * @param string $block_sb_side_two Content of any right sidebar
 * @param string $extra_main_wrapper_css Any css classes to stuff into the outer wrapper for sidebar placement
 * @param string $shell_content_extra_css Any css classes to stuff into the article for sidebar placement
 *
 * @throws Exception when a serious issue occurs
 *
 * @todo load theme test data and go through to ensure everything looks good by default
 * @todo paged_navigation using <!--nextpage--> tag
 * @todo for singular attachments, insert the attachment into the post content
 * @todo style comments
 * @todo let pingbacks and trackbacks show up even if comments are disabled
 */
function theme_draw_singular(
	Hffoundation\Theme_Front_End &$renderer,
	$block_sb_side_one = '', $block_sb_side_two = '',
	$extra_main_wrapper_css = '', $shell_content_extra_css = ''
) {

	if ( !have_posts() ) {
		$this->_notices[] = "Attempting to draw singular without any posts!";
		throw new \Exception("Non 404, singular, without any posts!", 10001);

	} else {
		while ( have_posts() ) {
			# load the current post into the global space for easy access
			the_post();

			# grab access to the currently loaded post
			global $post;

			# get the post id, css classes for it, and post type
			$post_id = get_the_ID();
			$css_classes = implode(' ', get_post_class());
			$post_type = get_post_type( $post );

			# We are drawing the entire post.

			###
			### The Title Block
			### - you can set a hide_title custom variable on a post to hide the title
			### - alternately, you can use is_page() and similar functions to force no title display for specific post types

			$block_title = '';

			# block_main_title : a component of block_title
			$block_main_title = '';
			if ( post_type_supports( $post_type, 'title' ) ) {
				$page_title = '';
				$hide_title = get_post_meta( $post_id, 'hide_title', true);
				if ( '1' !== $hide_title ) {
					$page_title = get_the_title();
					if ( !empty( $page_title ) ) {
						$page_title = "<h1>{$page_title}</h1>";
					}
				}
				if ( !empty( $page_title ) ) {
					$block_main_title = <<<HTML
<div id="s-page-title" class="page-title">
	{$page_title}
</div>

HTML;

				}
			}

$footer = get_footer();

			# If any components of block_title are not empty, set up the block_title element
			if ( !empty( $block_main_title ) ) {
				$block_title = <<<HTML
<header id="s-page-title-wrap" class="page-title-wrap columns">
	{$block_main_title}
	{$footer}
</header>

HTML;

			}

			# Determine if there are sub pages and set up sub page navigation
			#global $pages, $page;
			global $pages;
			$block_subpage_nav = '';
			if ( is_array( $pages ) && 1 < count( $pages ) ) {
				$is_paged = true;
			} else if ( is_int($pages) && 1 < $pages ) {
				$is_paged = true;
			} else {
				$is_paged = false;
			}
			if ( $is_paged ) {
				# Notice that this is filtered via the Theme_Front_End class (override_wp_link_pages)
				$paged_nav = wp_link_pages(
					array(
						'echo' => 0,
						'before' => __('Pages:'),
						'after' => '',
						'separator' => '</li><li>',
						'nextpagelink' => '',
						'previouspagelink' => ''
					)
				);
				if ( !empty($paged_nav) ) {
					$block_subpage_nav = '<nav id="s-page-paged-nav" class="columns theme-navi navi-paged"><ul class="pagination">' . $paged_nav . '</ul></nav>';
				}
			}

			###
			### The Content Block
			###

			$block_content = '';

			# post_content - a component block_content
			$post_content = '';
			if ( post_type_supports( $post_type, 'editor' ) ) {

				$post_content = get_the_content( 'Read More...' );
				$post_content = apply_filters( 'the_content', $post_content );
				$post_content = str_replace( ']]>', ']]&gt;', $post_content );

			}

			# featured image - a component of block_content
			# - if you want to do something different with the featured image, edit the below code.
			$featured_image = '';
			if ( current_theme_supports('post-thumbnails') && has_post_thumbnail() ) {
				if ( 'attachment' == "{$post_type}" || 'post' == "{$post_type}" ) {
					$post_thumbnail_id = get_post_thumbnail_id( $post_id );
					$post_image_data = wp_get_attachment_image_src( $post_thumbnail_id, 'large' );
					$attachment = get_post( $post_thumbnail_id );
					if ( is_object( $attachment ) ) {
						$thumbnail_alt = ( isset( $attachment->post_title ) && !empty( $attachment->post_title ) ) ? esc_attr( $attachment->post_title ) : esc_attr( get_the_title() );
					} else {
						$thumbnail_alt = esc_attr( get_the_title() );
					}
					$featured_image = <<<HTML
<div class="featured_image"><img src="{$post_image_data[0]}" id="attachment_{$post_thumbnail_id}" width="{$post_image_data[1]}" height="{$post_image_data[2]}" alt="{$thumbnail_alt}" /></div>
HTML;

				}
			}

			# If any component of block_content is not empty, set up the block_content area
			# - by default this draws the featured image before the content.
			# - if you want the feature image floated, add the float to the featured_image div.
			if ( !empty( $post_content ) || !empty( $featured_image ) ) {
				$block_content = <<<HTML
<div id="s-page-content" class="columns page-content">{$featured_image}{$post_content}</div>
HTML;

			}

			# Breadcrumbs
			# - this is an example of how to do breadcrumbs if they are wanted
			# - other available bars are author, date, and any taxonomy name
			$block_breadcrumbs = '';
			# - comment out the following 7 lines if you do not want breadcrumbs
			$wanted_bars = array('main');
			$crumb_bars = $renderer->get_nav_breadcrumbs( $wanted_bars );
			if ( is_array($crumb_bars) && 0 < count($crumb_bars) ) {
				if ( array_key_exists('main', $crumb_bars) ) {
					$block_breadcrumbs = '<div class="columns">' . $crumb_bars['main'] . '</div>';
				}
			}

			# Next, Up, and Previous Navigation
			# - next			: next post of the same type
			# - up			: up to any available post type archive (or nothing is displayed)
			# - previous	: previous post of same type
			$block_paged_navigation = '';
			# comment out the next block of code if you do not want next, up, and previous navigation
			list( $up_one, $previous_item, $next_item ) = $renderer->get_nav_up_prev_next();
			if ( !empty($up_one) || !empty($previous_item) || !empty( $next_item ) ) {
				if ( empty( $up_one ) ) {
					$up_one = '&nbsp;';
				}
				if ( empty( $previous_item ) ) {
					$previous_item = '&nbsp;';
				}
				if ( empty( $next_item ) ) {
					$next_item = '&nbsp;';
				}
				$block_paged_navigation = <<<HTML
<nav class="theme-navi next-previous columns">
	<div class="row">
		<div class="columns small-12 medium-4 large-5 medium-text-left">{$previous_item}</div>
		<div class="columns small-12 medium-4 large-2 medium-text-center">{$up_one}</div>
		<div class="columns small-12 medium-4 large-5 medium-text-right">{$next_item}</div>
	</div>
</nav>

HTML;

			}

			# Comments
			# - rather than commenting out the following, use theme-configuration to remove comments post type support
			# - for any post types that you don't want to show comments for.
			$block_comments = '';
			if ( post_type_supports( $post_type, 'comments') ) {
				ob_start();
				comments_template('', true);
				$comments = ob_get_contents();
				ob_end_clean();
				if ( !empty( $comments ) ) {
					$block_comments = <<<HTML
<div id="comment-wrap" class="columns">
	{$comments}
</div>

HTML;

				}
			}

			# Meta - author (or author linked)
			# - Many themes won't use these at all
			# - If you do need any of them, re-write the drawing code as wanted and then
			# - use them to build out the content you want in different places.
			$block_byline = '';
			$block_taxes = '';
			$use_meta = ( is_page() )? false : true;
			if ( $use_meta ) {
				# Non pages should include the following:
				# byline (under title) Posted by linked-author on Jan 25 2014
				# footer (above comments) Categorized <linked-cat>, Tags: <linked-tags>
				$meta_author = '';
				if ( post_type_supports( $post_type, 'author' ) ) {
					if ( !empty( $post->post_author ) ) {
						$author = get_userdata( $post->post_author );
						$author_url = get_author_posts_url( $post->post_author );
						$author_name = ( empty( $author->nickname ) )? $author->display_name : $author->nickname;
						$meta_author = <<<HTML
<span class="meta meta-author meta-author-{$author->ID}"><a href="{$author_url}">{$author_name}</a></span>
HTML;

					}
				}

				# Meta - published date
				$meta_published = '';
				$meta_published_date = get_the_date('M j Y');
				if ( !empty( $meta_published_date ) ) {
					$meta_published = <<<HTML
<span class="meta meta-date meta-published">{$meta_published_date}</span>
HTML;

				}

				if ( !empty($meta_published) || !empty( $meta_author) ) {
					if ( !empty($meta_author) ) {
						if ( !empty( $meta_published ) ) {
							$block_byline = '<div class="page-byline">Posted by ' . $meta_author . ' on ' . $meta_published . '</div>';
						} else {
							$block_byline = '<div class="page-byline">Posted by ' . $meta_author . '</div>';
						}
					} else {
						$block_byline = '<div class="page-byline">Posted on ' . $meta_published . '</div>';
					}
				}

				# Meta - modified date
				/* How to get the modified date if wanted
				$meta_modified = '';
				$meta_modified_date = get_the_modified_date('M j Y');
				if ( !empty( $meta_modified_date ) && "{$meta_modified_date}" != "{$meta_published_date}" ) {
					$meta_modified = <<<HTML
<span class="meta meta-date meta-modified">Last modified on {$meta_modified_date}</span>
HTML;

				}
				*/

				# Meta - taxonomy terms, one set for each taxonomy
				/* example of getting all taxonomies
				$meta_taxonomies = '';
				if ( function_exists('get_object_taxonomies') && !empty( $post_type ) ) {
					$tax_lists = array();
					$taxonomies = get_object_taxonomies( $post_type, 'objects' );
					if ( is_array( $taxonomies ) && 0 < count( $taxonomies ) ) {
						$unwanted = array('post_format');
						foreach ( $taxonomies as $tax_name => $tax_info ) {
							if ( !in_array($tax_name, $unwanted) && $tax_info->public ) {
								if ( isset($tax_info->labels->name) && !empty($tax_info->labels->name) ) {
									$tax_display_name = $tax_info->labels->name;
								} else {
									$tax_display_name = ucwords( str_replace('_', ' ', $tax_name) );
								}
								$terms = get_the_terms($post_id, $tax_name);
								if ( is_array($terms) && 0 < count($terms) ) {
									$term_links = array();
									foreach ( $terms as $term ) {
										$term_link = get_term_link($term);
										if ( !is_wp_error($term_link) ) {
											$term_link = "{$term_link}";
											$term_links[] = '<a href="' . $term_link . '" class="meta-tax">' . $term->name . '</a>';
										}
									}
									if ( 0 < count($term_links) ) {
										$term_listing = implode('</span><span class="meta-tax-link">', $term_links);
										$tax_lists["{$tax_name}"] = <<<HTML
<p class="meta-tax meta-tax-{$tax_name}"><span class="meta-tax-title">{$tax_display_name}:</span>
	<span class="meta-tax-link">{$term_listing}</span>
</p>
HTML;

									}
								}
							}
						}
						if ( 0 < count($tax_lists) ) {
							$meta_taxonomies = implode("\r\n", $tax_lists);
						}
					}
				}
				*/
				$category_list = '';
				$cats = get_the_terms( $post_id, 'category' );
				if ( is_array($cats) && 0 < count($cats) ) {
					$cat_links = array();
					foreach ( $cats as $cat ) {
						$cat_link = get_term_link( $cat );
						if ( !is_wp_error( $cat_link ) ) {
							$cat_links[] = '<a href="' . $cat_link . '" class="meta-tax">' . $cat->name . '</a>';
						}
					}
					if ( 0 < count($cat_links) ) {
						$cat_listing = implode(', ', $cat_links);
						$category_list = <<<HTML
<span class="meta-category"><span class="meta-title">Categorized</span> <span class="meta-taxes">{$cat_listing}</span></span>
HTML;
					}
				}

				$tag_list = '';
				$tags = get_the_terms( $post_id, 'post_tag' );
				if ( is_array($tags) && 0 < count($tags) ) {
					$tag_links = array();
					foreach ( $tags as $tag ) {
						$tag_link = get_term_link( $tag );
						if ( !is_wp_error( $tag_link ) ) {
							$tag_links[] = '<a href="' . $tag_link . '" class="meta-tax">' . $tag->name . '</a>';
						}
					}
					if ( 0 < count($tag_links) ) {
						$tag_listing = implode(', ', $tag_links);
						$tag_list = <<<HTML
<span class="meta-post_tag"><span class="meta-title">Tagged</span> <span class="meta-taxes">{$tag_listing}</span></span>
HTML;
					}
				}

				if ( !empty($category_list ) ) {
					if ( !empty( $tag_list ) ) {
						$block_taxes = '<div class="page-meta">' . $category_list . ' ' . $tag_list . '</div>';
					} else {
						$block_taxes = '<div class="page-meta">' . $category_list . '</div>';
					}
				} else if ( !empty( $tag_list ) ) {
					$block_taxes = '<div class="page-meta">' . $tag_list . '</div>';
				}

				# Meta - comments rss feed
				/* how to create a comments feed link if wanted
				$meta_comments = '';
				if ( post_type_supports( $post_type, 'comments' ) ) {
					$comments_feed_link = get_post_comments_feed_link( $post->ID );
					if ( !empty( $comments_feed_link ) ) {
						$meta_comments = <<<HTML
<span class="meta meta-comments meta-rss"><a href="{$comments_feed_link}">Comments RSS</a></span>
HTML;

					}
				}
				*/
			}

			###
			### Sidebars are handled near the top of this file.
			###

			/**
			 * The following does all of the actual drawing
			 */
			echo <<<HTML
<div id="shell_content" class="row-expanded {$shell_content_extra_css} shell-content-singular">
	<div class="row shell-content-inner">
		{$block_sb_side_one}
		<div class="columns small-12 {$extra_main_wrapper_css}">
			<article id="post_{$post_id}" class="{$css_classes} row article-wrap">
				{$block_breadcrumbs}
				{$block_title}
				{$block_byline}
				{$block_subpage_nav}
				{$block_content}
				{$block_taxes}
				{$block_paged_navigation}
				{$block_comments}
			</article>
		</div>
		{$block_sb_side_two}
	</div>
</div>

HTML;

		}
	}
}

/**
 * Draw non singular displays other than the 404 page
 *
 * Basically, this will handle drawing of:
 * - all post type archives (including the blog)
 * - date archives
 * - author archives
 * - search results
 * - taxonomy term archives
 *
 * @todo style sticky posts
 * @todo float featured images left of content instead of using columns
 * @todo make sure each post has an ID attribute
 * @todo implement post formats
 * @todo if excerpt supported and not empty, use that in place of content
 * @todo add css styles for post formats
 * @todo add divider between each post
 * @todo provide ability to draw in columns at different screen sizes
 * @todo fix taxonomy term page titles using taxonomy labels
 * @todo fix post type archive page titles using post type labels
 *
 * @see https://codex.wordpress.org/Post_Formats Post Format Details
 *
 * @param Hffoundation\Theme_Front_End $renderer
 * @param string $block_sb_side_one Content of any left sidebar
 * @param string $block_sb_side_two Content of any right sidebar
 * @param string $extra_main_wrapper_css Any css classes to stuff into the outer wrapper for sidebar placement
 * @param string $shell_content_extra_css Any css classes to stuff into the article for sidebar placement
 */
function theme_draw_non_singular(
	Hffoundation\Theme_Front_End &$renderer,
	$block_sb_side_one = '', $block_sb_side_two = '',
	$extra_main_wrapper_css = '', $shell_content_extra_css = ''
) {

	/** @var \WP_Query $wp_query */
	global $wp_query;

	# $is_hierarchical = false;
	$page_title = '';
	$page_summary = '';
	$page_rss_link = '';

	$current_term = null;

	# Set up any available page title, page summary, rss feed link, and wanted sidebars
	switch ( true ) {
		case ( is_home() ):
			# If using static front page, then the page for posts might have a title and excerpt
			# Otherwise we use the site name and description for the title and summary
			$blog_page_id = 0;
			$page_rss_link = get_bloginfo('rss_url');
			if ( 'page' == get_option('show_on_front', '') ) {
				$blog_page_id = get_option( 'page_for_posts', 0);
				if ( 0 < $blog_page_id ) {
					$blog_post = get_post( $blog_page_id );
					$page_title = $blog_post->post_title;
					$page_summary = $blog_post->post_excerpt;
				}
			}
			if ( 0 == $blog_page_id ) {
				$page_title = get_bloginfo('name');
				$page_summary = get_bloginfo('description');
			}
			break;
		case ( is_category() ):
			# $is_hierarchical = is_taxonomy_hierarchical('category');
			$item_id = $wp_query->get_queried_object_id();
			$current_term = get_term( $item_id, 'category', OBJECT );
			$page_title = __('Posts Categorized', $renderer->get_text_domain()) . ' ' . $current_term->name;
			$page_rss_link = get_term_feed_link( $item_id, 'category' );
			$page_summary = ( isset( $current_term->description ) )? $current_term->description : '';
			break;
		case ( is_tag() ):
			# $is_hierarchical = is_taxonomy_hierarchical('post_tag');
			$item_id = $wp_query->get_queried_object_id();
			$current_term = get_term( $item_id, 'post_tag', OBJECT );
			$page_title = __('Posts Tagged ', $renderer->get_text_domain()) . ' ' . $current_term->name;
			$page_rss_link = get_term_feed_link( $item_id, 'post_tag' );
			$page_summary = ( isset( $current_term->description ) )? $current_term->description : '';
			break;
		case ( is_search() ):
			$search_query = get_search_query();
			if ( !empty( $search_query ) ) {
				$page_title = __("Search results for", $renderer->get_text_domain()) . " &quot;" . stripcslashes($search_query) . "&quot;";
			}
			break;
		case ( is_author() ):
			$author_id = $wp_query->get_queried_object_id();
			$author = get_userdata( $author_id );
			$page_title = __("Articles by", $renderer->get_text_domain()) . " {$author->display_name}";
			$page_rss_link = get_author_feed_link( $author_id );
			$page_summary = get_user_meta( $author->ID, 'description', true );
			break;
		case ( is_date() ):
			$y = get_query_var('year');
			if ( is_year() ) {
				$page_title = "{$y} " . __("Archives", $renderer->get_text_domain());
			} else if ( is_month() ) {
				$m = get_query_var('monthnum');
				$query_date = "{$y}-{$m}-01";
				$m_name = date('M Y', strtotime( $query_date ) );
				$page_title = "{$m_name} " . __("Archives", $renderer->get_text_domain());
			} else if ( is_day() ) {
				$m = get_query_var('monthnum');
				$d = get_query_var('day');
				$query_date = "{$y}-{$m}-{$d}";
				$d_name = date('M jS Y', strtotime( $query_date ) );
				$page_title = "{$d_name} " . __("Archives", $renderer->get_text_domain());
			} else {
				$page_title = 'Date Archives';
			}
			break;
		default:
			$public_taxonomies = $renderer->get_taxonomies();
			$found = false;
			foreach ( $public_taxonomies as $tax_name => $tax_data ) {
				if ( is_tax( $tax_name ) ) {
					# We are on a taxonomy term archive page
					$found = true;
					$term_id = $wp_query->get_queried_object_id();
					$current_term = get_term( $term_id, $tax_name, OBJECT );
					$page_rss_link = get_term_feed_link( $term_id, $tax_name );
					$page_title = $current_term->name;
					$page_summary = ( isset( $current_term->description ) ) ? ltrim( rtrim( $current_term->description ) ) : '';
					# $is_hierarchical = is_taxonomy_hierarchical( $tax_name );
					break;
				}
			}
			if ( !$found ) {
				$public_post_types = $renderer->get_post_types();
				foreach ( $public_post_types as $type_name => $type_data ) {
					if ( is_post_type_archive( $type_name ) ) {
						$found = true;
						$page_rss_link = get_post_type_archive_feed_link( $type_name );
						$page_title = $type_name;
						$page_summary = '';
						break;
					}
				}
			}
			if ( !$found ) {
				$page_rss_link = '';
				$page_title = '';
				$page_summary = '';
			}
			break;
	}

	###
	### Page Title Block
	###

	$block_title = '';

	$page_title_area = '';
	if ( !empty( $page_title ) && !empty( $page_rss_link ) ) {
		$page_title_area = <<<HTML
<div class="columns small-10 medium-11">
	<h1 class="multi-title">{$page_title}</h1>
</div>
<div class="columns small-2 medium-1 text-right">
	<a href="{$page_rss_link}" class="rss-feed">RSS</a>
</div>
HTML;

	} else if ( !empty( $page_title ) ) {
		$page_title_area = <<<HTML
<div class="columns">
	<h1 class="multi-title">{$page_title}</h1>
</div>
HTML;

	} else if ( !empty( $page_rss_link ) ) {
		$page_title_area = <<<HTML
<div class="columns">
	<a href="{$page_rss_link}" class="rss-feed">RSS</a>
</div>
HTML;

	}

	if ( !empty($page_title_area) ) {
		$block_title = <<<HTML
<header id="multi-page-title" class="page-title-wrap row">
	{$page_title_area}
</header>
HTML;

	}

	###
	### Page Description (term.description)
	###

	$block_intro = '';
	if ( !empty( $page_summary ) ) {
		$block_intro = '<div class="row page-description"><div class="columns"><p>' . $page_summary . '</p></div></div>';
	}

	###
	### The Post List
	###
	$block_post_list = '';

	if ( have_posts() ) {
		$article_list = '';

		global $post;
		while( have_posts() ) {
			the_post();
			$id = get_the_ID();
			$post_type = get_post_type( $id );
			$css_classes = implode(' ', get_post_class( $post_type, $id));
			$link = get_permalink($id);

			# How we draw each post depends on that posts format

			$post_title = get_the_title();

			if ( current_theme_supports('post-formats') && post_type_supports( $post_type, 'post-formats') ) {
				$post_format = get_post_format( $id );
			} else {
				$post_format = '';
			}
			$post_title_block = '';
			$post_content_block = '';
			$use_default = false;
			$use_byline = false;
			$use_footer = false;
			switch ( $post_format ) {
				case 'aside':
					# No title. Otherwise normal.
					$post_content = '';
					if ( post_type_supports( $post_type, 'editor' )&& false !== strpos( $post->post_content, '<!--more-->' ) ) {
						# use the content up to the more tag
						$post_content = get_the_content('Read More...');
						$post_content = apply_filters('the_content', $post_content);
						$post_content = str_replace(']]>', ']]&gt;', $post_content);
					}
					if ( empty( $post_content ) && post_type_supports( $post_type, 'excerpt' ) && !empty( $post->post_excerpt ) ) {
						# Use the user written excerpt
						$post_content = '<p>' . get_the_excerpt() . '</p>';
					}
					if ( empty( $post_content ) && post_type_supports( $post_type, 'editor' ) ) {
						# Use the auto generated excerpt
						$post_content = get_the_excerpt();
					}
					if( !empty( $post_content ) ) {
						$post_content_block = <<<HTML
<div class="post-content">
{$post_content}
</div>
HTML;
					}
					$use_footer = true;
					break;
				case 'gallery':
					# Should contain a [gallery] shortcode
					# We want to show the first 3 or 4 items and link to the full gallery.
					# for now, just draw it out normally
					$use_default = true;
					break;
				case 'link':
					# if post content consists of one url, link post title to it
					if ( !empty( $post_title ) ) {
						if ( post_type_supports('editor', $post_type) ) {
							$raw_content = ltrim(rtrim($post->post_content));
							if ( filter_var($raw_content, FILTER_VALIDATE_URL) !== FALSE ) {
								$title_link_url = $raw_content;
								$post_title_block = <<<HTML
<div class="post-title">
	<h2><a href="{$title_link_url}">{$post_title}</a></h2>
</div>
HTML;

								# Since we are linking elsewhere, we only include content if we have an excerpt.
								if ( post_type_supports( $post_type, 'excerpt' ) && !empty( $post->post_excerpt ) ) {
									$post_content = '<p>' . get_the_excerpt() . '</p>';
								}
								if( !empty( $post_content ) ) {
									$post_content_block = <<<HTML
<div class="post-content">
{$post_content}
</div>
HTML;
								}
							} else {
								$use_default = true;
							}
						} else {
							$use_default = true;
						}
					} else {
						$use_default = true;
					}
					break;
				case 'image':
					# if post has a featured image draw that with the post title as the caption
					# else if post content consists of one image url, draw that image with the post title as the caption
					if ( current_theme_supports('post-thumbnails') && has_post_thumbnail() ) {
						# There is a post thumbnail - grab that
						$post_thumb_id = get_post_thumbnail_id($id);
						$image_data = wp_get_attachment_image_src($post_thumb_id, 'large');
						$attr_title = esc_attr($post_title);
						$image_width = $image_data[0];
						$post_thumbnail = <<<HTML
<a href="{$link}" class="featured-image"><img id="attachment_{$post_thumb_id}" class="wp-image-{$post_thumb_id} size-large" src="{$image_data[0]}" width="{$image_data[1]}" height="{$image_data[2]}" alt="{$attr_title}" /></a>
HTML;

					} else {
						# try to grab the first jpg, gif, png, bitmap, or tiff image available
						$post_thumbnail = '';
						$post_thumb_id = 0;
						$image_width = '';
						$args = array(
							'post_type' => 'attachment',
							'post_status' => 'inherit',
							'post_mime_type' => array(
								'image/jpeg',
								'image/gif',
								'image/png',
								'image/bmp',
								'image/tiff'
							),
							'post_parent' => $id,
							'posts_per_page' => 1
						);
						$attachments = new WP_Query( $args );
						if ( $attachments->have_posts() ) {
							# we found one, grab it
							while ( $attachments->have_posts() ) {
								$attachment = $attachments->next_post();
								$post_thumb_id = $attachment->ID;
								$image_data = wp_get_attachment_image_src( $post_thumb_id, 'large' );
								$image_width = $image_data[0];
								$attr_title = esc_attr( $post_title );
								$post_thumbnail = <<<HTML
<a href="{$link}" class="featured-image"><img id="attachment_{$post_thumb_id}" class="wp-image-{$post_thumb_id} size-large" src="{$image_data[0]}" width="{$image_data[1]}" height="{$image_data[2]}" alt="{$attr_title}" /></a>
HTML;


							}
						}
					}
					if ( !empty( $post_thumbnail ) ) {
						# There was a post thumbnail. Try to wrap the image in a caption.
						$use_footer = true;
						$image_w = ( empty($image_width) )? '' : ' width="' . $image_width . '"';
						$short_code = <<<HTML
[caption id="attachment_{$post_thumb_id}" align="aligncenter" {$image_w}]{$post_thumbnail}{$post_title}[/caption]
HTML;

						$code_text = do_shortcode( $short_code );
						if ( "{$code_text}" != "{$short_code}" ) {
							$post_content_block = <<<HTML
<div class="post-content">
	{$code_text}
</div>
HTML;


						} else {
							$post_title_block = <<<HTML
<div class="post-title">
	<h1>{$post_title}</h1>
</div>
HTML;

							$post_content_block = <<<HTML
<div class="post-content">
	{$post_thumbnail}
</div>
HTML;

						}
					} else {
						$use_default = true;
					}
					break;
				case 'quote':
					# ignore the title
					# if no blockquote in content, wrap content in a blockquote
					if ( post_type_supports( $post_type, 'excerpt' ) && !empty( $post->post_excerpt ) ) {
						# draw the excerpt in a quote with the title stuffed in as a citation if needed
						$excerpt = get_the_excerpt( $post );
						if ( false === strpos( $excerpt, '<cite' ) ) {
							$citation = '<cite>' . $post_title . '</cite>';
						} else {
							$citation = '';
						}
						$post_content_block = <<<HTML
<div class="post-content">
	<blockquote>
		{$excerpt}
		{$citation}
	</blockquote>
</div>
HTML;

						$use_footer = true;
					} else {
						# missing any excerpt - draw it normally
						$use_default = true;
					}
					break;
				case 'status':
					# default drawing - these should look different via styling applied to the post format
					$use_default = true;
					break;
				case 'video':
					# a video or video play list.
					# for now, leave this alone. wordpress should handle it using oembed
					$use_default = true;
					break;
				case 'audio':
					# an audio file or play list. decide what to do
					# for now, leave this alone. wordpress should handle it using oembed
					$use_default = true;
					break;
				case 'chat':
					# a chat transcript - styled via css
					$use_default = true;
					break;
				default:
					# normal use of post components
					$use_default = true;
					break;
			}

			if ( $use_default ) {
				$use_byline = true;
				$use_footer = true;
			}

			$post_byline_block = '';
			if ( $use_byline ) {
				# byline should contain posted by linked-author on Jan 12, 2014

				$meta_author = '';
				if ( post_type_supports( $post_type, 'author' ) ) {
					if ( !empty( $post->post_author ) ) {
						$author = get_userdata( $post->post_author );
						$author_url = get_author_posts_url( $post->post_author );
						$author_name = ( empty( $author->nickname ) )? $author->display_name : $author->nickname;
						$meta_author = <<<HTML
<span class="meta meta-author meta-author-{$author->ID}"><a href="{$author_url}">{$author_name}</a></span>
HTML;

					}
				}

				# Meta - published date
				$meta_published = '';
				$meta_published_date = get_the_date('M j Y');
				if ( !empty( $meta_published_date ) ) {
					$meta_published = <<<HTML
<span class="meta meta-date meta-published">{$meta_published_date}</span>
HTML;

				}

				if ( !empty($meta_published) || !empty( $meta_author) ) {
					if ( !empty($meta_author) ) {
						if ( !empty( $meta_published ) ) {
							$post_byline_block = '<div class="post-byline">Posted by ' . $meta_author . ' on ' . $meta_published . '</div>';
						} else {
							$post_byline_block = '<div class="post-byline">Posted by ' . $meta_author . '</div>';
						}
					} else {
						$post_byline_block = '<div class="post-byline">Posted on ' . $meta_published . '</div>';
					}
				}
			}

			$post_footer_block = '';
			if ( $use_footer ) {
				# footer should contain: Categorized: <one cat-link> Tagged: <up to 3 linked tags>
				$category_data = '';
				$cats = get_the_terms( $id, 'category' );
				if ( is_array($cats) && 0 < count($cats) ) {
					# first category only
					$cat = reset( $cats );
					$cat_link = get_term_link( $cat );
					if ( !is_wp_error( $cat_link ) ) {
						$category_data = <<<HTML
<span class="meta-category"><span class="meta-title">Categorized</span> <span class="meta-taxes"><a href="{$cat_link}" class="meta-tax">{$cat->name}</a></span></span>
HTML;

					}
				}

				$tag_data = '';
				$tags = get_the_terms( $id, 'post_tag' );
				if ( is_array($tags) && 0 < count($tags) ) {
					$tag_links = array();
					$i = 0;
					foreach ( $tags as $tag ) {
						$tag_link = get_term_link( $tag );
						if ( !is_wp_error( $tag_link ) ) {
							$tag_links[] = '<a href="' . $tag_link . '" class="meta-tax">' . $tag->name . '</a>';
						}
						if ( $i < 3 ) {
							$i++;
						} else {
							break;
						}
					}
					if ( 0 < count($tag_links) ) {
						$tag_listing = implode(', ', $tag_links);
						$tag_data = <<<HTML
<span class="meta-post_tag"><span class="meta-title">Tagged</span> <span class="meta-taxes">{$tag_listing}</span></span>
HTML;
					}
				}

				if ( !empty($category_data ) ) {
					if ( !empty( $tag_data ) ) {
						$post_footer_block = '<div class="post-meta">' . $category_data . ' ' . $tag_data . '</div>';
					} else {
						$post_footer_block = '<div class="post-meta">' . $category_data . '</div>';
					}
				} else if ( !empty( $tag_list ) ) {
					$post_footer_block = '<div class="post-meta">' . $tag_data . '</div>';
				}
			}

			if ( $use_default ) {
				if ( !empty( $post_title ) ) {
					$post_title_block = <<<HTML
<div class="post-title">
	<h2><a href="{$link}">{$post_title}</a></h2>
</div>
HTML;

				}
				$post_content = '';
				if ( post_type_supports( $post_type, 'editor' ) && false !== strpos( $post->post_content, '<!--more-->') ) {
					# User used a more tag, grab the content upto it
					$post_content = get_the_content('Read More...');
					$post_content = apply_filters('the_content', $post_content);
					$post_content = str_replace(']]>', ']]&gt;', $post_content);
				} else if ( post_type_supports( $post_type, 'excerpt') && !empty( $post->post_excerpt ) ) {
					# no more tag, but we have an excerpt. use that.
					$post_content = '<p>' . get_the_excerpt() . '</p>';
				} else if ( post_type_supports( $post_type, 'editor') ) {
					# use the wordpress auto generate excerpt.
					$post_content = '<p>' . get_the_excerpt() . '</p>';
				}
				$post_thumbnail = '';
				if ( current_theme_supports('post-thumbnails') && has_post_thumbnail() ) {
					# There is a post thumbnail - grab that
					$post_thumb_id = get_post_thumbnail_id($id);
					$image_data = wp_get_attachment_image_src($post_thumb_id, 'thumbnail');
					$attr_title = esc_attr($post_title);
					$post_thumbnail = <<<HTML
<a href="{$link}" class="featured-image"><img id="attachment_{$post_thumb_id}" class="wp-image-{$post_thumb_id}" src="{$image_data[0]}" width="{$image_data[1]}" height="{$image_data[2]}" alt="{$attr_title}" /></a>
HTML;

				}
				if ( !empty( $post_content ) || !empty( $post_thumbnail ) ) {
					$post_content_block = <<<HTML
<div class="post-content">
{$post_thumbnail}
{$post_content}
</div>
HTML;

				}
			}

			$article_list .= <<<HTML
<article id="post_{$id}" class="columns {$css_classes}">
	{$post_title_block}
	{$post_byline_block}
	{$post_content_block}
	{$post_footer_block}
</article>

HTML;

		}
		$block_post_list = <<<HTML
<div class="columns content post-list"><div class="row">
	{$article_list}
</div></div>

HTML;

	}

	# Next and Previous Navigation
	# - can only show up on date archives, author archives, and term archives
	$block_nav_next_prev = '';

	list( $up_one, $previous_item, $next_item ) = $renderer->get_nav_up_prev_next();
	if ( !empty($up_one) || !empty($previous_item) || !empty( $next_item ) ) {
		$previous_item = ( empty( $previous_item ) )? '&nbsp;' : $previous_item;
		$up_one = ( empty( $up_one ) )? '&nbsp;' : $up_one;
		$next_item = ( empty( $next_item ) )? '&nbsp;' : $next_item;
		$block_nav_next_prev = <<<HTML
<nav class="theme-navi next-previous columns">
	<div class="row">
		<div class="columns small-12 medium-4 large-5 medium-text-left">{$previous_item}</div>
		<div class="columns small-12 medium-4 large-2 medium-text-center">{$up_one}</div>
		<div class="columns small-12 medium-4 large-5 medium-text-right">{$next_item}</div>
	</div>
</nav>

HTML;

	}

	# Paged Navigation
	$block_paged_navigation = '';
	$pager = $renderer->get_nav_pager('Pages: ', 'Previous', 'Next');
	if ( !empty( $pager ) ) {
		$block_paged_navigation = <<<HTML
<div class="columns theme-navi next-prev-numbered">
	{$pager}
</div>
HTML;
	}


	###
	### Side Bars - done before this function is called
	###

	###
	### Bread Crumbs
	###
	$block_crumbs = '';
	$wanted_bars = array('main');
	$crumb_bars = $renderer->get_nav_breadcrumbs( $wanted_bars );
	if ( is_array($crumb_bars) && 0 < count($crumb_bars) ) {
		if ( array_key_exists('main', $crumb_bars) ) {
			$block_crumbs = '<div class="columns">' . $crumb_bars['main'] . '</div>';
		}
	}

	echo <<<HTML
<div id="shell-content" class="row-expanded {$shell_content_extra_css} shell-content-multi">
	<div class="row shell-content-inner">
		{$block_sb_side_one}
		<div class="columns small-12 {$extra_main_wrapper_css}">
			<div class="row article-wrap">
				{$block_crumbs}
				{$block_title}
				{$block_intro}
				{$block_post_list}
				{$block_paged_navigation}
				{$block_nav_next_prev}
			</div>
		</div>
		{$block_sb_side_two}
	</div>
</div>

HTML;

}

get_footer();
