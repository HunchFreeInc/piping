<?php
use Com\Hunchfree\Wp\Themes\Hffoundation as Hffoundation;

/*
	Template Name: Interior Page Template
*/

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
			### Header Block
			###
			if( have_rows('header_block') ){

				while( have_rows('header_block') ){
					the_row();

					$header_title = get_sub_field('header_title');
					$header_body_text = get_sub_field('header_body_text');
					$header_button_text = get_sub_field('header_button_text');
					$header_button_link = get_sub_field('header_button_link');
					$background_image = get_sub_field('background_image');
					if( !empty($background_image) ) {

						$header_img_url = $background_image['url'];
						$header_img_alt = $background_image['alt'];

					}

				}

			}

			$header_block = <<<HTML

			<div class="grid-container">

				<div class="grid-x grid-margin-x header careers-bg align-bottom" style="background: url(' {$header_img_url} ');    background-size: cover;">
				                  
	                <div class="large-6 cell content white-text top-border-white">
	        
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

				while( have_rows('content_block') ){
					the_row();

					$content = get_sub_field('content');

				}

			}

			$content_block = <<<HTML

			<section class="white-bg">

      			<div class="grid-container">
      				
					<div class="grid-x grid-margin-x align-middle">

						<div class="cell large-12">

							{$content}

						</div>

					</div>

				</div>

			</section>

HTML;

			$hotlinks = <<<HTML

			<div class="grid-container box-pad">

			                <div class="grid-x grid-margin-x small-up-1 medium-up-3 large-up-3 align-center">
			                
			                                            
			                            <div class="cell">

			                              <a href="https://piping.hunchfree.com">
			                    
			                                <div class="card" style="background: url('https://piping.hunchfree.com/wp-content/uploads/2018/01/industrial.jpg'); background-size: cover;">
			                    
			                                  <div class="card-content">
			                    
			                                    <h2 class="card-title">Hotlink</h2>
			                    
			                                    <p class="card-description button">Learn More</p>
			                    
			                                  </div>
			                    
			                                </div>
			                    
			                              </a>
			                    
			                            </div>
			                                                
			                            <div class="cell">

			                              <a href="https://piping.hunchfree.com">
			                    
			                                <div class="card" style="background: url('https://piping.hunchfree.com/wp-content/uploads/2018/01/industrial.jpg'); background-size: cover;">
			                    
			                                  <div class="card-content">
			                    
			                                    <h2 class="card-title">Hotlink</h2>
			                    
			                                    <p class="card-description button">Learn More</p>
			                    
			                                  </div>
			                    
			                                </div>
			                    
			                              </a>
			                    
			                            </div>
			                                                
			                            <div class="cell">

			                              <a href="https://piping.hunchfree.com">
			                    
			                                <div class="card" style="background: url('https://piping.hunchfree.com/wp-content/uploads/2018/01/industrial.jpg'); background-size: cover;">
			                    
			                                  <div class="card-content">
			                    
			                                    <h2 class="card-title">Hotlink</h2>
			                    
			                                    <p class="card-description button">Learn More</p>
			                    
			                                  </div>
			                    
			                                </div>
			                    
			                              </a>
			                    
			                            </div>
			                    
			      
			                </div>
			      
			              </div>
HTML;


			/**
			 * The following does all of the actual drawing
			 */
			echo <<<HTML
				{$header_block}
				{$content_block}
				{$hotlinks}

HTML;


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

get_footer();
