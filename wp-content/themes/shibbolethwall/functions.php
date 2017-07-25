<?php
add_action( 'after_switch_theme', 'flush_rewrite_rules' );

//Lets add Open Graph Meta Info
function insert_fb_in_head() {
	global $post;
	if ( !is_singular()) //if it is not a post or a page
		return;
        echo '<meta property="fb:admins" content="https://www.facebook.com/shibbolethwall"/>';
        echo '<meta property="og:title" content="' . get_the_title() . '"/>';
        echo '<meta property="og:type" content="article"/>';
        echo '<meta property="og:url" content="' . get_permalink() . '"/>';
        echo '<meta property="og:site_name" content="UP Vanguard Shibboleth Wall"/>';
	if(!has_post_thumbnail( $post->ID )) { //the post does not have featured image, use a default image
		$default_image=""; //replace this with a default image on your server or an image in your media library
		echo '<meta property="og:image" content="' . $default_image . '"/>';
	}else{
		$thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium' );
		echo '<meta property="og:image" content="' . esc_attr( $thumbnail_src[0] ) . '"/>';
	}
	echo "
";
}
add_action( 'wp_head', 'insert_fb_in_head', 5 );

//Lets add Twitter Card Meta Info
function insert_twitter_in_head() {
	global $post;
	if ( !is_singular()) //if it is not a post or a page
		return;
        echo '<meta name="twitter:card" content="summary" />';
        echo '<meta name="twitter:site" content="@ShibbolethWall" />';
        echo '<meta name="twitter:title" content="' . get_the_title() . '" />';
        echo '<meta name="twitter:description" content="' . wp_filter_nohtml_kses(get_the_excerpt()) . '" />';
		
	if(!has_post_thumbnail( $post->ID )) { //the post does not have featured image, use a default image
		$default_image=""; //replace this with a default image on your server or an image in your media library
		echo '<meta name="twitter:image" content="' . $default_image . '" />';		
	}else{
		$thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium' );
		echo '<meta name="twitter:image" content="' . esc_attr( $thumbnail_src[0] ) . '" />';		
	}
	echo "
";
}
add_action( 'wp_head', 'insert_twitter_in_head', 5 );

/*	@desc attach custom admin login CSS file	*/
function custom_login_css() {
  echo '<link rel="stylesheet" type="text/css" href="'.get_template_directory_uri().'/login.css" />';
}
add_action('login_head', 'custom_login_css');

/*	@desc update logo URL to point towards Homepage	*/
function custom_login_header_url($url) {
  return get_option('home');
}
add_filter( 'login_headerurl', 'custom_login_header_url' );

function custom_login_header_title($title) {
  $blog_title = get_bloginfo('name');
  return $blog_title;
}

add_filter( 'login_headertitle', 'custom_login_header_title' );
/*	@desc update admin icon to client icon	*/
function custom_admin_icon_css() {
  echo '<link rel="shortcut icon" href="'.get_template_directory_uri().'/images/logo.ico" />';
}
add_action('admin_head', 'custom_admin_icon_css');

function remove_footer_admin () {
    echo '<span id="footer-thankyou">Template implemented and developed by <a href="http://www.jamediasolutions.com/" target="_blank" title="JA Media Solutions">JA Media Solutions</a>.</span>';
}
add_filter('admin_footer_text', 'remove_footer_admin');

// Disable Admin Bar for all users
add_filter('show_admin_bar', '__return_false');

function shibbolethwall_remove_version() {return '';}
add_filter('the_generator', 'shibbolethwall_remove_version');

//Making jQuery Google API
function modify_jquery() {
	if (!is_admin()) {
		wp_deregister_script('jquery');
		wp_register_script('jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js', false, '1.11.3');
		wp_enqueue_script('jquery');
	}
}
add_action('init', 'modify_jquery');

// custom menu support
add_theme_support( 'menus' );
if (function_exists( 'register_nav_menus')) {register_nav_menus(array('primary_navigation' => 'Primary Navigation', 'secondary_navigation' => 'Secondary Navigation', 'utility_navigation' => 'Utility Navigation', 'footer_navigation' => 'Copyright Footer Navigation', 'footer_navigation_1' => 'Footer Navigation One', 'footer_navigation_2' => 'Footer Navigation Two', 'footer_navigation_3' => 'Footer Navigation Three', 'footer_navigation_4' => 'Footer Navigation Four'));}

?>