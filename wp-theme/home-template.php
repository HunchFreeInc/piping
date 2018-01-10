<?php
use Com\Hunchfree\Wp\Themes\Hffoundation as Hffoundation;

/*
	Template Name: Home Page Template
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
			  
			  <div class="grid-x grid-margin-x hero align-right text-right align-bottom" style="background: url(' {$header_img_url} ');">
			  
			    <div class="large-6 cell content white-text top-border-white">

			      <h1>{$header_title}</h1>

			      <p>{$header_body_text}</p>

			      <a href="{$header_button_link}" class="button red">{$header_button_text}</a>

			    </div>
			  
			  </div>
			
			</div>

HTML;


			###
			### Featured Content Block
			###
			if( have_rows('featured_content_block') ){

				while( have_rows('featured_content_block') ){
					the_row();

					if( have_rows('content_cards') ){
					$content_cards = '';

						while( have_rows('content_cards') ){
						the_row();

							$card_title = get_sub_field('card_title');
							$card_body_text = get_sub_field('card_body_text');
							$card_button_link = get_sub_field('card_button_link');

							$card_image = get_sub_field('background_image');
							if( !empty($card_image) ) {

								$card_img_url = $card_image['url'];
								$card_img_alt = $card_image['alt'];

							}

							$content_cards .= <<<HTML

							<div class="cell">
							    
							  <a href="{$card_button_link}">
							    
							    <div class="card" style="background: url('{$card_img_url}'); background-size: cover;">
							    
							      <div class="card-content">
							    
							        <h2 class="card-title">{$card_title}</h2>

							        <p>{$card_body_text}</p>
							    
							        <p class="card-description button">Learn more</p>
							    
							      </div>
							    
							    </div>
							  
							  </a>

							</div>

HTML;


						}

					}

				}

			}

			$featured_content_block = <<<HTML

			<div class="grid-container box-pad">
      
      			<div class="grid-x grid-margin-x small-up-1 medium-up-3 large-up-3">

      				{$content_cards}

      			</div>

      		</div>

HTML;


			###
			### Content Block
			###
			if( have_rows('content_block') ){

				while( have_rows('content_block') ){
					the_row();

					if( have_rows('content') ){
					$content = '';

						while( have_rows('content') ){
						the_row();

							$content_title = get_sub_field('content_title');
							$content_body_text = get_sub_field('content_body_text');
							$content_button_text = get_sub_field('content_button_text');
							$content_button_link = get_sub_field('content_button_link');

							$content_image = get_sub_field('content_image');
							if( !empty($content_image) ) {

								$content_img_url = $content_image['url'];
								$content_img_alt = $content_image['alt'];

							}

							if( get_row_index() % 2 == 0 ){

							$content .= <<<HTML

							<div class="grid-x grid-margin-x align-middle top-pad">

          						<div class="cell small-12 medium-8 large-6 small-order-2 medium-order-1">

            						<img src="{$content_img_url}"/>

          						</div>

          						<div class="cell small-12 medium-4 large-6 top-border small-order-1 medium-order-2">

            						<h2>{$content_title}</h2>

            						<p>{$content_body_text}</p>

            						<a href="{$content_button_link}" class="button red">{$content_button_text}</a>

          						</div>

        					</div>

HTML;
							
							} else {

							$content .= <<<HTML

							<div class="grid-x grid-margin-x align-middle">
							
								<div class="cell small-12 medium-4 large-6 top-border">

							    	<h2>{$content_title}</h2>

							    	<p>{$content_body_text}</p>

							    	<a href="{$content_button_link}" class="button red">{$content_button_text}</a>

							  	</div>

							 	<div class="cell small-12 medium-8 large-6">

							    	<img src="{$content_img_url}"/>

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

      				{$content}

      			</div>

      		</section>

HTML;


			/**
			 * The following does all of the actual drawing
			 */
			echo <<<HTML
				{$header_block}
				{$featured_content_block}
				{$content_block}

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
