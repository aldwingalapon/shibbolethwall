<?php
/**
 * @file gravityformsdynamicscrm.php
 *
 * The Gravity Forms Dynamics CRM Add-On plugin
 *
 * Integrates Gravity Forms with Dynamics CRM, allowing form submissions to be automatically sent to your Dynamics CRM account.
 *
 * @package   Gravity Forms Dynamics CRM Add-On
 * @license   GPL2+
 * @author    Saint Systems
 * @link      http://www.saintsystems.com
 * @copyright Copyright 2015, Saint Systems, LLC
 *
 * @wordpress-plugin
 * Plugin Name:       	Gravity Forms Dynamics CRM Add-On
 * Plugin URI:        	http://www.saintsystems.com/products/gravityforms-dynamics-crm-add-on/
 * Description:       	Integrates Gravity Forms with Dynamics CRM, allowing form submissions to be automatically sent to your Dynamics CRM account.
 * Version:          	1.3.7
 * Author:            	Saint Systems
 * Author URI:        	http://www.saintsystems.com
 * Text Domain:       	gravityformsdynamicscrm
 * License:           	GPLv2 or later
 * License URI: 		http://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path:			/languages
 */

/** If this file is called directly, abort. */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/** Constants */

/**
 * Full path to the GFDCRM file
 * @define "GF_DYNAMICS_CRM_FILE" "./gravityformsdynamicscrm.php"
 */
define( 'GF_DYNAMICS_CRM_FILE', __FILE__ );

/**
 * The URL to this file
 */
define( 'GF_DYNAMICS_CRM_URL', plugin_dir_url( __FILE__ ) );

/**
 * The absolute path to the plugin directory
 * @define "GF_DYNAMICS_CRM_DIR" "./"
 */
define( 'GF_DYNAMICS_CRM_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Gravity Forms Dynamics CRM requires at least this version of Gravity Forms to function properly.
 */
define( 'GF_DYNAMICS_CRM_MIN_GF_VERSION', '1.9.10.16' );

/**
 * Gravity Forms Dynamics CRM requires at least this version of WordPress to function properly.
 * @since 1.3
 */
define( 'GF_DYNAMICS_CRM_MIN_WP_VERSION', '3.3' );

/**
 * Gravity Forms Dynamics CRM requires at least this version of PHP CURL to function properly.
 * @since 1.3.2
 */
define( 'GF_DYNAMICS_CRM_MIN_CURL_VERSION', '7.0' );

/**
 * Gravity Forms Dynamics CRM requires at least this version of PHP to function properly.
 * @since 1.3
 */
define( 'GF_DYNAMICS_CRM_MIN_PHP_VERSION', '5.3.24' );

define( 'GF_DYNAMICS_CRM_PLUGIN_URL', plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) );

require_once( GF_DYNAMICS_CRM_DIR . 'includes/helper-functions.php' );
require_once( GF_DYNAMICS_CRM_DIR . 'includes/class-gfdcrm-compatibility.php' );

add_action( 'gform_loaded', array( 'GF_Dynamics_CRM_Bootstrap', 'load' ), 5 );

class GF_Dynamics_CRM_Bootstrap {

	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}

		require_once( GF_DYNAMICS_CRM_DIR . 'class-gf-dynamics-crm.php' );

		GFAddOn::register( 'GFDynamicsCRM' );
	}
}

/** Register hooks that are fired when the plugin is activated and deactivated. */
if ( is_admin() ) {
	register_activation_hook( __FILE__, array( 'GFDCRM_Plugin', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'GFDCRM_Plugin', 'deactivate' ) );
}

/**
 * GFDCRM_Plugin main class
 */
final class GFDCRM_Plugin {

	const version = '1.3.7';

	private static $_instance;

	private static $admin_notices = array();

	private static $dismissed_notices = array();

	/**
	 * Singleton instance
	 *
	 * @return GFDCRM_Plugin   GFDCRM_Plugin object
	 */
	public static function get_instance() {

		if ( empty( self::$_instance ) ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		require_once( GF_DYNAMICS_CRM_DIR . 'includes/class-gfdcrm-admin-notices.php' );

		if( ! GFDCRM_Compatibility::is_valid() ) {
			return;
		}

		$this->add_hooks();

	} //end function __construct

	/**
	 * Add hooks to set up the plugin
	 *
	 * @access public
	 * @return void
	 */
	public function add_hooks() {

		// Load plugin text domain
		//add_action( 'init', array( $this, 'load_plugin_textdomain' ), 1 );

		if ( is_admin() ) {
			add_action( 'after_plugin_row_gravityformsdynamicscrm/gravityformsdynamicscrm.php', array( $this, 'plugin_row' ) );
			add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
			add_filter( 'gform_custom_merge_tags', array( $this, 'gform_custom_merge_tags' ), 10, 4 );
		}

		add_action( 'network_admin_notices', array( $this, 'dismiss_notice' ), 50 );
		add_action( 'admin_notices', array( $this, 'dismiss_notice' ), 50 );
		add_action( 'admin_notices', array( $this, 'admin_notice' ), 100 );
		add_action( 'network_admin_notices', array( $this, 'admin_notice' ), 100 );
		add_action( 'wp_ajax_gfdcrm_disconnect', array( $this, 'disconnect' ) );
		add_action( 'wp_ajax_gfdcrm_get_module_fields', array( $this, 'get_module_fields' ) );
		add_filter( 'gform_replace_merge_tags', array( $this, 'gform_replace_merge_tags' ), 10, 7 );

	} //end function register_hooks

	/**
	 * Add custom merge tags to merge tag options
	 *
	 * @since 1.2.0
	 *
	 * @param array $existing_merge_tags
	 * @param int $form_id GF Form ID
	 * @param GF_Field[] $fields Array of fields in the form
	 * @param string $element_id The ID of the input that Merge Tags are being used on
	 *
	 * @return array Modified merge tags
	 */
	public function gform_custom_merge_tags( $existing_merge_tags = array(), $form_id, $fields = array(), $element_id = '' ) {

		$created_by_merge_tags = array(
			array(
				'label' => __('Dynamics CRM: Entity ID', 'gravityformsdynamicscrm'),
				'tag' => '{dynamicscrm:entity_id}'
			),
			array(
				'label' => __('Dynamics CRM: Entity Type', 'gravityformsdynamicscrm'),
				'tag' => '{dynamicscrm:entity_type}'
			),
			array(
				'label' => __('Dynamics CRM: Entity URL', 'gravityformsdynamicscrm'),
				'tag' => '{dynamicscrm:entity_url}'
			)
		);

		//return the form object from the php hook
		return array_merge( $existing_merge_tags, $created_by_merge_tags );
	}

	/**
	 * Instead of adding multiple hooks, add all hooks into this one method to improve speed
	 *
	 * @since 1.2.0
	 *
	 * @param string $text Text to replace
	 * @param array|boolean $form Gravity Forms form array
	 * @param array $entry Entry array
	 * @param bool $url_encode Whether to URL-encode output
	 * @param bool $esc_html Whether to apply `esc_html()` to output
	 *
	 * @return mixed
	 */
	public function gform_replace_merge_tags(  $text, $form = array(), $entry = array(), $url_encode = false, $esc_html = false ) {

		/**
		 * This prevents the gform_replace_merge_tags filter from being called twice, as defined in:
		 * @see GFCommon::replace_variables()
		 * @see GFCommon::replace_variables_prepopulate()
		 */
		if( false === $form ) {
			return $text;
		}

		// Process the merge vars here
		$text = $this->replace_user_variables_created_by( $text, $form, $entry, $url_encode, $esc_html );

		return $text;
	}

	/**
	 * Exactly like Gravity Forms' User Meta functionality, but instead shows information the Dynamics CRM record created.
	 *
	 * @since 1.2.0
	 *
	 * @param string $text Text to replace
	 * @param array $form Gravity Forms form array
	 * @param array $entry Entry array
	 * @param bool $url_encode Whether to URL-encode output
	 * @param bool $esc_html Whether to apply `esc_html()` to output
	 *
	 * @return string Text, with user variables replaced, if they existed
	 */
	private function replace_user_variables_created_by( $text, $form = array(), $entry = array(), $url_encode = false, $esc_html = false ) {

		// Is there is {dynamics_crm:[xyz]} merge tag?
		preg_match_all( "/\{dynamicscrm:(.*?)\}/", $text, $matches, PREG_SET_ORDER );

		// If there are no matches OR the Entry `dynamicscrm` isn't set or is 0 (no user)
		if( empty( $matches ) ) {
			return $text;
		}

		// Get the creator of the entry
		//$entry_creator = new WP_User( $entry['dynamics_crm'] );

		foreach ( $matches as $match ) {

			$full_tag = $match[0];
			$property = $match[1];

			$value = gform_get_meta( $entry['id'], 'dynamicscrm_' . $property );

			$value = $url_encode ? urlencode( $value ) : $value;

			$esc_html = $esc_html ? esc_html( $value ) : $value;

			$text = str_replace( $full_tag, $value, $text );
		}

		return $text;
	}

	/**
	 * Modify plugin action links at plugins screen
	 *
	 * @access public
	 * @static
	 * @param mixed $links
	 * @return array Action links with Support included
	 */
	public static function plugin_action_links( $links, $file ) {

		if ( $file != plugin_basename( __FILE__ ) ) {
			return $links;
		}

		$action = array(
			sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php' ) ) . '?page=gf_settings&subview=gravityformsdynamicscrm', esc_html__( 'Settings', 'gravityformsdynamicscrm' ) ),
			'<a href="https://www.saintsystems.com/support/gravity-forms-dynamics-crm-add-on" target="_blank">' . esc_html__( 'Support', 'gravityformsdynamicscrm' ) . '</a>'
		);

		return array_merge( $action, $links );
	}

	//Displays message on Plugin's page
	public static function plugin_row( $plugin_name ) {

		$settings = get_option( 'gravityformsaddon_gravityformsdynamicscrm_settings' );

		if ( ! rgar( $settings, 'license_key' ) || rgar( $settings, 'license_key_status' ) != 'valid' ) {

			//$plugin_name = 'gravityforms/gravityforms.php';

			//$new_version = version_compare( GFCommon::$version, $version_info['version'], '<' ) ? __( 'There is a new version of Gravity Forms available.', 'gravityforms' ) . ' <a class="thickbox" title="Gravity Forms" href="plugin-install.php?tab=plugin-information&plugin=gravityforms&TB_iframe=true&width=640&height=808">' . sprintf( __( 'View version %s Details', 'gravityforms' ), $version_info['version'] ) . '</a>. ' : '';

			echo '</tr><tr class="plugin-update-tr"><td colspan="3" class="plugin-update"><div class="update-message">' . __( sprintf( '%sRegister%s your copy of Gravity Forms Dynamics CRM Add-On to receive access to automatic upgrades and support. Need a license key? %sPurchase one now%s.', '<a href="' . admin_url() . 'admin.php?page=gf_settings&subview=gravityformsdynamicscrm">', '</a>', '<a href="http://www.saintsystems.com/products/gravity-forms-dynamics-crm-add-on/#utm_source=gravityformsdynamicscrm-plugin-settings-link&utm_medium=textlink&utm_campaign=purchase-link" target="_blank">', '</a>' ), 'gravityformsdynamicscrm' ) . '</div></td>';
		}
	}

	/**
	 * Should the notice be shown in the admin (Has it been dismissed already)?
	 *
	 * If the passed notice array has a `dismiss` key, the notice is dismissable. If it's dismissable,
	 * we check against other notices that have already been dismissed.
	 *
	 * @param  string $notice            Notice array, set using `add_notice()`.
	 * @return boolean                   True: show notice; False: hide notice
	 */
	function _maybe_show_notice( $notice ) {

		// There are no dismissed notices.
		if( empty( self::$dismissed_notices ) ) {
			return true;
		}

		// Has the
		$is_dismissed = !empty( $notice['dismiss'] ) && in_array( $notice['dismiss'], self::$dismissed_notices );

		return $is_dismissed ? false : true;
	}

	/**
	 * Get admin notices
	 * @since 1.0
	 * @return array
	 */
	public static function get_notices() {
		return self::$admin_notices;
	}

	/**
	 * Handle whether to display notices in Multisite based on plugin activation status
	 *
	 * @since 1.0
	 *
	 * @return bool True: show the notices; false: don't show
	 */
	private function check_show_multisite_notices() {

		if( ! is_multisite() ) {
			return true;
		}

		// It's network activated but the user can't manage network plugins; they can't do anything about it.
		if( GFDCRM_Plugin::is_network_activated() && ! is_main_site() ) {
			return false;
		}

		// or they don't have admin capabilities
		if( ! is_super_admin() ) {
			return false;
		}

		return true;
	}

	/**
	 * Dismiss a GFDCRM notice - stores the dismissed notices for 16 weeks
	 * @return void
	 */
	public function dismiss_notice() {

		// No dismiss sent
		if( empty( $_GET['gfdcrm-dismiss'] ) ) {
			return;
		}

		// Invalid nonce
		if( !wp_verify_nonce( $_GET['gfdcrm-dismiss'], 'dismiss' ) ) {
			return;
		}

		$notice_id = esc_attr( $_GET['notice'] );

		//don't display a message if use has dismissed the message for this version
		$dismissed_notices = (array)get_transient( 'gfdcrm_dismissed_notices' );

		$dismissed_notices[] = $notice_id;

		$dismissed_notices = array_unique( $dismissed_notices );

		// Remind users every 16 weeks
		set_transient( 'gfdcrm_dismissed_notices', $dismissed_notices, WEEK_IN_SECONDS * 16 );

	}

	/**
	 * Outputs the admin notices generated by the plugin
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function admin_notice() {

		/**
		 * Modify the notices displayed
		 * @since 1.0
		 */
		$notices = apply_filters( 'gfdcrm/admin/notices', self::$admin_notices );

		if ( empty( $notices ) || ! $this->check_show_multisite_notices() ) {
			return;
		}

		//don't display a message if use has dismissed the message for this version
		self::$dismissed_notices = isset( $_GET['show-dismissed-notices'] ) ? array() : (array)get_transient( 'gravityview_dismissed_notices' );

		foreach( $notices as $notice ) {

			if( false === $this->_maybe_show_notice( $notice ) ) {
				continue;
			}

			echo '<div id="message" class="notice '. gravityview_sanitize_html_class( $notice['class'] ).'">';

			// Too cute to leave out.
			//echo gravityview_get_floaty();

			if( !empty( $notice['title'] ) ) {
				echo '<h3>'.esc_html( $notice['title'] ) .'</h3>';
			}

			echo wpautop( $notice['message'] );

			if( !empty( $notice['dismiss'] ) ) {

				$dismiss = esc_attr($notice['dismiss']);

				$url = esc_url( add_query_arg( array( 'gfdrcm-dismiss' => wp_create_nonce( 'dismiss' ), 'notice' => $dismiss ) ) );

				echo wpautop( '<a href="'.$url.'" data-notice="'.$dismiss.'" class="button-small button button-secondary">'.esc_html__( 'Dismiss', 'gravityformsdynamicscrm' ).'</a>' );
			}

			echo '<div class="clear"></div>';
			echo '</div>';

		}

		//reset the notices handler
		self::$admin_notices = array();
	}

	/**
	 * Add a notice to be displayed in the admin.
	 * @param array $notice Array with `class` and `message` keys. The message is not escaped.
	 */
	public static function add_notice( $notice = array() ) {

		$notice['class'] = empty( $notice['class'] ) ? 'error' : $notice['class'];

		self::$admin_notices[] = $notice;
	}

	/**
	 * Handle oauth actions
	 */
	public static function template_redirect() {

		global $wp_query;
    
	    // If our endpoint isn't hit, just return
	    if ( ! isset( $wp_query->query_vars['gfdcrm_action'] ) ) {
	        return;
	    }

		$gfdcrm_action = get_query_var( 'gfdcrm_action' );

		if ( isset( $gfdcrm_action ) ) {
			switch ( $gfdcrm_action ) {
				case 'authorize':
					self::authorize();
					break;
					exit;
				case 'authorized':
					self::authorized();
					break;
					exit;
			}
		}

	} //end function template_redirect

	/**
	 * Handle oauth authorize request
	 */
	public static function authorize() {

		if ( ! class_exists( 'GF_Dynamics_CRM_OAuth_Client' ) ) {
			require_once( GF_DYNAMICS_CRM_DIR . 'includes/class-gf-dynamics-crm-oauth-client.php' );
		}

        $client = new GF_Dynamics_CRM_OAuth_Client();
        
	    $client->authorize();

	} //end function authorize

	/**
	 * Remove oauth credentials
	 */
	public function disconnect() {

		delete_option( 'gravityformsdynamicscrm_credentials' );
		echo json_encode( array( 
			'status_code' => 200,
			'message' => 'Success',
		 ) );
		die();

	} //end function disconnect

	/**
	 * Get Module Fields
	 * @since 1.1.0
	 */
	public function get_module_fields() {

		$gfdcrm = GFDynamicsCRM::get_instance();

		$gfdcrm->get_module_fields();
		die();

	} //end function disconnect

	/**
	 * Handle oauth authorization callback
	 */
	public static function authorized() {

		if ( ! class_exists( 'GF_Dynamics_CRM_OAuth_Client' ) ) {
			require_once( GF_DYNAMICS_CRM_DIR . 'includes/class-gf-dynamics-crm-oauth-client.php' );
		}

		$client = new GF_Dynamics_CRM_OAuth_Client();

		if ( isset( $_COOKIE['gfdcrm.authstate'] ) && $_COOKIE['gfdcrm.authstate'] !== $_GET['state']) {
    		die('error: state does not match');
  		}
  		
  		unset( $_COOKIE['gfdcrm.authstate'] );
  		
  		setcookie( 'gfdcrm.authstate', null, -1, COOKIEPATH, COOKIE_DOMAIN );
	    
	    $response = $client->acquire_token_with_authorization_code();

		if ( !isset( $response['access_token'] ) 
			&& !isset( $response['expires_on'] ) 
			&& !isset( $response['refresh_token'] ) 
			//&& !isset( $response['resource'] )
			&& !isset( $response['token_type'] )
			//&& !isset( $response['scope'] )
			//&& !isset( $response['id_token'] ) 
			) {
			echo('Error authorizing:');
			echo('<pre>');
	    	print_r($response);
	    	echo('</pre>');
	    	exit;
		}

	    echo '<script>window.close();</script>';
	    exit();

	} //end function authorized

	/**
	 * Check whether GFDCRM is network activated
	 * @since 1.0
	 * @return bool
	 */
	public static function is_network_activated() {
		return is_multisite() && ( function_exists( 'is_plugin_active_for_network' ) && is_plugin_active_for_network( 'gravityformsdynamicscrm/gravityformsdynamicscrm.php' ) );
	}

	/**
	 * Plugin activate function.
	 *
	 * @access public
	 * @static
	 * @param mixed $network_wide
	 * @return void
	 */
	public static function activate( $network_wide = false ) {

		// Add the rewrite rule on activation
		self::rewrite_rules();

		flush_rewrite_rules();

		// Update the current GV version
		update_option( 'gfdrcm_version', self::version );

	} //end function activate

	/**
	 * Plugin deactivate function.
	 *
	 * @access public
	 * @static
	 * @param mixed $network_wide
	 * @return void
	 */
	public static function deactivate( $network_wide ) {

		flush_rewrite_rules();

	} //end function deactivate

	public static function rewrite_rules() {

		add_rewrite_rule( 'gfdcrm/authorize/?([^/]*)', 'index.php?gfdcrm_action=authorize', 'top' );
		add_rewrite_rule( 'gfdcrm/callback/?([^/]*)', 'index.php?gfdcrm_action=authorized', 'top' );

	} //end function rewrite_rules

	public static function query_vars( $vars ) {

		$vars[] = 'gfdcrm_action';
		return $vars;

	} //end function query_vars

} //end class GFDCRM_Plugin

add_action( 'plugins_loaded', array( 'GFDCRM_Plugin', 'get_instance' ), 1 );

add_action( 'init', array( 'GFDCRM_Plugin', 'rewrite_rules' ) );

add_action( 'query_vars', array( 'GFDCRM_Plugin', 'query_vars' ) );

add_action( 'template_redirect', array( 'GFDCRM_Plugin', 'template_redirect' ) );

function gf_dynamics_crm() {
	return GFDynamicsCRM::get_instance();
}
