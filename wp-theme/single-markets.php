<?php
use Com\Hunchfree\Wp\Themes\Hffoundation as Hffoundation;

/**
 * Market Template (single-market.php)
 *
 **/
 
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

	if ( is_404() ) {
		theme_draw_four_zero_four();
	} else {
		theme_draw_market( $renderer );
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

get_footer();

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

function theme_draw_market(
	Hffoundation\Theme_Front_End &$renderer
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
			$post_type = get_post_type( $post );
			$post_title = get_the_title();

	            ###
	            ### Header Block
	            ###
	            if( have_rows('header_block') ){

	                while( have_rows('header_block') ){
	                    the_row();

	                    $header_title = get_sub_field('header_title');
	                    $header_body_text = get_sub_field('header_body_text');
	                    $background_image = get_sub_field('background_image');

	                    if( !empty($background_image) ) {

	                        $header_img_url = $background_image['url'];
	                        $header_img_alt = $background_image['alt'];

	                    }

	                }

	            }

	            $header_block = <<<HTML
				
				<div class="grid-container">
	      
	                  	<div class="grid-x grid-margin-x header careers-bg align-middle align-center" style="background: url(' {$header_img_url} ');background-size: cover;">
	                  
	                    	<div class="large-8 medium-10 small-12 cell content white-text text-center">
	            
	                      		<h1>{$header_title}</h1>
	            
	                      		<p>{$header_body_text}</p>
	            
	                    	</div>
	                  
	                  	</div>
	                
	            	</div>

HTML;

			###
			### Content Block
			###
			if( have_rows('content_block') ){
				$content_items = '';

				while( have_rows('content_block') ){
					the_row();

					if( have_rows('content') ){
						$content_items = '';

						while( have_rows('content') ){
						the_row();

							$content_title = get_sub_field('content_title');
							$content_body_text = get_sub_field('content_body_text');

							$content_image = get_sub_field('content_image');
							if( !empty($content_image) ) {

								$content_img_url = $content_image['url'];
								$content_img_alt = $content_image['alt'];

							}

							if( get_row_index() % 2 == 0 ){

							$content_items .= <<<HTML

							<div class="grid-x grid-margin-x align-center">
							
								<div class="cell small-12 medium-10 large-6 top-border">

							    		<h2>{$content_title}</h2>

							    		<p>{$content_body_text}</p>

							  	</div>

							 	<div class="cell small-12 medium-10 large-6 text-center">

							    		<img src="{$content_img_url}" alt="{$content_img_alt}"/>

							  	</div>

							</div>

HTML;
							
							} else {

							$content_items .= <<<HTML

							<div class="grid-x grid-margin-x align-center top-pad">

          						<div class="cell small-12 medium-10 large-6 small-order-2 large-order-1">

            							<img src="{$content_img_url}" alt="{$content_img_alt}"/>

          						</div>

          						<div class="cell small-12 medium-10 large-6 top-border small-order-1 large-order-2">

            							<h2>{$content_title}</h2>

            							<p>{$content_body_text}</p>

          						</div>

        						</div>

HTML;
							}


						}

					}

				}

			}

			$content_block = <<<HTML

			<section class="white-bg">

      			<div class="grid-container">

      				{$content_items}

      			</div>

      		</section>

HTML;


			###
			### Sub-Markets
			###
			if( have_rows('sub_content_section') ){

				while( have_rows('sub_content_section') ){
					the_row();

					if( have_rows('sub_content_items') ){
					$content_cards = '';

						while( have_rows('sub_content_items') ){
						the_row();

							$sub_content_title = get_sub_field('sub_content_title');
							$sub_content_copy = get_sub_field('sub_content_copy');

							$sub_content_imagery = get_sub_field('sub_content_imagery');

							$content_cards .= <<<HTML

							<div class="cell text-center">
							    
							    <!--<a>-->
							    
							    <div class="card no-hover" style="background: url('{$sub_content_imagery}'); background-size: cover;">
							    
							      <div class="card-content">
							    
							        <h2 class="card-title">{$sub_content_title}</h2>

							        <p>{$sub_content_copy}</p>
							    							    
							      </div>
							    
							    </div>
							  
							  	<!--</a>-->

							</div>

HTML;


						}

					}

				}

				$sub_content_section = <<<HTML

				<div class="grid-container box-pad">
	      
	      			<div class="grid-x grid-margin-x small-up-1 medium-up-3 large-up-3 align-center">

	      				{$content_cards}

	      			</div>

	      		</div>

HTML;

			} else {


		            $sub_content_section = <<<HTML
		            
		            <section class="">

		              <div class="grid-container">
		        
		                <div class="grid-x grid-margin-x align-center pipes-bg text-center align-middle foot-callout" style="background: url(' https://piping.hunchfree.com/wp-content/uploads/2018/01/pipes.jpg '); background-size: cover;">
		        
		                  <div class="cell large-8 medium-8 small-10 white-text content">
		        
		                    <h2>Let's Talk!</h2>
		        
		                    <p>Pellentesque in ipsum id orci porta dapibus. Mauris blandit aliquet elit, eget tincidunt nibh pulvinar a. Proin eget tortor risus. Curabitur arcu erat, accumsan id imperdiet et, porttitor at sem.</p>

		                    <a href="/contact/" class="button white">Contact Us</a>
		        
		                  </div>
		        
		                </div>
		            
		              </div>
		        
            </section>
                    
HTML;

			}


		
		
		/**
		 * The following does all of the actual drawing
		 */
		echo <<<HTML
		  {$header_block}
		  {$content_block}
		  {$sub_content_section}

HTML;
		}
	}
}
