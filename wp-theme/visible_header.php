<?php
use Com\Hunchfree\Wp\Themes\Hffoundation as Hffoundation;

defined('\\ABSPATH') or die('Permission denied');

$site_title = get_bloginfo('name');
$site_url = get_bloginfo('url');
$ss_url = trailingslashit( get_stylesheet_directory_uri() );

/**
 * Menu Building
 *
 * Most sites should use a built in wordpress menu for the primary navigation.
 *
 * This lets users easily adjust the menu as needed.
 *
 * We support two different methods of building the menu:
 * a) Use our own (non-foundation) menu
 * b) Use foundation menus
 *
 * To use the non-foundation drop down menus, you need to use a menu_class of pvtdd
 * so that the custom navigation walker can detect it.
 *
 * To use foundation drop down menus, stick the foundation classes (e.g. vertical menu)
 * into the menu_class argument.
 *
 * Both methods allow you to add a css class of has_button or has_hollow_button to any
 * link in a wordpress menu and have the walker draw the button or hollow_button css
 * class for that menu item.
 *
 * @todo add theme support for Customize Selective Refresh Widgets (v 4.5+)
 */

# load the custom menu nav walker that understands has_button and has_hollow_button
locate_template('php/walkers/menus/main-nav-menu-walker.php', true);


# Desktop Main Menu
$menu_args = array(
	'container' => '',						# the tag to wrap everything in, if wanted
	'menu_class' => 'pvtdd',				# any special menu css classes (e.g foundation declarations)
	'items_wrap' => '%3$s',
	'depth' => 3,								# maximum depth of the menu
	'theme_location' => 'main-menu',	# declared theme_location from register_nav_menus in theme_configuration.json
	'echo' => 0,								# return the menu instead of echoing it
	'fallback_cb' => false					# if not found, return nothing instead of the first menu found
);

# Desktop Utility Menu
$utility_args = array(
	'container' => '',
	'menu_class' => 'pvtdd',
	'items_wrap' => '%3$s',
	'depth' => 3,
	'theme_location' => 'utility-menu',
	'echo' => 0,
	'fallback_cb' => false
);

# Mobile Menu
$mobile_args = array(
	'container' => '',
	'menu_class' => 'pvtdd',
	'items_wrap' => '%3$s',
	'depth' => 1,
	'theme_location' => 'mobile-menu',
	'echo' => 0,
	'fallback_cb' => false
);


# User our custom menu nav walker instead of the normal one (if properly loaded above)
if ( class_exists( '\\Com\\Hunchfree\\Wp\\Themes\\Hffoundation\\Main_Nav_Menu_Walker') ) {
	$menu_args['walker'] = new Hffoundation\Main_Nav_Menu_Walker();
	$utility_args['walker'] = new Hffoundation\Main_Nav_Menu_Walker();
	$mobile_args['walker'] = new Hffoundation\Main_Nav_Menu_Walker();
}

# Allow filtering of arguments if wanted via functions.php declarations in child theme
$menu_args = apply_filters( 'pwf_adjust_theme_main_nav_arguments', $menu_args );
$utility_args = apply_filters( 'pwf_adjust_theme_main_nav_arguments', $utility_args );
$mobile_args = apply_filters( 'pwf_adjust_theme_main_nav_arguments', $mobile_args );

# Generate the menu and store it in a variable to draw it later
$top_nav = wp_nav_menu( $menu_args );
$utility_nav = wp_nav_menu( $utility_args );
$mobile_nav = wp_nav_menu( $mobile_args );

# If nothing was returned, create a fake menu for display until user creates
# a menu and associates it with our menu location specified above
if ( empty( $top_nav ) ) {
	/* Foundation based vertical menu example
	$top_nav = <<<HTML
	 <li>
		  <a href="#">Set</a>
		  <ul class="menu vertical">
				<li>
					 <a href="#">Sub Item One</a>
					 <ul class="menu vertical">
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

	*/

	# Non foundation based example
	$top_nav = <<<HTML
	<li class="menu-item menu-item-type-post_type menu-item-object-page current-menu-ancestor current-menu-parent current_page_parent current_page_ancestor menu-item-has-children opens-inner menu-item-135">
		<a href="#">Set</a>
		<ul class="sub-menu">
			<li id="menu-item-29" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-29"><a href="#">Sub One</a></li>
			<li id="menu-item-158" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-158"><a href="#">Sub Two</a></li>
			<li id="menu-item-38" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-38"><a href="#">Sub Three</a></li>
			<li id="menu-item-35" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-35"><a href="#">Sub Four</a></li>
			<li id="menu-item-133" class="menu-item menu-item-type-post_type menu-item-object-page current-menu-item page_item page-item-9 current_page_item current_page_parent menu-item-133"><a href="#">Sub Five</a></li>
		</ul>
	</li>
	<li id="menu-item-37" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-37"><a href="#">Up</a></li>
	<li id="menu-item-39" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-39"><a href="#">Main</a></li>
	<li id="menu-item-31" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-31"><a href="#">Nav</a></li>
HTML;

}

$extra_content_block = '';

/** @var Hffoundation\Theme_Controller_Front_End $Renderer */
$Renderer = Hffoundation\Theme_Front_End::get_instance();

$header_display_class = '';
$header_display_method = $Renderer->get_best_mod('header_display_method', 'normal');
if ( !empty($header_display_method) && 'normal' !== $header_display_method ) {
	switch ( "{$header_display_method}" ) {
		case 'mobile':
			$header_display_class = 'theme-sticky-mobile-nav';
			break;
		case 'all':
			$header_display_class = 'theme-sticky-nav';
			break;
		default:
			break;
	}
}

### Handle placeable sidebars above, in, and below the shell header

#$header_image = get_header_image();
#if ( !empty($header_image) ) {
#	$image_data = get_custom_header();
#	if ( is_object( $image_data ) ) {
#		$width_attr = ( isset( $image_data->width ) && !empty( $image_data->width ) )? ' width="' . $image_data->width . '"' : '';
#		$height_attr = ( isset( $image_data->height ) && !empty( $image_data->height ) )? ' height="' . $image_data->height . '"' : '';
#	} else {
#		$width_attr = '';
#		$height_attr = '';
#	}
#	$header_image = <<<HTML
#<img src="{$header_image}" {$width_attr} {$height_attr} id="theme_header_image" class="show-for-medium" />
#<span class="hide-for-medium header-text-data">{$site_title}</span>
#HTML;
#
#} else {
#	$header_image = $site_title;
#}

/**
 * Visible Site Header with non-foundation based primary navigation
 *
 * To set up the non-foundation based visible header, you need to set up a few different divs
 *
 * mobile-nav-pad
 * This should contain only a non breaking space as it is used to properly position things
 *
 * mobile-nav-block
 * Displayed on small and medium sized screens (e.g. mobile)
 * Hidden on large screens (e.g. desktop)
 * It should contain a button or link with the class mobile-toggle-button, which will be used
 * to show or hide what is contained in the hideable-nav-block section.
 *
 * mobile-toggle-button
 * This must be a button or a element. It will be used to show or hide the hideable-nav-block
 *
 * hideable-nav-block
 * Hidden on small and medium sized screens until user clicks the mobile-toggle-button
 * Shown on large screens
 * This should contain the main site navigation and anything else needed.
 * Don't forget to use the various show-on and hide-on classes to ensure elements that should
 * not be visible on small or medium screens are not shown when the mobile-toggle-button is clicked
 *
 * @note to make the header sticky, add the class theme-sticky-nav into the site-visible-header div
 * 		or add theme-sticky-mobile-nav if you only want the mobile nav to be sticky
 */
$theme_dir = trailingslashit( get_stylesheet_directory_uri() );


/**
 * SETTING UP THE MOBILE LOGO
 *
 * The following section handles letting users use the theme customizer to set up the mobile logo.
 *
 * To make sure they put the right sized image into place, be sure to update configs/theme-default-mods.json
 * and set the mobile_logo_height and mobile_logo_width values up to match what you used in your header if you
 * used a mobile logo in your design.
 *
 * To use what you used in your design as the default, edit the default mobile logo block
 */
# default mobile logo
$theme_base_url = trailingslashit( get_stylesheet_directory_uri() );

$mobile_logo = <<<HTML
<a href="{$site_url}" class="mobile-logo-link" rel="home" itemprop="url">
	<img src="{$theme_base_url}/assets/images/mobile-logo.png" width="118" height="36" alt="{$site_title}" class="custom-logo-mobile" />
</a>
HTML;

# to display just text, remove the above block and uncomment the below line:
# $mobile_logo = '<a href="' . $site_url . '">' . $site_title . '</a>';


$mobile_logo_id = $Renderer->get_best_mod('mobile_header_custom_logo', '');
if ( !empty($mobile_logo_id) && false !== $mobile_logo_id ) {
	$mobile_logo = sprintf( '<a href="%1$s" class="mobile-logo-link" rel="home" itemprop="url">%2$s</a>',
		esc_url( home_url( '/' ) ),
		wp_get_attachment_image( $mobile_logo_id, 'full', false, array(
			'class'    => 'custom-logo-mobile',
			'alt' => $site_title
		) )
	);
}

/**
 * SETTING UP THE LOGO
 *
 * To make this work nicely with the theme customizer, be sure to edit theme-configuration.json and set:
 * - use_theme_logo : true
 * - theme_logo (set the height and width to those of the logo size you used)
 *
 * Make sure to set up the default logo below
 */

$title_extra_css = 'has-logo';

# default logo
$logo = <<<HTML
<a href="{$site_url}" class="custom-logo-link" rel="home" itemprop="url">
	<img src="{$theme_base_url}assets/images/logo.png" width="164" height="50" alt="{$site_title}" class="custom-logo" itemprop="logo" />
</a>
HTML;

# to use no logo, delete the above and uncomment the following line:
# $logo = '';

if ( function_exists('the_custom_logo') && current_theme_supports('custom-logo') ) {
	# user has set a different logo via the customizer, use that instead
	$alternate_logo = get_custom_logo();
	if ( !empty($alternate_logo) && false !== $alternate_logo ) {
		$logo = $alternate_logo;
		$title_extra_css = 'has-logo';
	}
}
if ( empty($logo) || false === $logo ) {
	$title_extra_css = '';
}

/**
 * SETTING UP THE SITE DESCRIPTION
 *
 * If you don't use the site description in the shell header anywhere, replace the below code with
 * the following line:
 * $site_description = '';
 */
$site_description = get_bloginfo('description');
if ( !empty( $site_description ) ) {
	$site_description = <<<HTML
<div class="columns small-12 site-description"><p>{$site_description}</p></div>
HTML;

}

/**
 * SETTING UP THE SITE TITLE TEXT
 *
 * If you display the site title (whether next to a logo or not), the below should be fine.
 *
 * Otherwise, you should comment out the block below that overrides the site title text
 */
$site_title_text = '';
if ( !empty( $site_title ) ) {
	$site_title_text = <<<HTML
<span class="header-text-data {$title_extra_css}"><a href="{$site_url}">{$site_title}</a></span>
HTML;

}

/**
 * GENERATING THE ACTUAL OUTPUT
 *
 * The header you design for your foundation template probably looks different than the below.
 *
 * As long as you used the docs/header-baked.txt and instructions in docs/foundation_to_wordpress.html to
 * create your header, you should be able to replace the below block with your own visible header,
 * substituting in the proper variables where noted.
 *
 * If you need a different header on one page than you do on other pages, you can use the various is_*
 * classes that wordpress offers (e.g. is_front_page(), is_home(), etc.) in order to draw things one way
 * for a particular template and another way for other templates.
 *
 * The easiest way to set up the below is to replace the contents of the following divs
 * with the content you used in your designs:
 * - #mobile-nav-block
 * - #hideable-nav-block
 *
 * and then substitute in the following variables where needed:
 * - {$header_display_class}
 * - {$mobile_logo}
 * - {$logo}
 * - {$site_title_text}
 * - {$top_nav}
 * - {$site_description}
 */
echo <<<HTML

<div class="sticky desktop">

    <div class="grid-container-expanded utility">
        
        <div class="grid-x text-right">
        
            <div class="large-12 cell">
        
                <ul class="menu align-right">

                    {$utility_nav}
            
                </ul>
        
            </div>
        
        </div>
      
    </div>

    <section class="nav">

        <div class="grid-container">

            <div class="grid-x align-middle">

                <div class="large-3 cell">

                    <div class="logo">

                        {$logo}

                    </div>

                </div>

                <div class="large-9 cell text-right">

                    <ul class="pvtdd menu">

                        {$top_nav}

                    </ul>

                </div>

            </div>

        </div>

    </section>

</div>

<div class="sticky mobile">
  
    <section class="nav">
    
        <div class="grid-container">
    
            <div class="grid-x grid-margin-x align-middle">
            
                <div class="small-8 cell">
            
                    <div class="logo">
                
                        {$logo}
              
                    </div>
            
                </div>
            
                <div class="columns small-4 text-right">  
            
                    <button class="menu-trigger" id="trigger-overlay" type="button"><i class="fa fa-bars fa-2x" aria-hidden="true"></i></button>
          
                </div>
      
            </div>

        </div>
    
    </section>

</div>

<div class="overlay overlay-hugeinc">

	<button type="button" class="overlay-close">X</button>
	
	<nav>
	
		<ul>
			
			{$mobile_nav}
	
		</ul>
	
	</nav>
	
</div>

HTML;

