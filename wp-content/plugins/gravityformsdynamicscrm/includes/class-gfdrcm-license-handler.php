<?php

class GFDCRM_License_Handler {

	/**
	 * @var GFDynamicsCRM
	 */
	private $FeedAddOn;

	const name = 'Gravity Forms Dynamics CRM Add-On';

	const author = 'Saint Systems';
	
	const url = 'https://www.saintsystems.com';
	
	const version = GFDCRM_Plugin::version;

	private $EDD_SL_Plugin_Updater;

	/**
	 * @var GFDCRM_License_Handler
	 */
	public static $instance;

	/**
	 * @param GFDynamicsCRM $GFFeedAddOn
	 *
	 * @return GFDCRM_License_Handler
	 */
	public static function get_instance( GFDynamicsCRM $GFFeedAddOn ) {
		if( empty( self::$instance ) ) {
			self::$instance = new self( $GFFeedAddOn );
		}
		return self::$instance;
	}
	
	private function __construct( GFDynamicsCRM $GFFeedAddOn ) {

		$this->FeedAddOn = $GFFeedAddOn;

		$this->setup_edd();
		
		$this->add_hooks();
	}

	private function add_hooks() {
		add_action( 'wp_ajax_gravityformsdynamicscrm_license', array( $this, 'license_call' ) );
	}

	function localize_scripts() {

		$settings = $this->FeedAddOn->get_plugin_settings();

		$status = trim( rgget( 'license_key_status', $settings ) );
		$key = trim( rgget( 'license_key', $settings ) );

		if( !empty( $key ) ) {
			$response = rgget( 'license_key_response', $settings );
			$response = is_array( $response ) ? (object) $response : json_decode( $response );
		} else {
			$response = array();
		}

		wp_localize_script( 'gfdcrm-admin', 'GFDCRMGlobals', array(
			'license_box' => $this->get_license_message( $response ),
			'tooltips' => array(
				'validating_license_key' => array(
					'title' => __( 'Validating...' , 'gravityformsdynamicscrm' ),
					'text' => __( 'One moment while we validate your license key.' , 'gravityformsdynamicscrm' ),
				),
				'validating_credentials' => array(
					'title' => __( 'Validating...' , 'gravityformsdynamicscrm' ),
					'text' => __( 'One moment while we validate your credentials.' , 'gravityformsdynamicscrm' ),
				),
				'invalid_credentials' => array(
					'title' => __( 'Disconnected' , 'gravityformsdynamicscrm' ),
					'text'	=> __( 'Unable to connect with the specified credentials.' , 'gravityformsdynamicscrm' ),
				),
				'valid_credentials' => array(
					'title' => __( 'Connected' , 'gravityformsdynamicscrm' ),
					'text'	=> __( 'Your credentials are valid.' , 'gravityformsdynamicscrm' ),
				),
			)
		));
	}

	function settings_edd_license_activation( $echo ) {

		$settings = $this->FeedAddOn->get_plugin_settings();

		$status = trim( rgget( 'license_key_status', $settings ) );
		$key = trim( rgget( 'license_key', $settings ) );

		if( !empty( $key ) ) {
			$response = rgget( 'license_key_response', $settings );
			$response = is_array( $response ) ? (object) $response : json_decode( $response );
		} else {
			$response = array();
		}

		$fields = array(
			array(
				'name'  => 'edd-activate',
				'value' => __('Activate License', 'gravityformsdynamicscrm'),
				'data-pending_text' => __('Verifying license&hellip;', 'gravityformsdynamicscrm'),
				'data-edd_action' => 'activate_license',
				'class' => 'button-primary',
			),
			array(
				'name'  => 'edd-deactivate',
				'value' => __('Deactivate License', 'gravityformsdynamicscrm'),
				'data-pending_text' => __('Deactivating license&hellip;', 'gravityformsdynamicscrm'),
				'data-edd_action' => 'deactivate_license',
				'class' => ( empty( $status ) ? 'button-primary hide' : 'button-primary' ),
			),
			array(
				'name'  => 'edd-check',
				'value' => __('Check License', 'gravityformsdynamicscrm'),
				'data-pending_text' => __('Verifying license&hellip;', 'gravityformsdynamicscrm'),
				'title' => 'Check the license before saving it',
				'data-edd_action' => 'check_license',
				'class' => 'button-secondary',
			),
		);

		$class = 'button gfdcrm-edd-action';

		$class .= ( !empty( $key ) && $status !== 'valid' ) ? '' : ' hide';

		$submit = '<div class="gfdcrm-button-wrapper">';
		foreach ( $fields as $field ) {
			$field['type'] = 'button';
			$field['class'] = isset( $field['class'] ) ? $field['class'] . ' '. $class : $class;
			$field['style'] = 'margin-left: 10px;';
			$submit .= $this->FeedAddOn->settings_submit( $field, $echo );
		}
		$submit .= '</div>';

		return $submit;
	}

	/**
	 * Include the EDD plugin updater class, if not exists
	 * @since 1.7.4
	 * @return void
	 */
	private function setup_edd() {

		if( !class_exists('EDD_SL_Plugin_Updater') ) {
			require_once( GF_DYNAMICS_CRM_DIR . 'includes/lib/EDD_SL_Plugin_Updater.php');
		}

		// setup the updater
		$this->EDD_SL_Plugin_Updater = new EDD_SL_Plugin_Updater(
			self::url,
			GF_DYNAMICS_CRM_FILE,
			$this->_get_edd_settings()
		);

	}

	/**
	 * Generate the array of settings passed to the EDD license call
	 *
	 * @since 1.7.4
	 *
	 * @param string $action The action to send to edd, such as `check_license`
	 * @param string $license The license key to have passed to EDD
	 *
	 * @return array
	 */
	function _get_edd_settings( $action = '', $license = '' ) {

		// retrieve our license key from the DB
		$settings = $this->FeedAddOn->get_plugin_settings();
		$license_key = empty( $license ) ? trim( $settings[ 'license_key' ] ) : $license;

		$settings = array(
			'version'   => self::version,
			'license'   => $license_key,
			'item_name' => self::name,
			'author'    => self::author,
			'url'       => home_url(),
		);

		if ( !empty( $action ) ) {
			$settings['edd_action'] = esc_attr( $action );
		}

		$settings = array_map( 'urlencode', $settings );

		return $settings;
	}

	/**
	 * Perform the call
	 * @return array|WP_Error
	 */
	private function _license_get_remote_response( $data, $license = '' ) {

		$api_params = $this->_get_edd_settings( $data['edd_action'], $license );

		$url = add_query_arg( $api_params, self::url );

		$response = wp_remote_get( $url, array(
			'timeout'   => 15,
			'sslverify' => false,
		));

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// Not JSON
		if ( empty( $license_data ) ) {

			delete_transient( 'gfdcrm' . esc_attr( $data['field_id'] ) . '_valid' );

			// Change status
			return array();
		}

		// Store the license key inside the data array
		$license_data->license_key = $license;

		return $license_data;
	}

	/**
	 * Generate the status message displayed in the license field
	 *
	 * @since 1.7.4
	 * @param $license_data
	 *
	 * @return string
	 */
	function get_license_message( $license_data ) {

		if( empty( $license_data ) ) {
			$class = 'hide';
			$message = '';
		} else {

			if( ! empty( $license_data->error ) ) {
				$class = 'error';
				$string_key = $license_data->error;
			} else {
				$class = $license_data->license;
				$string_key = $license_data->license;
			}

			//$message = sprintf( '<p><strong>%s: %s</strong></p>', $this->strings('status'), $this->strings( $string_key, $license_data ) );
			$message = '<span class="gf_tooltip tooltip tooltip_gfdcrm_license_message" title="' . sprintf( '<h6>%s</h6>%s', $this->strings('status'), $this->strings( $string_key, $license_data ) ) . '" style="display:inline-block;position:relative;top:4px;font-size:14px;">
	            <i class="fa fa-check-circle gf_keystatus_valid gf_valid"></i>
	        </span>';
		}

		return $this->generate_license_box( $message, $class );
	}

	/**
	 * Generate the status message box HTML based on the current status
	 *
	 * @since 1.7.4
	 * @param $message
	 * @param string $class
	 *
	 * @return string
	 */
	private function generate_license_box( $message, $class = '' ) {

		//$template = '<div id="gfdcrm-edd-status" class="gfdcrm-edd-message inline %s">%s</div>';
		$template = '<div id="gfdcrm-edd-status" class="gfdcrm-edd-message inline %s">%s</div>';

		$output = sprintf( $template, esc_attr( $class ), $message );

		return $output;
	}

	/**
	 * Perform the call to EDD based on the AJAX call or passed data
	 *
	 * @since 1.7.4
	 *
	 * @param array $array {
	 * @type string $license The license key
	 * @type string $edd_action The EDD action to perform, like `check_license`
	 * @type string $field_id The ID of the field to check
	 * @type boolean $update Whether to update plugin settings. Prevent updating the data by setting an `update` key to false
	 * @type string $format If `object`, return the object of the license data. Else, return the JSON-encoded object
	 * }
	 *
	 * @return mixed|string|void
	 */
	public function license_call( $array = array() ) {

		$is_ajax = ( defined('DOING_AJAX') && DOING_AJAX );
		$data = empty( $array ) ? $_POST['data'] : $array;

		if ( $is_ajax && empty( $data['license'] ) ) {
			die( - 1 );
		}

		$license = esc_attr( rgget( 'license', $data ) );
		$license_data = $this->_license_get_remote_response( $data, $license );

		// Empty is returned when there's an error.
		if ( empty( $license_data ) ) {
			if ( $is_ajax ) {
				exit( json_encode( array() ) );
			} else { // Non-ajax call
				return json_encode( array() );
			}
		}

		$license_data->message = $this->get_license_message( $license_data );

		$json = json_encode( $license_data );

		$update_license = ( !isset( $data['update'] ) || !empty( $data['update'] ) );

		$is_check_action_button = ( 'check_license' === $data['edd_action'] && defined('DOING_AJAX') && DOING_AJAX );

		// Failed is the response from trying to de-activate a license and it didn't work.
		// This likely happened because people entered in a different key and clicked "Deactivate",
		// meaning to deactivate the original key. We don't want to save this response, since it is
		// most likely a mistake.
		if ( $license_data->license !== 'failed' && !$is_check_action_button && $update_license ) {

			if( !empty( $data['field_id'] ) ) {
				set_transient( 'gfdcrm_' . esc_attr( $data['field_id'] ) . '_valid', $license_data, DAY_IN_SECONDS );
			}

			$this->license_call_update_settings( $license_data, $data );

		}

		if ( $is_ajax ) {
			exit( $json );
		} else { // Non-ajax call
			return ( rgget('format', $data ) === 'object' ) ? $license_data : $json;
		}
	}

	/**
	 * Update the license after fetching it
	 * @param object $license_data
	 * @return void
	 */
	private function license_call_update_settings( $license_data, $data ) {

		// Update option with passed data license
		$settings = $this->FeedAddOn->get_plugin_settings();

        $settings['license_key'] = $license_data->license_key = trim( $data['license'] );
		$settings['license_key_status'] = $license_data->license;
		$settings['license_key_response'] = (array)$license_data;

		//update_option( 'gravityformsaddon_gravityformsdynamicscrm_settings', $settings );
		$this->FeedAddOn->update_plugin_settings( $settings );
	}

	/**
	 * URL to direct license renewal, or if license key is not set, then just the account page
	 * @since 1.0
	 * @param  object|null $license_data Object with license data
	 * @return string Renewal or account URL
	 */
	private function get_license_renewal_url( $license_data ) {
		$renew_license_url = ( ! empty( $license_data ) && !empty( $license_data->license_key ) ) ? sprintf( 'https://www.saintsystems.com/checkout/?download_id=536&edd_license_key=%s', $license_data->license_key ) : 'https://www.saintsystems.com/my-account/';
		return $renew_license_url;
	}

	/**
	 * Override the text used in the Redux Framework EDD field extension
	 * @param  array|null $status Status to get. If empty, get all strings.
	 * @param  object|null $license_data Object with license data
	 * @return array          Modified array of content
	 */
	public function strings( $status = null, $license_data = null ) {

		$strings = array(
			'status' => esc_html__( 'Status', 'gravityformsdynamicscrm' ),
			'error' => esc_html__( 'There was an error processing the request.', 'gravityformsdynamicscrm' ),
			'failed'  => esc_html__( 'Could not deactivate the license. The license key you attempted to deactivate may not be active or valid.', 'gravityformsdynamicscrm' ),
			'site_inactive' => esc_html__('The license key is valid, but it has not been activated for this site.', 'gravityformsdynamicscrm' ),
			'no_activations_left' => esc_html__( 'Invalid: this license has reached its activation limit.', 'gravityformsdynamicscrm') . ' ' . sprintf( esc_html__( 'You can manage license activations %son your Saint Systems account page%s.', 'gravityformsdynamicscrm' ), '<a href="https://www.saintsystems.com/my-account/#licenses">', '</a>' ),
			'deactivated' => esc_html__( 'The license has been deactivated.', 'gravityformsdynamicscrm' ),
			'valid' => esc_html__( 'The license key is valid and active.', 'gravityformsdynamicscrm' ),
			'invalid' => esc_html__( 'The license key entered is invalid.', 'gravityformsdynamicscrm' ),
			'missing' => esc_html__( 'The license key was not defined.', 'gravityformsdynamicscrm' ),
			'revoked' => esc_html__( 'This license key has been revoked.', 'gravityformsdynamicscrm' ),
			'expired' => sprintf( esc_html__( 'This license key has expired. %sRenew your license on the Saint Systems website%s to receive updates and support.', 'gravityformsdynamicscrm' ), '<a href="'. esc_url( $this->get_license_renewal_url( $license_data ) ) .'">', '</a>' ),

			'verifying_license' => esc_html__( 'Verifying license&hellip;', 'gravityformsdynamicscrm' ),
			'activate_license' => esc_html__( 'Activate License', 'gravityformsdynamicscrm' ),
			'deactivate_license' => esc_html__( 'Deactivate License', 'gravityformsdynamicscrm' ),
			'check_license' => esc_html__( 'Verify License', 'gravityformsdynamicscrm' ),
		);

		if ( empty( $status ) ) {
			return $strings;
		}

		if ( isset( $strings[ $status ] ) ) {
			return $strings[ $status ];
		}

		return null;
	}

	public function validate_license_key( $value, $field ) {

		// No license? No status.
		if ( empty( $value ) ) {
			return NULL;
		}

		$settings = $this->FeedAddOn->get_plugin_settings();

		$response = $this->license_call( array(
			'license' => $settings[ 'license_key' ],
			'edd_action' => 'check_license',
			'field_id' => $field['name'],
		) );

		$response = is_string( $response ) ? json_decode( $response, true ) : $response;

		switch ( $response['license'] ) {
			case 'valid':
				$return = true;
				break;
			case 'invalid':
				$return = false;
				break;
			default:
				$return = false;
		}

		return $return;
	}

} //end class GFDCRM_License_Handler