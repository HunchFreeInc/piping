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
			  
			  <div class="grid-x grid-margin-x hero align-center text-center align-middle" style="background: url(' {$header_img_url} '); background-size: cover; background-position: center center;">
			  
			    <div class="large-8 medium-10 small-12 cell content white-text">

			      <h1>{$header_title}</h1>

			      <p>{$header_body_text}</p>

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

							<div class="cell text-center">
							    
							  <!--<a href="{$card_button_link}">-->
							    
							    <div class="card no-hover" style="background: url('{$card_img_url}'); background-size: cover;">
							    
							      <div class="card-content">
							    
							        <h2 class="card-title">{$card_title}</h2>

							        <p>{$card_body_text}</p>
							    
							        <!-- <p class="card-description button">Learn more</p> -->
							    
							      </div>
							    
							    </div>
							  
							  <!--</a>-->

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
			### Mission Statement Block
			###
			if( have_rows('mission_statement_block') ){

				while( have_rows('mission_statement_block') ){
					the_row();


					$mission_statement_title = get_sub_field('mission_statement_title');
					$mission_statement_copy = get_sub_field('mission_statement_copy');

				}

			}

			$mission_statement_block = <<<HTML

			<section class="dk-bg normal-pad">

      			<div class="grid-container">

      				<div class="grid-x grid-margin-x align-center">

      					<div class="cell large-8 medium-10 small-12 text-center">

      						<h2 class="mission-statement">{$mission_statement_title}</h2>

      						<p class="mission-statement">{$mission_statement_copy}</p>

      					</div>

      				</div>

      			</div>

      		</section>

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

							<div class="grid-x grid-margin-x grid-margin-y align-center top-pad">

          						<div class="cell small-12 medium-10 large-6 medium-order-2 large-order-1">

            						<img src="{$content_img_url}" alt="{$content_img_alt}"/>

          						</div>

          						<div class="cell small-12 medium-10 large-6 top-border small-order-1 medium-order-2">

            						<h2>{$content_title}</h2>

            						<p>{$content_body_text}</p>

            						<a href="{$content_button_link}" class="button red">{$content_button_text}</a>

          						</div>

        					</div>

HTML;
							
							} else {

							$content .= <<<HTML

							<div class="grid-x grid-margin-x grid-margin-y align-middle align-center">
							
								<div class="cell small-12 medium-10 large-6 top-border">

							    	<h2>{$content_title}</h2>

							    	<p>{$content_body_text}</p>

							    	<a href="{$content_button_link}" class="button red">{$content_button_text}</a>

							  	</div>

							 	<div class="cell small-12 medium-10 large-6">

							    	<img src="{$content_img_url}" alt="{$content_img_alt}"/>

							  	</div>

							</div>

HTML;
							}


						}

					}

				}

			}

			$content_block = <<<HTML

			<section class="white-bg normal-pad">

      			<div class="grid-container">

      				{$content}

      			</div>

      		</section>

HTML;

			###
			###	Partners Block
			###
			if( have_rows('partners_block') ){

				while( have_rows('partners_block') ){
					the_row();

					if( have_rows('partners') ){
					$partners = '';

						while( have_rows('partners') ){
							the_row();

							$partner_name = get_sub_field('partner_name');
							$partner_website = get_sub_field('partner_website');
							$partner_logo = get_sub_field('partner_logo');
							if( !empty($partner_logo) ) {

								$partner_img_url = $partner_logo['url'];
								$partner_img_alt = $partner_logo['alt'];

							}

							$partners .= <<<HTML
							<div class="cell text-center">

								<a href="{$partner_website}" target="_blank">

									<img src="{$partner_img_url}" alt="{$partner_img_alt}" />

								</a>

							</div>

HTML;

						}

					}

				}

			}

			$partners_block = <<<HTML

			<div class="grid-container normal-pad">

				<div class="grid-x text-center">

					<div class="cell small-12">

						<h2>Proud Partners</h2>

					</div>

				</div>
      
      			<div class="grid-x grid-margin-x small-up-1 medium-up-3 large-up-3">

      				{$partners}

      			</div>

      		</div>

HTML;





			/**
			 * The following does all of the actual drawing
			 */
			echo <<<HTML
				{$header_block}
				{$featured_content_block}
				{$mission_statement_block}
				{$content_block}
				{$partners_block}

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
