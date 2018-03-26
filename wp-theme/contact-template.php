<?php
use Com\Hunchfree\Wp\Themes\Hffoundation as Hffoundation;

/*
	Template Name: Contact Page Template
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
                    $background_image = get_sub_field('background_image');

                    if( !empty($background_image) ) {

                        $header_img_url = $background_image['url'];
                        $header_img_alt = $background_image['alt'];

                    }

                }

            }

            $header_block = <<<HTML
			
			 <div class="grid-container">
      
                  <div class="grid-x grid-margin-x header careers-bg align-center text-center align-middle" style="background: url(' {$header_img_url} '); background-size: cover;">
                  
                    <div class="large-8 medium-10 small-12 cell content white-text">
            
                      <h1>{$header_title}</h1>
            
                      <p>{$header_body_text}</p>
            
                    </div>
                  
                  </div>
                
            </div>

HTML;


            ###
            ### Content Block
            ###
            if( have_rows('contact_form') ){

                while( have_rows('contact_form') ){
                    the_row();

                    $form_shortcode = get_sub_field('form_shortcode');
                    $form = do_shortcode("$form_shortcode");

                }

            }

            $contact_form_block = <<<HTML

			<section class="white-bg">

                <div class="grid-container">
                    
                    <div class="grid-x grid-margin-x grid-margin-y align-center">

                        <div class="cell small-12 medium-8 large-7">

                            {$form}

      			       </div>

                    </div>

                </div>

      		</section>

HTML;


            ###
            ### Locations Block
            ###
            if( have_rows('locations_block') ){

                while( have_rows('locations_block') ){
                    the_row();

                    $locations_title = get_sub_field('locations_title');
                    $locations_text = get_sub_field('locations_text');
                    $locations_map_embed = get_sub_field('locations_map_embed');

                }

            }

            $locations_block = <<<HTML

    <section class="normal-pad">

        <div class="grid-container">

            <div class="grid-x grid-margin-x">
      
                <div class="cell large-4 medium-4 small-12 top-border">

                    <h2>{$locations_title}</h2>

                    <p>{$locations_text}</p>

                </div>

                <div class="cell large-8 medium-8 small-12">

                    {$locations_map_embed}

                </div>

            </div>

        </div>

    </section>

    <script>
    document.addEventListener( 'wpcf7mailsent', function( event ) {
        location = '/thank-you/';
    }, false );
    </script>

HTML;


            /**
             * The following does all of the actual drawing
             */
            echo <<<HTML
				{$header_block}
				{$contact_form_block}
                {$locations_block}

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
