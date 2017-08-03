<?php
add_action( 'after_switch_theme', 'flush_rewrite_rules' );

add_action( 'after_setup_theme', 'woocommerce_support' );
function woocommerce_support() {
    add_theme_support( 'woocommerce' );
}

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

if ( function_exists('register_sidebar') )
register_sidebar(array('id'=>'default-sidebar','name'=>'Default Sidebar','before_widget' => '<span id="%1$s" class="widget %2$s">','after_widget' => '</span>','before_title' => '<h2 class="widgettitle">','after_title' => '</h2>',));
register_sidebar(array('id'=>'footer-top','name'=>'Top Footer','before_widget' => '','after_widget' => '','before_title' => '<h2 class="widgettittle">','after_title' => '</h2>',));
register_sidebar(array('id'=>'home-footer-menu-one','name'=>'Home Footer Menu One','before_widget' => '','after_widget' => '','before_title' => '<h4>','after_title' => '</h4>',));
register_sidebar(array('id'=>'home-footer-menu-two','name'=>'Home Footer Menu Two','before_widget' => '','after_widget' => '','before_title' => '<h4>','after_title' => '</h4>',));
register_sidebar(array('id'=>'home-footer-menu-three','name'=>'Home Footer Menu Three','before_widget' => '','after_widget' => '','before_title' => '<h4>','after_title' => '</h4>',));
register_sidebar(array('id'=>'home-footer-menu-four','name'=>'Home Footer Menu Four','before_widget' => '','after_widget' => '','before_title' => '<h4>','after_title' => '</h4>',));
register_sidebar(array('id'=>'footer-middle','name'=>'Middle Footer','before_widget' => '','after_widget' => '','before_title' => '<h2 class="widgettittle">','after_title' => '</h2>',));
register_sidebar(array('id'=>'footer-bottom','name'=>'Bottom Footer','before_widget' => '','after_widget' => '','before_title' => '<h2 class="widgettittle">','after_title' => '</h2>',));

// thumbnail support
add_theme_support('post-thumbnails'); 

add_filter( 'embed_oembed_html', 'custom_oembed_filter', 10, 4 ) ;

function custom_oembed_filter($html, $url, $attr, $post_ID) {
    $return = '<div class="video-container">'.$html.'</div>';
    return $return;
}

add_filter( 'template_include', 'learning_center_post_template');

function learning_center_post_template( $template ) {
    if ( is_single( )  ) {
        $new_template = locate_template( array( 'single-learning-center.php' ) );
        if ( '' != $new_template ) {
            return $new_template ;
        }
    }
    return $template;
}

// Remove Query String
function _remove_script_version( $src ){
$parts = explode( '?ver', $src );
return $parts[0];
}
add_filter( 'script_loader_src', '_remove_script_version', 15, 1 );
add_filter( 'style_loader_src', '_remove_script_version', 15, 1 );

// Add a unique id attribute to the body tag of an HTML page
function id_the_body() {
        global $post, $wp_query, $wpdb;
        $post = $wp_query->post;
	$body_id = "";
        if ($post->post_type == 'page') $body_id = 'page-' . $post->ID;
        if ($post->post_type == 'post') $body_id = 'post-' . $post->ID;
        if ( is_front_page() ) $body_id = 'home';
        if ( is_home() ) $body_id = 'home';
        if ( is_category() ) $body_id = 'category-' . get_query_var('cat');
        if ( is_tag() ) $body_id = 'tag-' . get_query_var('tag');
        if ( is_author() ) $body_id = 'author-' . get_query_var('author');
        if ( is_date() ) $body_id = 'date-archive';
        if (is_search()) $body_id = 'search-archive';
        if (is_404()) $body_id = '404-error';
        if ($body_id) echo "id=\"$body_id\"";
}
// Add special class names for the parents of the page
function class_the_body($more_classes='') {
        global $post, $wp_query, $wpdb;
        $post = $wp_query->post;
		$parent_id_string = "";
        if ($post->ancestors) {
                /* reverse the order of the array elements b/c we want the immediate parent to be last in the class list */
                $parent_array = array_reverse($post->ancestors);
                foreach ($parent_array as $key => $parent_id) {
                        $parent_id_string = $parent_id_string . ' childof-' . 
$parent_id;
                }
        }
	$type = "";
        if ($post->post_type == 'page') $type = 'page';
        if ($post->post_type == 'post') $type = 'single';
        // these 2 are not needed since we id the body with home
        if (is_home()) $type = 'home';
        if (is_front_page()) $type = 'front';
        if (is_category()) $type = 'archive category-archive';
        if (is_tag()) $type = 'archive tag-archive';
        if (is_author()) $type = 'archive author-archive';
        // again, these 3 are not needed since we id the body with these
        if (is_date()) $type = 'archive date-archive';
        if (is_search()) $type = 'archive search-archive';
        if (is_404()) $type = '404-error';
        // need a lot of trimming b/c any combination of these could be blank
		if($parent_id_string) {
			$classes = trim($parent_id_string . ' ' . $more_classes);
		}else{
			$classes = trim($more_classes);
		}
        if ($type) $classes = $type . ' ' . $classes;
        $classes = trim($classes);
        if ($classes) echo $classes;
}

function set_shibbolethwall_post_views($postID) {
    $count_key = 'shibbolethwall_post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        $count = 0;
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
    }else{
        $count++;
        update_post_meta($postID, $count_key, $count);
    }
}
//To keep the count accurate, lets get rid of prefetching
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

function get_shibbolethwall_post_views($postID){
    $count_key = 'shibbolethwall_post_views_count';
    $count = get_post_meta($postID, $count_key, true);
    if($count==''){
        delete_post_meta($postID, $count_key);
        add_post_meta($postID, $count_key, '0');
        return "0 View";
    }
    return $count.' Views';
}

function limit_words($string, $word_limit) {
	$words = explode(' ', $string);

	return implode(' ', array_slice($words, 0, $word_limit));
}

add_filter( 'gform_validation_message', 'change_message', 10, 2 );
function change_message( $message, $form ) {
	return "";
}

add_filter( 'gform_tabindex', 'gform_tabindexer', 10, 2 );
function gform_tabindexer( $tab_index, $form = false ) {
    $starting_index = 1000; // if you need a higher tabindex, update this number
    if( $form )
        add_filter( 'gform_tabindex_' . $form['id'], 'gform_tabindexer' );
    return GFCommon::$tab_index >= $starting_index ? GFCommon::$tab_index : $starting_index;
}

add_filter( 'gform_next_button', 'input_to_button', 10, 2 );
add_filter( 'gform_previous_button', 'input_to_button', 10, 2 );
add_filter( 'gform_submit_button', 'input_to_button', 10, 2 );
function input_to_button( $button, $form ) {
    $dom = new DOMDocument();
    $dom->loadHTML( $button );
    $input = $dom->getElementsByTagName( 'input' )->item(0);
    $new_button = $dom->createElement( 'button' );
    $new_button->appendChild( $dom->createTextNode( $input->getAttribute( 'value' ) ) );
    $input->removeAttribute( 'value' );
    foreach( $input->attributes as $attribute ) {
	if (($attribute->name)=='id'){
		$old_id = $attribute->value;
		$input->setAttribute( $attribute->name, $attribute->value.'_temp' );
		$new_button->setAttribute( 'id', $old_id );
	} else{
		$new_button->setAttribute( $attribute->name, $attribute->value );
	}
    }
    $input->removeAttribute( 'id' );
    $input->parentNode->replaceChild( $new_button, $input );

    return $dom->saveHtml( $new_button );
}

/**
 * Abstract class for counting shares 
 */
interface Share_Counter {
  
  /**
   * Getting the share count
   */
  public static function get_share_count( $url );
  
}

/**
 * Facebook Shares
 */
class FacebookShareCount implements Share_Counter {
	public static function get_share_count( $url ) {
		$facebook_app_id = "1969305276679581"; // Please provide facebook app id e.g. "1810788365825924";
		$facebook_app_secret = "d58818d32a6f76832bedba3cecb5d43a"; //Please provide facebook app secret e.g. "7899abce0697c9d2b1355359b91f309c";
		$access_token = $facebook_app_id . '|' . $facebook_app_secret;
		$check_url = 'https://graph.facebook.com/v2.7/?id=' . urlencode(  $url ) . '&fields=share&access_token=' . $access_token;
		$response = wp_remote_retrieve_body( wp_remote_get( $check_url ) );
		$encoded_response = json_decode( $response, true );
		$share_count = intval( $encoded_response['share']['share_count'] );
		return $share_count;
	}
}

/**
 * Twitter Shares
 */
class TwitterShareCount implements Share_Counter {
	public static function get_share_count( $url ) {
		$check_url = 'http://public.newsharecounts.com/count.json?url=' . urlencode( $url );
		$response = wp_remote_retrieve_body( wp_remote_get( $check_url ) );
		$encoded_response = json_decode( $response, true );
		$share_count = intval( $encoded_response['count'] ); 
		return $share_count;
	}
}

/**
 * Google+ Shares
 */
class GoogleShareCount implements Share_Counter {
	public static function get_share_count( $url ) {
		if( !$url ) {
	    	return 0;
	    }
		if ( !filter_var($url, FILTER_VALIDATE_URL) ){
			return 0;
		}
	    foreach (array('apis', 'plusone') as $host) {
	        $ch = curl_init(sprintf('https://%s.google.com/u/0/_/+1/fastbutton?url=%s',
	                                      $host, urlencode($url)));
	        curl_setopt_array($ch, array(
	            CURLOPT_FOLLOWLOCATION => 1,
	            CURLOPT_RETURNTRANSFER => 1,
	            CURLOPT_SSL_VERIFYPEER => 0,
	            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 6.1; WOW64) ' .
	                                      'AppleWebKit/537.36 (KHTML, like Gecko) ' .
	                                      'Chrome/32.0.1700.72 Safari/537.36' ));
	        $response = curl_exec($ch);
	        $curlinfo = curl_getinfo($ch);
	        curl_close($ch);
	        if (200 === $curlinfo['http_code'] && 0 < strlen($response)) { break 1; }
	        $response = 0;
	    }
	    
	    if( !$response ) {
	    		return 0;
	    }
	    preg_match_all('/window\.__SSR\s\=\s\{c:\s(\d+?)\./', $response, $match, PREG_SET_ORDER);
	    return (1 === sizeof($match) && 2 === sizeof($match[0])) ? intval($match[0][1]) : 0;
	}
}

/**
 * LinkedIN Shares
 */
class LinkedINShareCount implements Share_Counter {
	public static function get_share_count( $url ) {
		$remote_get = json_decode( file_get_contents('https://www.linkedin.com/countserv/count/share?url=' . urlencode( $url ) . '&format=json'), true);
		 
		$share_count = $remote_get['count'];
		return $share_count; 
	}
}

/**
 * Pinterest Shares
 */
class PinterestShareCount implements Share_Counter {
	public static function get_share_count( $url ) {
		$check_url = 'http://api.pinterest.com/v1/urls/count.json?callback=pin&url=' . urlencode( $url );
		$response = wp_remote_retrieve_body( wp_remote_get( $check_url ) );
		 
		$response = str_replace( 'pin({', '{', $response);
		$response = str_replace( '})', '}', $response);
		$encoded_response = json_decode( $response, true );
		 
		$share_count = intval( $encoded_response['count'] ); 
		return $share_count;
	}
}

/**
 * StumbleUpon Shares
 */
class StumbleUponShareCount implements Share_Counter {
	public static function get_share_count( $url ) {
		$check_url = 'http://www.stumbleupon.com/services/1.01/badge.getinfo?url=' . urlencode( $url );
		$response = wp_remote_retrieve_body( wp_remote_get( $check_url ) );
		$encoded_response = json_decode( $response, true );
		$share_count = intval( $encoded_response['result']['views'] ); 
		return $share_count;
	}
}

add_filter( 'wp_nav_menu_items', 'primary_navigation_search_menu_item', 10, 2 );
function primary_navigation_search_menu_item( $items, $args ) {
    if ($args->theme_location == 'primary_navigation') {
        $items = (get_field('show_homecoming_title', 'option') ? '<li class="homecoming-title"><h1>' .
 get_field('title_large', 'option') . '<small>' . get_field('title_small', 'option') . '</small></h1></li>' : '') . $items . (get_field('show_search_form_after_menu', 'option') ? '<li class="search-menu-item" data-toggle="collapse" data-target="#menu_searchform"><i class="fa fa-search" aria-hidden="true"></i></li>' : '');
    }
    return $items;
}

//Custom Post Types code generated from CPTUI
add_action( 'init', 'cptui_register_my_cpts_slider' );
function cptui_register_my_cpts_slider() {
	$labels = array(
		"name" => __( 'Sliders', 'shibbolethwall' ),
		"singular_name" => __( 'Slider', 'shibbolethwall' ),
		"search_items" => __( 'Search Sliders', 'shibbolethwall' ),
		"all_items" => __( 'All Sliders', 'shibbolethwall' ),
		"edit_item" => __( 'Edit Slider', 'shibbolethwall' ),
		"update_item" => __( 'Update Slider', 'shibbolethwall' ),
		"add_new_item" => __( 'Add New Slider', 'shibbolethwall' ),
		"new_item_name" => __( 'New Slider', 'shibbolethwall' ),
		"menu_name" => __( 'Slider', 'shibbolethwall' ),
		);
	$args = array(
		"label" => __( 'Sliders', 'shibbolethwall' ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => false,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"has_archive" => false,
		"show_in_menu" => true,
				"exclude_from_search" => true,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array( "slug" => "slider", "with_front" => true ),
		"query_var" => true,
		"menu_position" => 5,"menu_icon" => "dashicons-slides",
		"supports" => array( "title", "editor", "thumbnail", "page-attributes" ),
		"taxonomies" => array( "slider_position" ),
);
	register_post_type( "slider", $args );
// End of cptui_register_my_cpts_slider()
}
add_action( 'init', 'cptui_register_my_taxes_slider_position' );
function cptui_register_my_taxes_slider_position() {
	$labels = array(
		"name" => __( 'Slider Position', 'shibbolethwall' ),
		"singular_name" => __( 'Slider Position', 'shibbolethwall' ),
		"search_items" => __( 'Search Slider Positions', 'shibbolethwall' ),
		"all_items" => __( 'All Slider Positions', 'shibbolethwall' ),
		"parent_item" => __( 'Parent Slider Position', 'shibbolethwall' ),
		"parent_item_colon" => __( 'Parent Slider Position:', 'shibbolethwall' ),
		"edit_item" => __( 'Edit Slider Position', 'shibbolethwall' ),
		"update_item" => __( 'Update Slider Position', 'shibbolethwall' ),
		"add_new_item" => __( 'Add New Slider Position', 'shibbolethwall' ),
		"new_item_name" => __( 'New Slider Position', 'shibbolethwall' ),
		"menu_name" => __( 'Slider Position', 'shibbolethwall' ),
		);
	$args = array(
		"label" => __( 'Slider Position', 'shibbolethwall' ),
		"labels" => $labels,
		"public" => true,
		"hierarchical" => true,
		"label" => "Slider Position",
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => array( 'slug' => 'slider_position', 'with_front' => true, ),
		"show_admin_column" => false,
		"show_in_rest" => false,
		"rest_base" => "",
		"show_in_quick_edit" => false,
	);
	register_taxonomy( "slider_position", array( "slider" ), $args );
// End cptui_register_my_taxes_slider_position()
}

add_filter( 'manage_edit-slider_columns', 'shibbolethwall_edit_slider_columns' ) ;
function shibbolethwall_edit_slider_columns( $columns ) {
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Slider Item' ),
		'slider_position' => __( 'Slider Position' ),
		'date' => __( 'Date' )
	);
	return $columns;
}
add_action( 'manage_slider_posts_custom_column', 'shibbolethwall_manage_slider_columns', 10, 2 );
function shibbolethwall_manage_slider_columns( $column, $post_id ) {
	global $post;
	switch( $column ) {
		/* If displaying the 'slider_position' column. */
		case 'slider_position' :
			/* Get the position for the post. */
			$terms = get_the_terms( $post_id, 'slider_position' );
			/* If terms were found. */
			if ( !empty( $terms ) ) {
				$out = array();
				/* Loop through each term, linking to the 'edit posts' page for the specific term. */
				foreach ( $terms as $term ) {
					$out[] = sprintf( '<a href="%s">%s</a>',
						esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'slider_position' => $term->slug ), 'edit.php' ) ),
						esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'slider_position', 'display' ) )
					);
				}
				/* Join the terms, separating them with a comma. */
				echo join( ', ', $out );
			}
			/* If no terms were found, output a default message. */
			else {
				_e( 'No Slider Position' );
			}
			break;
		/* Just break out of the switch statement for everything else. */
		default :
			break;
	}
}
add_filter( 'manage_edit-slider_sortable_columns', 'shibbolethwall_slider_sortable_columns' );
function shibbolethwall_slider_sortable_columns( $columns ) {
	$columns['slider_position'] = 'slider_position';
	return $columns;
}

?>