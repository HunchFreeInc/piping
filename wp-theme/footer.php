<?php
use Com\Hunchfree\Wp\Themes\Hffoundation as Hffoundation;

defined('\\ABSPATH') or die('Permission denied - header.php');

$year = date('Y');

# Draw the main navigation menu in the footer
# Small readers without javascript will jump here when clicking the menu icon
# Hidden on larger screens that should support the normal nav menu
locate_template('walkers/menus/main-nav-menu-walker.php', true);

$menu_args = array(
    'container' => '',
    'menu_class' => 'vertical menu',
    'items_wrap' => '%3$s',
    'depth' => 2,
    'theme_location' => 'footer-menu',
    'echo' => 0,
    'fallback_cb' => false
);
if ( class_exists( '\\Com\\Hunchfree\\Wp\\Themes\\Hffoundation\\Main_Nav_Menu_Walker') ) {
    $menu_args['walker'] = new Hffoundation\Main_Nav_Menu_Walker();
}

$menu_args = apply_filters( 'pwf_adjust_theme_main_nav_arguments', $menu_args );

$top_nav = wp_nav_menu( $menu_args );
if ( empty( $top_nav ) ) {
    $top_nav = <<<HTML
	<li>
		<a href="#">Set</a>
		<ul class="vertical menu">
			<li>
				<a href="#">Sub Item One</a>
				<ul class="vertical menu">
					<li><a href="#">Sub Sub One</a></li>
					<li><a href="#">Sub Sub One</a></li>
					<li><a href="#">Sub Sub One</a></li>
				</ul>
			</li>
		</ul>
	</li>
	<li><a href="#">Up</a></li>
	<li><a href="#">Main</a></li>
	<li><a href="#">Nav</a></li>

HTML;

}

/** @var Hffoundation\Theme_Controller_Front_End $Renderer */
$Renderer = Hffoundation\Theme_Front_End::get_instance();

# Admin visible notices and exceptions should be drawn at the very bottom of the footer
$admin_notices = '';
if ( is_user_logged_in() && current_user_can('switch_themes') ) {
    $exceptions = $Renderer->get_exceptions();
    if ( 0 < count($exceptions) ) {
        $notice_list = '';
        foreach ( $exceptions as $e ) {
            $notice_list .= "\r\n" . '<div class="columns"><pre>' . print_r($e, true) . '</pre></div>';
        }
    } else {
        $notice_list = '<div class="columns">No Exceptions</div>';
    }
    $warnings = $Renderer->get_warnings();
    if ( 0 < count($warnings) ) {
        $warning_list = "\r\n" . '<div class="columns"><h6>Warnings:</h6><pre>' . print_r($warnings, true) . '</pre></div>';
    } else {
        $warning_list = '<div class="columns">No Warnings</div>';
    }
    $admin_notices = <<<HTML
<div class="row-expanded exceptions" style="background-color: #EFEFEF; padding: 1em 0;">
	<div class="row">
		<div class="columns">
			<p><b>Admin Notices:</b> Only logged in admins see this content. Are you an admin? Blink once for yes or once for no.</p>
		</div>
		{$notice_list}
		{$warning_list}
	</div>
</div>
HTML;

}

/**
 * Add your footer into the below footer section as wanted.
 *
 * It should be contained in one or more rows drawn after the row that contains the {$top_nav} variable.
 */

echo <<<HTML

<section class="footer-menu">

    <div class="grid-container">

        <div class="grid-x grid-margin-x grid-padding-y align-center align-top">

            <div class="large-4 medium-4 small-12 cell medium-text-left text-center">

                <img src="/wp-content/uploads/2018/01/footer-logo.png"/>
                <p class="small-p">Cras ultricies ligula sed magna dictum porta. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sollicitudin molestie malesuada. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>

            </div>

            <div class="large-4 medium-4 small-12 cell medium-text-left text-center top-border-white">

                <h2>Menu</h2>

                <div class="sep"></div>

                <div class="grid-x small-up-1 medium-up-2">
                
                <div class="cell">
                
                <ul>
                   {$top_nav}
                </ul>
                
                </div>


                </div>

            </div>

            <div class="large-4 medium-4 small-12 cell medium-text-left text-center top-border-white">

                <h2>Contact Information</h2>
                <p>9100 Canniff St<br />
                    Houston, TX 77017</p>
                <p><i class="fas fa-phone-square"></i> <a href="/">888-889-9683</a></p>

            </div>

        </div>

    </div>

</section>

<section class="footer">

    <div class="grid-container">

        <div class="grid-x grid-margin-x grid-margin-y align-middle">

            <div class="cell large-6 text-center medium-text-left">

                <p>© 2018 Piping and Equipment. All Rights Reserved.</p>

            </div>

            <div class="cell large-6 text-center medium-text-right">

                <a href="https://hunchfree.com" target="_blank"><img src="/wp-content/uploads/2018/01/hf-logo.png"/></a>

            </div>

        </div>

    </div>

</section>

{$admin_notices}

HTML;
