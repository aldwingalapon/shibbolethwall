<?php
/**
 * Handle issues with plugin and version compatibility
 *
 * @package   Gravity Forms Dynamics CRM Add-On
 * @author    Saint Systems, LLC
 * @link      http://www.saintsystems.com
 * @copyright Copyright 2016, Saint Systems, LLC
 *
 * @since 1.3
 */

/**
 * Handle Gravity Forms Dynamics CRM compatibility notices and fallback shortcodes
 * @since 1.3
 */
class GFDCRM_Compatibility {

	/**
	 * @var GFDCRM_Compatibility
	 */
	private static $_instance = null;

	/**
	 * @var bool Is Gravity Forms version valid and is Gravity Forms loaded?
	 */
	public static $valid_gravity_forms = false;

	/**
	 * @var bool Is the WordPress installation compatible?
	 */
	public static $valid_wordpress = false;

	/**
	 * @var bool Is CURL enabled?
	 */
	public static $valid_curl = false;

	/**
	 * @var bool Is the server's PHP version compatible?
	 */
	public static $valid_php = false;

	/**
	 * @var array Holder for notices to be displayed in frontend shortcodes if not valid GF
	 */
	static private $notices = array();

	function __construct() {

		self::$valid_gravity_forms = self::check_gravityforms();

		self::$valid_wordpress = self::check_wordpress();

		self::$valid_php = self::check_php();

		self::$valid_curl = self::check_curl();

		$this->add_hooks();
	}

	function add_hooks() {

		add_filter( 'gravityformsdynamicscrm/admin/notices', array( $this, 'insert_admin_notices' ) );

		$this->add_fallback_shortcode();
	}

	/**
	 * Add the compatibility notices to the other admin notices
	 * @param array $notices
	 *
	 * @return array
	 */
	function insert_admin_notices( $notices = array() ) {

		return array_merge( $notices, self::$notices );

	} //end function insert_admin_notices

	/**
	 * @return GFDCRM_Compatibility
	 */
	public static function get_instance() {

		if ( empty( self::$_instance ) ) {
			self::$_instance = new self;
		}

		return self::$_instance;

	}

	/**
	 * Is everything compatible with this version of the plugin?
	 * @return bool
	 */
	public static function is_valid() {

		return ( self::is_valid_gravity_forms() 
			  && self::is_valid_wordpress() 
			  && self::is_valid_curl() 
			  && self::is_valid_php()
		);

	}

	/**
	 * Is the version of WordPress compatible?
	 * @since 1.3
	 */
	static function is_valid_wordpress() {
		return self::$valid_wordpress;
	}

	/**
	 * @since 1.3
	 * @return bool
	 */
	static function is_valid_gravity_forms() {
		return self::$valid_gravity_forms;
	}

	/**
	 * @since 1.3.2
	 * @return bool
	 */
	static function is_valid_curl() {
		return self::$valid_curl;
	}

	/**
	 * @since 1.3
	 * @return bool
	 */
	static function is_valid_php() {
		return self::$valid_php;
	}

	/**
	 * @since 1.3
	 * @return bool
	 */
	function add_fallback_shortcode() {

		// If Gravity Forms doesn't exist or is outdated, load the admin view class to
		// show the notice, but not load any post types or process shortcodes.
		// Without Gravity Forms, there is no Gravity Forms Dynamics CRM. Beautiful, really.
		if( ! self::is_valid() ) {

			// If the plugin's not loaded, might as well hide the shortcode for people.
			add_shortcode( 'gravityformsdynamicscrm', array( $this, '_shortcode_gf_notice'), 10, 3 );

		}
	}

	/**
	 * Get admin notices
	 * @since 1.3
	 * @return array
	 */
	public static function get_notices() {
		return self::$notices;
	}

	/**
	 * @since 1.3
	 *
	 * @param array $atts
	 * @param null $content
	 * @param string $shortcode
	 *
	 * @return null|string NULL returned if user can't manage options. Notice shown with a warning that GF isn't supported.
	 */
	public function _shortcode_gf_notice( $atts = array(), $content = null, $shortcode = 'gravityformsdynamicscrm' ) {

		if( ! current_user_can('manage_options') ) {
			return null;
		}

		$notices = self::get_notices();

		$message = '<div style="border:1px solid red; padding: 15px;"><p style="text-align:center;"><em>' . esc_html__( 'You are seeing this notice because you are an administrator. Other users of the site will see nothing.', 'gravityformsdynamicscrm') . '</em></p>';
		foreach( (array)$notices as $notice ) {
			$message .= wpautop( $notice['message'] );
		}
		$message .= '</div>';

		return $message;

	}

	/**
	 * Is the version of PHP compatible?
	 *
	 * @since 1.3
	 * @return boolean
	 */
	public static function check_php() {
		if( false === version_compare( phpversion(), GF_DYNAMICS_CRM_MIN_PHP_VERSION , '>=' ) ) {

			self::$notices['php_version'] = array(
				'class' => 'error',
				'message' => sprintf( __( "%sGravity Forms Dynamics CRM requires PHP Version %s or newer.%s \n\nYou're using Version %s. Please ask your host to upgrade your server's PHP.", 'gravityformsdynamicscrm' ), '<h3>', GF_DYNAMICS_CRM_MIN_PHP_VERSION, "</h3>\n\n", '<span style="font-family: Consolas, Courier, monospace;">'.phpversion().'</span>' )
			);

			return false;
		}

		return true;
	}

	/**
	 * Is the PHP cURL extension enabled and is the version compatible?
	 *
	 * @since 1.3.2
	 * @return boolean
	 */
	public static function check_curl() {

		// Bypass other checks: if the curl is enabled exists
		if ( !function_exists( 'curl_version' ) ) {

			// PHP CURL isn't even enabled, abort! :)
			self::$notices['curl_version'] = array(
				'class' => 'error',
				'message' => sprintf( __( '%sGravity Forms Dynamics CRM requires the PHP cURL Extension to be installed/enabled in order to run properly.%s %sClick Here%s to see how to install/enable PHP cURL on your server.', 'gravityformsdynamicscrm' ), '<h3>', "</h3>\n\n", '<a href="http://php.net/manual/en/curl.installation.php" target="_blank">' , '</a>' ),
			);

			return false;

		}

		// If we got here, PHP CURL is enabled, so let's make sure the version's right
		// If so, we're good.
		$curl_version = curl_version();

		if ( !isset( $curl_version['version'] ) ) {

			// Can't determine PHP CURL version, abort! :)
			self::$notices['curl_version'] = array(
				'class' => 'error',
				'message' => sprintf( __( "%sGravity Forms Dynamics CRM requires PHP cURL Version %s or newer.%s \n\nWe were unable to determine your version. Please ask your host to upgrade your server's PHP cURL version.", 'gravityformsdynamicscrm' ), '<h3>', GF_DYNAMICS_CRM_MIN_CURL_VERSION, "</h3>\n\n" )
			);

			return false;

		}

		$curl_version = $curl_version['version'];
		
		if ( false === version_compare( $curl_version, GF_DYNAMICS_CRM_MIN_CURL_VERSION , '>=' ) ) {

			self::$notices['curl_version'] = array(
				'class' => 'error',
				'message' => sprintf( __( "%sGravity Forms Dynamics CRM requires PHP cURL Version %s or newer.%s \n\nYou're using Version %s. Please ask your host to upgrade your server's PHP cURL version.", 'gravityformsdynamicscrm' ), '<h3>', GF_DYNAMICS_CRM_MIN_CURL_VERSION, "</h3>\n\n", '<span style="font-family: Consolas, Courier, monospace;">'.$curl_version.'</span>' )
			);

			return false;
		}

		// If we got here, we're good to go. PHP cURL is enabled and the version's >= GF_DYNAMICS_CRM_MIN_CURL_VERSION
		// Carry on!
		return true;

	} //end public static function check_curl()

	/**
	 * Is WordPress compatible?
	 *
	 * @since 1.3
	 * @return boolean
	 */
	public static function check_wordpress() {
		global $wp_version;

		if( version_compare( $wp_version, GF_DYNAMICS_CRM_MIN_WP_VERSION ) <= 0 ) {

			self::$notices['wp_version'] = array(
				'class' => 'error',
				'message' => sprintf( __( "%sGravity Forms Dynamics CRM requires WordPress %s or newer.%s \n\nYou're using Version %s. Please upgrade your WordPress installation.", 'gravityformsdynamicscrm' ), '<h3>', GF_DYNAMICS_CRM_MIN_WP_VERSION, "</h3>\n\n", '<span style="font-family: Consolas, Courier, monospace;">'.$wp_version.'</span>' )
			);

			return false;
		}

		return true;

	} //end public static function check_wordpress()


	/**
	 * Check if Gravity Forms plugin is active and show notice if not.
	 *
	 * @since 1.3
	 *
	 * @access public
	 * @return boolean True: checks have been passed; GV is fine to run; False: checks have failed, don't continue loading
	 */
	public static function check_gravityforms() {

		// Bypass other checks: if the class exists
		if ( class_exists( 'GFCommon' ) ) {

			// and the version's right, we're good.
			if ( true === version_compare( GFCommon::$version, GF_DYNAMICS_CRM_MIN_GF_VERSION, ">=" ) ) {
				return true;
			}

			// Or the version's wrong
			self::$notices['gf_version'] = array(
				'class' => 'error',
				'message' => sprintf( __( "%sGravity Forms Dynamics CRM requires Gravity Forms Version %s or newer.%s \n\nYou're using Version %s. Please update your Gravity Forms or purchase a license. %sGet Gravity Forms%s - starting at $39%s%s", 'gravityformsdynamicscrm' ), '<h3>', GF_DYNAMICS_CRM_MIN_GF_VERSION, "</h3>\n\n", '<span style="font-family: Consolas, Courier, monospace;">'.GFCommon::$version.'</span>', "\n\n".'<a href="http://www.gravityforms.com?ref=saintsystems" class="button button-secondary button-large button-hero" target="_blank">' , '<em>', '</em>', '</a>')
			);

			return false;
		}

		$gf_status = self::get_plugin_status( 'gravityforms/gravityforms.php' );

		/**
		 * The plugin is activated and yet somehow GFCommon didn't get picked up...
		 * OR
		 * It's the Network Admin and we just don't know whether the sites have GF activated themselves.
		 */
		if ( true === $gf_status || is_network_admin() ) {
			return true;
		}

		// If GFCommon doesn't exist, assume GF not active
		$return = false;

		switch ( $gf_status ) {
			case 'inactive':

				// Required for multisite
				if( ! function_exists('wp_create_nonce') ) {
					require_once ABSPATH . WPINC . '/pluggable.php';
				}

				// Otherwise, throws an error on activation & deactivation "Use of undefined constant LOGGED_IN_COOKIE"
				if( is_multisite() ) {
					wp_cookie_constants();
				}

				$return = false;

				$button = function_exists('is_network_admin') && is_network_admin() ? '<strong><a href="#gravity-forms">' : '<strong><a href="'. wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=gravityforms/gravityforms.php' ), 'activate-plugin_gravityforms/gravityforms.php') . '" class="button button-large">';

				self::$notices['gf_inactive'] = array( 'class' => 'error', 'message' => sprintf( __( '%sGravity Forms Dynamics CRM requires Gravity Forms to be active. %sActivate Gravity Forms%s to use the Gravity Forms Dynamics CRM plugin.', 'gravityformsdynamicscrm' ), '<h3>', "</h3>\n\n". $button, '</a></strong>' ) );
				break;
			default:
				self::$notices['gf_installed'] = array( 'class' => 'error', 'message' => sprintf( __( '%sGravity Forms Dynamics CRM requires Gravity Forms to be installed in order to run properly. %sGet Gravity Forms%s - starting at $39%s%s', 'gravityformsdynamicscrm' ), '<h3>', "</h3>\n\n".'<a href="http://www.gravityforms.com?ref=saintsystems" class="button button-secondary button-large button-hero" target="_blank">' , '<em>', '</em>', '</a>') );
				break;
		}

		return $return;

	} //end public static function check_gravityforms()

	/**
	 * Check if specified plugin is active, inactive or not installed
	 *
	 * @access public
	 * @static
	 * @param string $location (default: '')
	 * @return boolean|string True: plugin is active; False: plugin file doesn't exist at path; 'inactive' it's inactive
	 */
	public static function get_plugin_status( $location = '' ) {

		if ( ! function_exists('is_plugin_active') ) {
			include_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}

		if ( is_network_admin() && is_plugin_active_for_network( $location ) ) {
			return true;
		}

		if ( !is_network_admin() && is_plugin_active( $location ) ) {
			return true;
		}

		if (
			!file_exists( trailingslashit( WP_PLUGIN_DIR ) . $location ) &&
			!file_exists( trailingslashit( WPMU_PLUGIN_DIR ) . $location )
		) {
			return false;
		}

		return 'inactive';
	}

}

GFDCRM_Compatibility::get_instance();
