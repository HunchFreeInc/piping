<?php
use Com\Hunchfree\Wp\Themes\Hffoundation as Hffoundation;

/*
	Template Name: Thank You
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

			<div class="grid-container" style="padding-bottom: 3rem;">
			  
			  <div class="grid-x grid-margin-x hero align-center text-center align-middle" style="background: url('/wp-content/uploads/2018/03/240_f_64531819_2uwloiom6jyz6dgqktxjcg7pkofn91h1.jpg '); background-size: cover; background-position: center center;">
			  
			    <div class="large-8 medium-10 small-12 cell content white-text">

			      <h1>Thank You!</h1>

			      <p>We've received your contact form, we'll be in touch with you soon!</p>

			      <a href="/" class="button red">Return Home</a>

			    </div>
			  
			  </div>
			
			</div>

HTML;





			/**
			 * The following does all of the actual drawing
			 */
			echo <<<HTML
				{$header_block}
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
