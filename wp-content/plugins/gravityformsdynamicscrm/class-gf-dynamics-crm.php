<?php

GFForms::include_feed_addon_framework();

class GFDynamicsCRM extends GFFeedAddOn {

	protected $_version = GFDCRM_Plugin::version;
	protected $_min_gravityforms_version = GF_DYNAMICS_CRM_MIN_GF_VERSION;
	protected $_slug = 'gravityformsdynamicscrm';
	protected $_path = 'gravityformsdynamicscrm/gravityforms-dynamics-crm.php';
	protected $_full_path = __FILE__;
	protected $_url = 'http://www.gravityforms.com';
	protected $_title = 'Dynamics CRM Add-On';
	protected $_short_title = 'Dynamics CRM';
	protected $_enable_rg_autoupgrade = true;
	protected $api = null;
	private static $_instance = null;
	private $auth_client = null;

	private $dynamics_crm_url = 'https://<tenant_id>.crm.dynamics.com/';
	private $dynamics_crm_url_valid = null;

	/**
	* @var GFDRCM_License_Handler Process license validation
	*/
	private $License_Handler;

	/* Members plugin integration */
	protected $_capabilities = array( 'gravityforms_dynamicscrm', 'gravityforms_dynamicscrm_uninstall' );

	/* Members plugin integration */
	protected $_capabilities_settings_page = 'gravityforms_dynamicscrm';
	protected $_capabilities_form_settings = 'gravityforms_dynamicscrm';
	protected $_capabilities_uninstall = 'gravityforms_dynamicscrm_uninstall';

	/**
	* Get instance of this class.
	* 
	* @access public
	* @static
	* @return $_instance
	*/
	public static function get_instance() {

		if ( self::$_instance == null ) {
			self::$_instance = new GFDynamicsCRM();
		}

		return self::$_instance;

	}

	public function init() {

		parent::init();
        
		//loading translations
		load_plugin_textdomain( 'gravityformsdynamicscrm', FALSE, '/gravityforms-dynamics-crm/languages' );
	}

	/**
	 * Run actions when initializing admin
	 *
	 * Triggers the license key notice
	 *
	 * @return void
	 */
	public function init_admin() {

	    $this->_load_license_handler();

		parent::init_admin();

		$this->ensure_upgrade();
	}

	/**
	 * Load license handler in admin-ajax.php
	 */
	public function init_ajax() {
		$this->_load_license_handler();
	}

	/**
	 * Enqueue plugin settings scripts.
	 *
	 * @since 1.3.2
	 * 
	 * @access public
	 * @return array $scripts
	 */
	public function scripts() {
		
		$scripts = array(
			array(
				'handle'    => 'gfdcrm-admin',
				'src'       => $this->get_base_url() . '/assets/js/admin.js',
				'version'   => $this->_version,
				'callback'  => array( $this, 'localize_scripts' ),
				'deps'      => array( 'jquery' ),
				'in_footer' => true,
				'enqueue' 	=> array(
	                  			array(
	                      				'admin_page' => array( 'plugin_settings' ),
	                      				'tab'        => 'gravityformsdynamicscrm'
	                  			)
	              			)
			),
		);

		return array_merge( parent::scripts(), $scripts );
		
	}

	/**
	 * Enqueue plugin settings scripts.
	 *
	 * @since 1.3.2
	 * 
	 * @access public
	 * @return array $scripts
	 */
	public function localize_scripts() {
		$this->License_Handler->localize_scripts();
	}

	/**
	 * Check if Gravity Forms Dynamics CRM Add-On plugin settings script should be enqueued.
	 *
	 * @since 1.3.2
	 * 
	 * @access public
	 * @return bool
	 */
	public function maybe_enqueue_plugin_settings_script() {
		return rgget( 'page' ) == 'gf_settings' && rgget( 'subview' ) == 'gravityformsdynamicscrm';
	}

	/**
	 * Enqueue plugin settings css.
	 *
	 * @since 1.3.2
	 * 
	 * @access public
	 * @return array $styles
	 */
	public function styles() {
		
		$styles = array(
			array(
				'handle'  => 'gfdcrm-admin-css',
				'src'     => $this->get_base_url() . '/assets/css/admin.css',
				'version' => $this->_version,
				'deps'	  => array(
					'gaddon_form_settings_css'
				),
				'enqueue' => array(
                  			array(
                      				'admin_page' => array( 'plugin_settings' ),
                      				'tab'        => 'gravityformsdynamicscrm'
                  			)
              			),
			),
		);

		return array_merge( parent::styles(), $styles );
		
	}

	/**
	 * Make sure the license handler is available
	 */
	private function _load_license_handler() {

		if ( !empty( $this->License_Handler ) ) {
			return;
		}

		require_once( GF_DYNAMICS_CRM_DIR . 'includes/class-gfdrcm-license-handler.php');

		$this->License_Handler = GFDCRM_License_Handler::get_instance( $this );
	}

	public function is_valid_dynamics_crm_url( $force = false ) {

    	if ( $this->dynamics_crm_url_valid != null && !$force ) {
    		return $this->dynamics_crm_url_valid;
    	}

    	/* Get the plugin settings */
		$settings = $this->get_plugin_settings();
		$crm_url = rgar( $settings, 'dynamics_crm_url' );

		if ( empty( $crm_url ) ) return false;

		if ( !GFDynamicsCRM::startsWith( $crm_url, 'https://' ) || !GFDynamicsCRM::endsWith( $crm_url, '/' ) ) {
			$this->dynamics_crm_url_valid = false;
			return $this->dynamics_crm_url_valid;
		}

		$api_url = $crm_url . '/XRMServices/2011/Organization.svc/web?SdkClientVersion=6.1.0.533';

		$response = wp_remote_get( $api_url );

		if ( is_wp_error( $response ) ) {
			$this->dynamics_crm_url_valid = false;
			return $this->dynamics_crm_url_valid;
		} else if ( $response['response']['code'] != 401 && !isset( $response['headers']['www-authenticate'] ) && !GFDynamicsCRM::startsWith( $response['headers']['www-authenticate'], 'Bearer authorization_uri=' ) ) {
			$this->dynamics_crm_url_valid = false;
			return $this->dynamics_crm_url_valid;
		}
		$this->dynamics_crm_url_valid = true;
		return $this->dynamics_crm_url_valid;
    }

    /**
     * [is_on_premises description]
     * @return [type] [description]
     */
    public function is_on_premises( ) {

    	/* Get the plugin settings */
		$settings = $this->get_plugin_settings();
		$dynamics_crm_url = rgar( $settings, 'dynamics_crm_url' );

        // If on-premises Dynamics, the CRM URL won't end with ".dynamics.com/"
        return !GFDynamicsCRM::endsWith( $dynamics_crm_url, '.dynamics.com/' );

    } //end function is_on_premises

	/**
     * Setup plugin settings fields.
     * 
     * @access public
     * @return array
     */
	public function plugin_settings_fields() {

        $description  = '<p>';
        $description .= __( 'Use this connector with Dynamics CRM. Use Gravity Forms to collect customer information and automatically add them to your Dynamics CRM account.', 'gravityformsdynamicscrm' );
        $description .= '</p>';

		return array(
			array(
				'title'       => '',
				'description' => $description,
				'fields'      => array(
                    array(
                        'name'              => 'credentials',
                        'type'              => 'hidden',
                    ),
                    array(
                        'name'                => 'license_key',
                        'label'               => __( 'License Key', 'gravityformsdynamicscrm' ),
                        'type'                => 'license_key_field_type',
                        'class'               => 'medium',
                        'tooltip'             => '<h6>' . __( 'License Key', 'gravityformsdynamicscrm' ) . '</h6>' . __( 'Your Gravity Forms Dynamics CRM Add-On license key is required for the plugin to work and to enable automatic updates and support.', 'gravityformsdynamicscrm' ),
                        'tooltip_class'       => 'tooltipclass',
                    ),
                    array(
                        'name'                => 'license_key_status',
                        'type'                => 'hidden',
                        'default_value'		  => '',
                    ),
                    array(
                        'name'                => 'license_key_response',
                        'type'                => 'hidden',
                        'default_value'		  => '',
                    ),
					array(
						'name'                => 'dynamics_crm_url',
						'label'               => __( 'Dynamics CRM URL', 'gravityformsdynamicscrm' ),
						'type'                => 'dynamics_crm_url_field_type',
						'class'               => 'medium',
                        'tooltip'             => '<h6>' . __( 'Dynamics CRM URL', 'gravityformsdynamicscrm' ) . '</h6>' . __( 'Enter your Dynamics CRM URL</br> (e.g. https://<strong>[tenant_id]</strong>.crm.dynamics.com/)', 'gravityformsdynamicscrm' ),
                        'tooltip_class'       => 'tooltipclass',
                        'feedback_callback'   => array( $this, 'is_valid_crm_url' ),
					),
					array(
                        'name'              => 'username',
                        'label'             => __( 'Username', 'gravityformsdynamicscrm' ),
                        'type'              => 'username_field_type',
                        'hidden'			=> !$this->is_on_premises(),
                    ),
                    array(
                        'name'              => 'password',
                        'label'             => __( 'Password', 'gravityformsdynamicscrm' ),
                        'type'              => 'password_field_type',
                        'hidden'			=> !$this->is_on_premises(),
                    ),
					array(
	                    'label' 			=> '',
	                    'type'  			=> 'connect_to_dynamics_field_type',
	                    'name'  			=> 'connect_to_dynamics',
	                    'hidden'			=> $this->is_on_premises(),
	                ),
				)
			),
		);
	}

	/**
	 * Make this available to oauth class
	 * @return [type] [description]
	 */
	public function get_plugin_settings() {
		return parent::get_plugin_settings();
	}

	/**
	 * License Key Field
	 * @return [type] [description]
	 */
	public function settings_license_key_field_type() {
	
		$settings = $this->get_plugin_settings();
		$license_key = rgar( $settings, 'license_key' );
		$license_key_status = rgar( $settings, 'license_key_status' );
		$license_key_response = rgar( $settings, 'license_key_response' );

		if ( !empty( $license_key ) ) {

			$response = $this->License_Handler->license_call( array(
				'license' => $license_key,
				'edd_action' => 'check_license',
				'field_id' => 'edd-check',
				'update' => true
			) );

			$settings = $this->get_plugin_settings();
			$license_key = rgar( $settings, 'license_key' );
			$license_key_status = rgar( $settings, 'license_key_status' );
		}

		$license_key_field = '<input type="text" name="_gaddon_setting_license_key" id="license_key" class="regular-text gaddon-setting gaddon-text edd-license-key" value="' . $license_key . '" />';

		$license_key_field .= $this->License_Handler->settings_edd_license_activation( false );

		echo apply_filters( 'gfdcrm_license_key_field', $license_key_field );
	}

	/**
	 * Dynamics CRM URL Field
	 * @return dynamics field type
	 */
	public function settings_dynamics_crm_url_field_type() {
	
		$settings = $this->get_plugin_settings();
		$dynamics_crm_url = rgar( $settings, 'dynamics_crm_url' );
		$dynamics_crm_url_valid = $this->is_valid_dynamics_crm_url();

		$dynamics_crm_url_field = '<input type="text" name="_gaddon_setting_dynamics_crm_url" id="dynamics_crm_url" class="regular-text gaddon-setting gaddon-text dynamics-crm-url" value="' . $dynamics_crm_url . '" />';

		if ( $dynamics_crm_url_valid ) {
			$dynamics_crm_url_field .= '<span class="gf_tooltip tooltip" title="' . esc_html__( '<h6>Valid URL</h6>Your Dynamics CRM URL is valid.', 'gravityformsdynamicscrm' ) . '" style="display:inline-block;position:relative;top:4px;font-size:14px;">
	            <i class="fa fa-check-circle gf_keystatus_valid gf_valid"></i>
	        </span>';
		} else {
			$dynamics_crm_url_field .= '<span class="gf_tooltip tooltip" title="' . esc_html__( '<h6>Invalid URL</h6>Your Dynamics CRM URL is invalid. It should begin with \'https://\' and end with a \'/\'', 'gravityformsdynamicscrm' ) . '" style="display:inline-block;position:relative;top:3px;font-size:14px;">
	            <i class="fa fa-exclamation-circle icon-exclamation-sign gf_invalid"></i>
	        </span>';
		}

		echo apply_filters( 'gfdcrm_dynamics_crm_url_field', $dynamics_crm_url_field );
	}

	/**
	 * Password Field
	 * @return [type] [description]
	 */
	public function settings_username_field_type() {
	
		$settings = $this->get_plugin_settings();
		$username = rgar( $settings, 'username' );
		$credentials = rgar( $settings, 'credentials' );
		if ( empty( $username ) && !empty( $credentials ) ) {
			$credentials_array = explode( ':', base64_decode( $credentials ) );
			$username = $credentials_array[0];
		}

		$username_field = '<input type="text" name="_gaddon_setting_username" id="username" class="gaddon-setting gaddon-text username" value="' . $username . '" />';
		// if ( !empty( $username ) ) {
		// 	$username_field .= '<span id="username-status" class="gf_tooltip tooltip" title="' . esc_html__( '<h6>Validating...</h6>One moment while we validate your credentials.', 'gravityformsdynamicscrm' ) . '" style="display:inline-block;position:relative;top:3px;font-size:14px;">
	 //            <i class="fa fa-refresh gf_keystatus_valid gf_valid"></i>
	 //        </span>';
	 //    }

		echo apply_filters( 'gfdcrm_username_field', $username_field );

	}

	/**
	 * Password Field
	 * @return [type] [description]
	 */
	public function settings_password_field_type() {
	
		$settings = $this->get_plugin_settings();
		$password = rgar( $settings, 'password' );
		$credentials = rgar( $settings, 'credentials' );
		if ( empty( $password ) && !empty( $credentials ) ) {
			$credentials_array = explode( ':', base64_decode( $credentials ) );
			$password = $credentials_array[1];
		}

		$password_field = '<input type="password" name="_gaddon_setting_password" id="password" class="gaddon-setting gaddon-text password" value="' . $password . '" />';
		// if ( !empty( $password ) ) {
		// 	$password_field .= '<span id="password-status" class="gf_tooltip tooltip" title="' . esc_html__( '<h6>Validating...</h6>One moment while we validate your credentials.', 'gravityformsdynamicscrm' ) . '" style="display:inline-block;position:relative;top:3px;font-size:14px;">
	 //            <i class="fa fa-refresh gf_keystatus_valid gf_valid"></i>
	 //        </span>';
		// }

		echo apply_filters( 'gfdcrm_password_field', $password_field );

	}

	/**
	 * Custom field type to initiate OAuth authorization
	 * @return [type] [description]
	 */
	public function settings_connect_to_dynamics_field_type() {

        $connect_to_dynamics_field = '';

        $settings = $this->get_plugin_settings();

        if ( isset( $settings['dynamics_crm_url'] ) ) $dynamics_crm_url = $settings['dynamics_crm_url']; else $dynamics_crm_url = '';
		?>
		<div>
		<?php
			if ( empty( $dynamics_crm_url ) ):
			?>
				Click <strong>Update Settings</strong> below to save your settings and connect to Dynamics.
	    	<?php else:
				if ( $this->has_valid_credentials() ):
					$connect_to_dynamics_field .= '<span class="gf_tooltip tooltip" title="<h6>' . esc_html__( 'Connected to Dynamics CRM', 'gravityformsdynamicscrm') . '</h6>' . sprintf( esc_html__( '%s', 'gravityformsdynamicscrm' ), '<a href=\''. $dynamics_crm_url . '\' target=\'_blank\'>' . $dynamics_crm_url . '</a>' ) . '" style="display:inline-block;position:relative;top:4px;font-size:14px;">
		            <i class="fa fa-check-circle gf_keystatus_valid gf_valid"></i>
		        </span>';
			?>
				<a id="gform-disconnect-from-dynamics" class="button button-primary gaddon-setting gaddon-submit" href="#">Disconnect from Dynamics</a>
				<img id="gfdcrm-loading" src="<?php echo GF_DYNAMICS_CRM_PLUGIN_URL.'/images/loading.gif'; ?>" style="display:none" />
			<?php else: ?>
	    		<a id="gform-connect-to-dynamics" class="button button-primary gaddon-setting gaddon-submit" href="<?php echo home_url( 'gfdcrm/authorize' ); ?>">Connect to Dynamics</a>
	    		<img id="gfdcrm-loading" src="<?php echo GF_DYNAMICS_CRM_PLUGIN_URL.'/images/loading.gif'; ?>" style="display:none" />
			<?php endif; ?>
			<?php echo $connect_to_dynamics_field ?>
	   
	    <?php endif;?>
	    </div>
	    <?php
	}

	public function settings_lookup_field_map( $field, $echo = true ) {

		$default_value = rgar( $field, 'value' ) ? rgar( $field, 'value' ) : rgar( $field, 'default_value' );
		$value         = $this->get_setting( $field['name'], $default_value );

		$field_value = current($value);

		$html = '';
		$value_field = $key_field = $custom_key_field = $field;
		$form = $this->get_current_form();

		/* Setup key field drop down */
		$key_field['choices']  = ( isset( $field['field_map'] ) ) ? $field['field_map'] : null;
		$key_field['name']    .= '_key';
		$key_field['class']    = 'key key_{i}';
		$key_field['style']    = 'width:200px;';

		/* Setup custom key text field */
		$custom_key_field['name']  .= '_custom_key_{i}';
		$custom_key_field['class']  = 'custom_key custom_key_{i}';
		$custom_key_field['style']  = 'width:200px;max-width:90%;';
		$custom_key_field['value']  = '{custom_key}';

		/* Setup value drop down */
		$value_field['name']  .= '_custom_value';
		$value_field['class']  = 'value value_{i}';
		
		/* Remove unneeded values */
		unset( $field['field_map'] );
		unset( $value_field['field_map'] );
		unset( $key_field['field_map'] );
		unset( $custom_key_field['field_map'] );

		//add on errors set when validation fails
		if ( $this->field_failed_validation( $field ) ) {
			$html .= $this->get_error_icon( $field );
		}

		/* Build key cell based on available field map choices */
		if ( empty( $key_field['choices'] ) ) {
			
			/* Set key field value to "gf_custom" so custom key is used. */
			$key_field['value'] = 'gf_custom';
			
			/* Build HTML string */
			$key_field_html = '<td>' .
                $this->settings_hidden( $key_field, false ) . '
            </td>';			
			
		} else {

			/* Ensure field map array has a custom key option. */
			$has_gf_custom = false;
			foreach ( $key_field['choices'] as $choice ) {
				if ( rgar( $choice, 'name' ) == 'gf_custom' || rgar( $choice, 'value' ) == 'gf_custom' ) {
					$has_gf_custom = true;
				}
				if ( rgar( $choice, 'choices' ) ) {
					foreach ( $choice['choices'] as $subchoice ) {
						if ( rgar( $subchoice, 'name' ) == 'gf_custom' || rgar( $subchoice, 'value' ) == 'gf_custom' ) {
							$has_gf_custom = true;
						}
					}					
				}
			}
			
			/* Build HTML string */
			$key_field_html = '<th>' .
                $this->settings_select( $key_field, false ) . '
            </th>';
			
		}

		$html .= '
            <table class="settings-field-map-table" cellspacing="0" cellpadding="0">
            	<thead>
					<tr>
						<th>' . $this->lookup_field_map_title() . '</th>
						<th>' . esc_html__( 'Value', 'gravityformsdynamicscrm' ) . '</th>
					</tr>
				</thead>
                <tbody class="repeater">
	                <tr>
	                    '. $key_field_html .'
	                    <td>' .
			                $this->settings_lookup_field_map_select( $value_field, $form['id'], $field_value ) . '
						</td>
						<td>
							{buttons}
						</td>
	                </tr>
                </tbody>
            </table>';

		$html .= $this->settings_hidden( $field, false );

		$limit = empty( $field['limit'] ) ? 0 : $field['limit'];

		$html .= "
			<script type=\"text/javascript\">
			
				var lookupFieldMap". esc_attr( $field['name'] ) ." = new gfieldmap({
					
					'baseURL':      '". GFCommon::get_base_url() ."',
					'fieldId':      '". esc_attr( $field['name'] ) ."',
					'fieldName':    '". $field['name'] ."',
					'keyFieldName': '". $key_field['name'] ."',
					'limit':        '". $limit . "'
										
				});
			
			</script>";

		if ( $echo ) {
			echo $html;
		}

		return $html;

	}

	public function settings_lookup_field_map_select( $field, $form_id, $field_value ) {

		$field_type          = ( isset( $field['field_type'] ) ) ? $field['field_type'] : null;
		$exclude_field_types = ( isset( $field['exclude_field_types'] ) ? $field['exclude_field_types'] : null );

		$field['choices'] = $this->get_lookup_field_map_choices( $field_value['key'] );

		if ( empty( $field['choices'] ) || ( count( $field['choices'] ) == 1 && rgblank( $field['choices'][0]['value'] ) ) ) {
			
			if ( ( ! is_array( $field_type ) && ! rgblank( $field_type ) ) || ( is_array( $field_type ) && count( $field_type ) == 1 ) ) {
			
				$type = is_array( $field_type ) ? $field_type[0] : $field_type;
				$type = ucfirst( GF_Fields::get( $type )->get_form_editor_field_title() );
				
				return sprintf( __( 'Please add a %s field to your form.', 'gravityforms' ), $type );
				
			}

		}
		
		return $this->settings_select( $field, false );

	}

	public static function get_lookup_field_map_choices( $lookup_field_name = null ) {

		// if $lookup_field_name is null, we'll provide one option prompting
		// the user to select a lookup field from the left column
		$lookup_field_name = 'LeadSourceCode';

		$choices = array(
            array(
                'label' => esc_html__( '[Select a Value]', 'gravityformsdynamicscrm' ),
                'value' => ''
            )
        );

		//if ( false === ( $choices = get_transient( 'gfdcrm_lookup_' . $lookup_field_name ) ) ) {

		$gfdcrm = self::get_instance();

	        $option_set_values = $gfdcrm->api->get_option_set_values( 'lead', $lookup_field_name );

	        if ( ! empty( $option_set_values ) ) {

	            foreach( $option_set_values as $option_set_value ) {
	                $choices[] = array(
	                    'label' => $option_set_value['SourceValue'],
	                    'value' => $option_set_value['TargetValue'],
	                );
	            }

	        }

	        //set_transient( 'gfdcrm_lookup_' . $lookup_field_name, $choices, 5 * MINUTE_IN_SECONDS );

	    //}

		// $choices = array(
		// 	array( 
		// 		'label' => 'Advertisement',
		// 		'value' => '1'
		// 	),
		// );

 		return $choices;
	}

	/**
	 * Return whether we have a valid set of oauth credentials
	 * @return boolean [description]
	 */
	public function has_valid_credentials() {
		$credentials = get_option( 'gravityformsdynamicscrm_credentials' );

		if ( !is_array( $credentials ) ) return false;

		if ( !isset( $credentials['access_token'] ) 
			&& !isset( $credentials['expires_on'] ) 
			&& !isset( $credentials['refresh_token'] ) 
			&& !isset( $credentials['resource'] )
			&& !isset( $credentials['token_type'] )
			&& !isset( $credentials['scope'] )
			&& !isset( $credentials['id_token'] ) ) {
			return false;
		}
		return true;
	}

	/**
     * Setup feed edit page.
     * 
     * @access public
     * @return array
     */
	public function feed_edit_page( $form, $feed_id ) {

		// ensures valid credentials were entered in the settings page
		if ( $this->has_valid_credentials() == false ) {
			?>
			<div><?php echo sprintf( __( 'We are unable to connect to Dynamics CRM. Please reauthorize your connection to Dynamics CRM on the %sSettings Page%s.', 'gravityformsdynamicscrm' ),
					'<a href="' . $this->get_plugin_settings_url() . '">', '</a>' ); ?>
			</div>
			<?php
			return;
		}

		echo '<script type="text/javascript">var form = ' . GFCommon::json_encode( $form ) . ';</script>';

		parent::feed_edit_page( $form, $feed_id );
	}

	/**
	 * Fork of maybe_save_plugin_settings to get auth token..
	 * 
	 * @access public
	 * @return void
	 */
	public function maybe_save_plugin_settings() {

		if ( $this->is_save_postback() ) {

			// store a copy of the previous settings for cases where action whould only happen if value has changed
			$this->set_previous_settings( $this->get_plugin_settings() );

			$settings = $this->get_posted_settings();
			
			if ( $this->has_license_key_changed() ) {
				
				$license_key = rgget( 'license_key', $settings );
				$license_status = rgget( 'license_key_status', $settings );

				if ( !empty( $license_key ) ) {
					
					// $response = $this->License_Handler->license_call( array(
					// 	'license' => $license_key,
					// 	'edd_action' => 'activate_license',
					// 	'field_id' => 'edd-activate',
					// 	'update' => true
					// ) );

				} else {

					// Safeguard in case user cleared out license key and forgot to deactivate it first
					// We'll go ahead and deactivate it for them

					$previous_settings = $this->get_previous_settings();
					$license_key = rgget( 'license_key', $previous_settings );

					$response = $this->License_Handler->license_call( array(
						'license' => $license_key,
						'edd_action' => 'deactivate_license',
						'field_id' => 'edd-deactivate',
						'update' => true
					) );

					$settings['license_key'] = '';
					$settings['license_key_status'] = '';
					$settings['license_key_response'] = '';

				}
				
			}
			
			$sections = $this->plugin_settings_fields();
			$is_valid = $this->validate_settings( $sections, $settings );

			if ( $is_valid ) {

				$settings['credentials'] = base64_encode($settings['username'].':'.$settings['password']);
				unset($settings['username']);
				unset($settings['password']);
				
				$settings = $this->filter_settings( $sections, $settings );
				$this->update_plugin_settings( $settings );
				GFCommon::add_message( $this->get_save_success_message( $sections ) );
				
			} else {
				
				GFCommon::add_error_message( $this->get_save_error_message( $sections ) );
			}
			
		}

	}

	public function update_plugin_settings( $settings ) {
		parent::update_plugin_settings( $settings );
	}
	
	/**
	 * Check if the plugin settings have changed.
	 * 
	 * @access public
	 * @return bool
	 */
	public function have_plugin_settings_changed() {
		
		/* Get previous and new settings. */
		$previous_settings = $this->get_previous_settings();
		$new_settings      = $this->get_posted_settings();
		
		/* If the license key has changed, return true. */
		if ( $previous_settings['license_key'] !== $new_settings['license_key'] ) {
			
			return true;
			
		}

		/* If the dynamics crm url changed, return true. */
		if ( $previous_settings['dynamics_crm_url'] !== $new_settings['dynamics_crm_url'] ) {
			
			return true;
			
		}
		
		return false;
		
	}

	/**
	 * Check if the plugin license_key changed.
	 * 
	 * @access public
	 * @return bool
	 */
	public function has_license_key_changed() {
		
		/* Get previous and new settings. */
		$previous_settings = $this->get_previous_settings();
		$new_settings      = $this->get_posted_settings();
		
		/* If the license key has changed, return true. */
		if ( $previous_settings['license_key'] !== $new_settings['license_key'] ) {
			
			return true;
			
		}
		
		return false;
		
	}

    /**
     * Setup fields for feed settings.
     * 
     * @access public
     * @return array
     */
	public function feed_settings_fields() {

        $feed = ( $this->get_posted_settings() ) ? $this->get_posted_settings() : $this->get_current_feed();

        /* Build base fields array. */
        $base_fields = array(
            'title'  => '',
            'fields' => array(
                array(
                    'name'           => 'feedName',
                    'label'          => esc_html__( 'Feed Name', 'gravityformsdynamicscrm' ),
                    'type'           => 'text',
                    'required'       => true,
                    'default_value'  => $this->get_default_feed_name(),
                    'tooltip'        => '<h6>'. esc_html__( 'Name', 'gravityformsdynamicscrm' ) .'</h6>' . esc_html__( 'Enter a feed name to uniquely identify this setup.', 'gravityformsdynamicscrm' )
                ),
                array(
                    'name'           => 'action',
                    'label'          => esc_html__( 'Action', 'gravityformsdynamicscrm' ),
                    'required'       => true,
                    'type'           => 'select',
                    'onchange'       => "jQuery(this).parents('form').submit();",
                    'tooltip'        => '<h6>'. esc_html__( 'Action', 'gravityformsdynamicscrm' ) .'</h6>' . esc_html__( 'Choose what will happen when this feed is processed.', 'gravityformsdynamicscrm' ),
                    'choices'        => array(
                        array(
                            'label' => esc_html__( 'Select an Action', 'gravityformsdynamicscrm' ),
                            'value' => ''
                        ),
                        array(
                            'label' => esc_html__( 'Create / Update Contact', 'gravityformsdynamicscrm' ),
                            'value' => 'contact'
                        ),
                        array(
                            'label' => esc_html__( 'Create / Update Lead', 'gravityformsdynamicscrm' ),
                            'value' => 'lead'
                        ),                      
                    )
                )
            )
        );

        /* Build contact fields array. */
        $contact_fields = array(
            'title'      => esc_html__( 'Contact Details', 'gravityformsdynamicscrm' ),
            'dependency' => array( 'field' => 'action', 'values' => ( 'contact' ) ),
            'fields'     => array(
                array(
                    'name'       => 'contactStandardFields',
                    'label'      => esc_html__( 'Map Fields', 'gravityformsdynamicscrm' ),
                    'type'       => 'field_map',
                    'field_map'  => $this->get_field_map_for_module( 'contact' ),
                    'tooltip'    => '<h6>'. esc_html__( 'Map Fields', 'gravityformsdynamicscrm' ) .'</h6>' . esc_html__( 'Select which Dynamics CRM fields (left) pair with their respective Gravity Form fields (right).', 'gravityformsdynamicscrm' )
                ),
                array(
                    'name'       => 'contactCustomFields',
                    'label'      => '',
                    'type'       => 'dynamic_field_map',
                    'field_map'  => $this->get_field_map_for_module( 'contact', 'dynamic' ),
                ),
                // array(
                //     'name'       => 'contactLookupFields',
                //     'label'      => __( 'Lookup Fields', 'gravityformsdynamicscrm' ),
                //     'type'       => 'lookup_field_map',
                //     'field_map'  => $this->get_field_map_for_module( 'contact', 'lookup' ),
                //     'tooltip'   => '<h6>' . __( 'Lookup Fields', 'gravityformsdynamicscrm' ) . '</h6>' . __( 'Select any Dynamics CRM Lookup Fields and the values you would like set for this feed.', 'gravityformsdynamicscrm' ),
                // ),
                array(
                    'name'       => 'contactOwner',
                    'label'      => esc_html__( 'Contact Owner', 'gravityformsdynamicscrm' ),
                    'type'       => 'select',
                    'choices'    => $this->get_users_for_feed_setting(),
                    'tooltip'   => '<h6>' . __( 'Contact Owner', 'gravityformsdynamicscrm' ) . '</h6>' . __( 'Select the user you would like to assign new contacts to. If not provided, contact owner defaults to the authenticated user.', 'gravityformsdynamicscrm' ),
                ),
                array(
                    'name'       => 'contactDescription',
                    'type'       => 'textarea',
                    'class'      => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
                    'label'      => esc_html__( 'Description', 'gravityformsdynamicscrm' ),
                ),
            )
        );

        $contact_fields['fields'][] = array(
            'name'       => 'options',
            'label'      => esc_html__( 'Options', 'gravityformsdynamicscrm' ),
            'type'       => 'checkbox',
            'onclick'    => "jQuery(this).parents('form').submit();",
            'choices'    => array(
                // array(
                //     'name'          => 'contactApprovalMode',
                //     'label'         => esc_html__( 'Approval Mode', 'gravityformsdynamicscrm' ),
                // ),
                // array(
                //     'name'          => 'contactWorkflowMode',
                //     'label'         => esc_html__( 'Workflow Mode', 'gravityformsdynamicscrm' ),
                // ),
                // array(
                //     'name'          => 'contactEmailOptOut',
                //     'label'         => esc_html__( 'Email Opt Out', 'gravityformsdynamicscrm' ),
                // ),
                array(
                    'name'          => 'contactDuplicateAllowed',
                    'label'         => esc_html__( 'Allow duplicate contacts', 'gravityformsdynamicscrm' ),
                    'tooltip'       => esc_html__( 'If duplicate contacts are allowed, you will not be able to update contacts if they already exist.', 'gravityformsdynamicscrm' )
                ),
                array(
                    'name'          => 'contactUpdate',
                    'label'         => esc_html__( 'Update Contact if contact already exists for email address', 'gravityformsdynamicscrm' ),
                ),
            )
        );

        /* Build lead fields array. */
        $lead_fields = array(
            'title'      => esc_html__( 'Lead Details', 'gravityformsdynamicscrm' ),
            'dependency' => array( 'field' => 'action', 'values' => ( 'lead' ) ),
            'fields'     => array(
                array(
                    'label'      => __( 'Topic', 'gravityformsdynamicscrm' ),
                    'name'       => 'leadTopic',
                    'type'       => 'text',
                    'class'      => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
                    'required'   => true,
                ),
                array(
                    'name'       => 'leadStandardFields',
                    'label'      => esc_html__( 'Map Fields', 'gravityformsdynamicscrm' ),
                    'type'       => 'field_map',
                    'field_map'  => $this->get_field_map_for_module( 'lead' ),
                    'tooltip'    => '<h6>'. esc_html__( 'Map Fields', 'gravityformsdynamicscrm' ) .'</h6>' . esc_html__( 'Select which Dynamics CRM fields (left) pair with their respective Gravity Form fields (right).', 'gravityformsdynamicscrm' )
                ),
                array(
                    'name'       => 'leadCustomFields',
                    'label'      => '',
                    'type'       => 'dynamic_field_map',
                    'field_map'  => $this->get_field_map_for_module( 'lead', 'dynamic' ),
                ),
                // array(
                //     'name'       => 'leadLookupFields',
                //     'label'      => __( 'Lookup Fields', 'gravityformsdynamicscrm' ),
                //     'type'       => 'lookup_field_map',
                //     'field_map'  => $this->get_field_map_for_module( 'lead', 'lookup' ),
                //     'tooltip'   => '<h6>' . __( 'Lookup Fields', 'gravityformsdynamicscrm' ) . '</h6>' . __( 'Select any Dynamics CRM Lookup Fields and the values you would like set for this feed.', 'gravityformsdynamicscrm' ),
                // ),
                array(
                    'name'       => 'leadOwner',
                    'label'      => esc_html__( 'Lead Owner', 'gravityformsdynamicscrm' ),
                    'type'       => 'select',
                    'choices'    => $this->get_users_for_feed_setting(),
                    'tooltip'   => '<h6>' . __( 'Lead Owner', 'gravityformsdynamicscrm' ) . '</h6>' . __( 'Select the user you would like to assign new leads to. If not provided, lead owner defaults to the authenticated user.', 'gravityformsdynamicscrm' ),
                ),
                // TODO: Populate these with values from PickListMappingSet endpoint
                // array(
                //     'name'       => 'leadRating',
                //     'label'      => esc_html__( 'Lead Rating', 'gravityformsdynamicscrm' ),
                //     'type'       => 'select',
                //     'choices'    => $this->get_module_field_choices( 'Leads', 'Rating' )
                // ),
                // array(
                //     'name'       => 'leadSource',
                //     'label'      => esc_html__( 'Lead Source', 'gravityformsdynamicscrm' ),
                //     'type'       => 'select',
                //     'choices'    => $this->get_module_field_choices( 'Leads', 'Lead Source' )
                // ),
                // array(
                //     'name'       => 'leadStatus',
                //     'label'      => esc_html__( 'Lead Status', 'gravityformsdynamicscrm' ),
                //     'type'       => 'select',
                //     'choices'    => $this->get_module_field_choices( 'Leads', 'Lead Status' )
                // ),
                array(
                    'name'       => 'leadDescription',
                    'type'       => 'textarea',
                    'class'      => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
                    'label'      => esc_html__( 'Description', 'gravityformshelpscout' ),
                ),
            )
        );
        
        $lead_fields['fields'][] = array(
            'name'       => 'options',
            'label'      => esc_html__( 'Options', 'gravityformsdynamicscrm' ),
            'type'       => 'checkbox',
            'onclick'    => "jQuery(this).parents('form').submit();",
            'choices'    => array(
                // array(
                //     'name'       => 'leadApprovalMode',
                //     'label'      => esc_html__( 'Approval Mode', 'gravityformsdynamicscrm' ),
                // ),
                // array(
                //     'name'       => 'leadWorkflowMode',
                //     'label'      => esc_html__( 'Workflow Mode', 'gravityformsdynamicscrm' ),
                // ),
                // array(
                //     'name'       => 'leadDoNotAllowBulkEmail',
                //     'label'      => esc_html__( 'Do not allow Bulk Emails', 'gravityformsdynamicscrm' ),
                //     'tooltip'    => esc_html__( 'Select whether the lead accepts bulk email sent through marketing campaigns or quick campaigns. If checked, the lead can be added to marketing lists, but will be excluded from the email.', 'gravityformsdynamicscrm' )
                // ),
                array(
                    'name'       => 'leadDuplicateAllowed',
                    'label'      => esc_html__( 'Allow duplicate leads', 'gravityformsdynamicscrm' ),
                    'tooltip'    => esc_html__( 'If duplicate leads are allowed, you will not be able to update leads if they already exist.', 'gravityformsdynamicscrm' ),
                ),
                array(
                    'name'       => 'leadUpdate',
                    'label'      => esc_html__( 'Update Lead if lead already exists for email address', 'gravityformsdynamicscrm' ),
                    'tooltip'    => esc_html__( 'If checked, only the mapped fields will be updated for the lead if a lead is found with a matching email address.', 'gravityformsdynamicscrm' ),
                ),
            )
        );

        /* Build conditional logic field array. */
        $conditional_fields = array(
            'title'      => esc_html__( 'Feed Conditional Logic', 'gravityformsdynamicscrm' ),
            'dependency' => array( $this, 'show_conditional_sections' ),
            'fields'     => array(
                array(
                    'name'           => 'feedCondition',
                    'type'           => 'feed_condition',
                    'label'          => esc_html__( 'Conditional Logic', 'gravityformsdynamicscrm' ),
                    'checkbox_label' => esc_html__( 'Enable', 'gravityformsdynamicscrm' ),
                    'instructions'   => esc_html__( 'Export to Dynamics CRM if', 'gravityformsdynamicscrm' ),
                    'tooltip'        => '<h6>' . esc_html__( 'Conditional Logic', 'gravityformsdynamicscrm' ) . '</h6>' . esc_html__( 'When conditional logic is enabled, form submissions will only be exported to Dynamics CRM when the condition is met. When disabled, all form submissions will be posted.', 'gravityformsdynamicscrm' )
                ),
                
            )
        );
        
        return array( $base_fields, $contact_fields, $lead_fields, $conditional_fields );

	}

    public static function dump($d, $halt = 0) {
        print "<pre>" . print_r($d,true) . "</pre>";
        if ( $halt ) {
            die("Halted ...");
        }
    }

    /**
     * Get field map fields for a Dynamics CRM module.
     * 
     * @access public
     * @param string $module
     * @return array $field_map
     */
    public function get_field_map_for_module( $module, $field_map_type = 'standard' ) {
        
        $field_map = $this->get_module_fields( $module, $field_map_type );

        foreach ( $field_map as $key => &$field ) {
            
            $standard_test = in_array( rgar( $field, 'label' ), array( 'Email Address', 'First Name', 'Last Name' ) );
            $required_test = rgar( $field, 'required' );
            
            if ( $field_map_type === 'standard' ) {
                
                if ( $standard_test ) {
                    $field['required'] = true;
                }
                
                if ( ! $standard_test && ! $required_test ) {
                    unset( $field_map[$key] );
                }
                
            } else if ( $field_map_type === 'dynamic' ) {
                
                if ( $standard_test || $required_test ) {
                    unset( $field_map[$key] );
                }
                
            }
                        
        }
        
        return $field_map;
        
    }

    /***
	 * Renders the save button for settings pages
	 *
	 * @param array $field - Field array containing the configuration options of this field
	 * @param bool  $echo  = true - true to echo the output to the screen, false to simply return the contents as a string
	 *
	 * @return string The HTML
	 */
	public function settings_submit( $field, $echo = true ) {

		$field['type']  = ( isset($field['type']) && in_array( $field['type'], array('submit','reset','button') ) ) ? $field['type'] : 'submit';

		$attributes    = $this->get_field_attributes( $field );
		$default_value = rgar( $field, 'value' ) ? rgar( $field, 'value' ) : rgar( $field, 'default_value' );
		$value         = $this->get_setting( $field['name'], $default_value );


		$attributes['class'] = isset( $attributes['class'] ) ? esc_attr( $attributes['class'] ) : 'button-primary gfbutton';
		$name    = ( $field['name'] === 'gform-settings-save' ) ? $field['name'] : '_gaddon_setting_'.$field['name'];

		if ( empty( $value ) ) {
			$value = __( 'Update Settings', 'gravityview' );
		}

		$attributes = $this->get_field_attributes( $field );

		$html = '<input
                    type="' . $field['type'] . '"
                    name="' . esc_attr( $name ) . '"
                    value="' . $value . '" ' .
		        implode( ' ', $attributes ) .
		        ' />';

		if ( $echo ) {
			echo $html;
		}

		return $html;
	}

    /**
     * Get Dynamics Users for selection of Lead/Contact Owner
     * @return [type] [description]
     */
    public function get_users_for_feed_setting() {

        // TODO: Optionally get Teams for assignment from TeamSet endpoint
        $users = array(
            array(
                'label' => esc_html__( '-None-', 'gravityformsdynamicscrm' ),
                'value' => ''
            )
        );

        /* If API instance is not initialized, exit. */
        if ( ! $this->initialize_api() ) {
            
            $this->log_error( __METHOD__ . '(): Unable to get users because API is not initialized.' );
            return $users;
            
        }

        // cache Dynamics users for 15 minutes
        if ( false === ( $dynamics_users = get_transient( 'gfdcrm_users' ) ) ) {

	        $dynamics_users = $this->api->get_system_users();

	        set_transient( 'gfdcrm_users', $dynamics_users, 15 * MINUTE_IN_SECONDS );

	    }

        if ( ! empty( $dynamics_users ) ) {

            foreach( $dynamics_users as $user ) {
                $users[] = array(
                    'label' => $user['FullName'] . ' ('. $user['DomainName'] .')',
                    'value' => $user['SystemUserId'],
                );
            }

        }

        return $users;
    }

    /**
     * Get the Dynamics CRM tenant url.
     * @param  [type] $settings [description]
     * @return [type]           [description]
     */
    public function get_tenant_url( $settings ) {

        if ( $settings == null ) {
            $settings = $this->get_plugin_settings();
        }
        return str_replace( '<tenant_id>', $settings['tenant_id'], $this->dynamics_crm_url );

    } //end function get_tenant_url

    /**
     * Set feed creation control.
     * 
     * @access public
     * @return bool
     */
    public function can_create_feed() {
        
    	if ( $this->has_valid_credentials() ) {

	        return $this->initialize_api();

	    }
        return false;
    }

    /**
     * Setup columns for feed list table.
     * 
     * @access public
     * @return array
     */
	public function feed_list_columns() {
		return array(
			'feedName' => esc_html__( 'Name', 'gravityformsdynamicscrm' ),
            'action'   => esc_html__( 'Action', 'gravityformsdynamicscrm' )
		);
	}

    /**
     * Get value for action feed list column.
     * 
     * @access public
     * @param array $feed
     * @return string $action
     */
    public function get_column_value_action( $feed ) {
        
        if ( rgars( $feed, 'meta/action' ) == 'contact' ) {
            
            return esc_html__( 'Create a New Contact', 'gravityformsdynamicscrm' );
            
        } else if ( rgars( $feed, 'meta/action' ) == 'lead' ) {
            
            return esc_html__( 'Create a New Lead', 'gravityformsdynamicscrm' );
            
        }
        
    }

    /**
     * Custom dependency to show Feed Conditional Logic feed settings sections.
     * 
     * @access public
     * @return void
     */
    public function show_conditional_sections() {
        
        /* Get current feed. */
        $feed = $this->get_current_feed();
        
        /* Get posted settings. */
        $posted_settings = $this->get_posted_settings();
        
        /* Show if an action is chosen */
        return ( rgar( $posted_settings, 'action' ) !== '' || rgars( $feed, 'meta/action' ) !== '' );
            
    }

    /**
     * Get fields for a Dynamics CRM module.
     * 
     * @access public
     * @param string $module (default: null)
     * @return array $fields
     */
    public function get_module_fields( $module = null, $field_type = null ) {

    	$this->log_debug( __METHOD__ . '(): Attempting to retrieve entity metadata from Dynamics CRM.' );

    	try {

	    	/* If API instance is not initialized, exit. */
	        if ( ! $this->initialize_api() ) {
	        
	            $this->log_error( __METHOD__ . '(): Failed to set up the API.' );
	            return;
	            
	        }

	        if ( false === ( $fields = get_option( 'gravityformsdynamicscrm_module_fields' ) ) || is_null( $module ) ) {

		        /* Get fields for each module. */
		        $fields = array(
		            'contact' => $this->api->get_fields( 'contact', $field_type ),
		            'lead'    => $this->api->get_fields( 'lead', $field_type ),
		            'case' 	  => $this->api->get_fields( 'incident', $field_type ),
		        );

		        $this->log_debug( __METHOD__ . '(): Entity metadata retrieved from Dynamics CRM.' );

		        update_option( 'gravityformsdynamicscrm_module_fields', $fields );

		    } else {

		    	$this->log_debug( __METHOD__ . '(): Entity metadata cached and retrieved from WordPress.' );

			}
	        
	        return rgar( $fields, $module ) ? rgar( $fields, $module ) : $fields;

	    } catch (Exception $e) {
	    	$this->log_error( __METHOD__ . '(): Error retrieving entity metadata from Dynamics CRM:' . $e->getMessage() );
	    	throw $e;
	    }
        
    }

    /**
     * Get field from a Dynamics CRM module.
     * 
     * @access public
     * @param string $module
     * @param string $field_name
     * @return array $field
     */
    public function get_module_field( $module, $field_name ) {
        
        $module_fields = $this->get_module_fields( $module );
        
        return rgar( $module_fields, $field_name );
        
    }

    /**
     * Ensure upgrade to Add-On framework
     * @return [type] [description]
     */
	public function ensure_upgrade() {

		if ( get_option( 'upgrade' ) ){
			return false;
		}

		$feeds = $this->get_feeds();
		if ( empty( $feeds ) ) {
			//Force Add-On framework upgrade
			$this->upgrade( '2.0' );
		}

		update_option( 'upgrade', 1 );
	}


    /**
     * Process the Dynamics CRM feed.
     * 
     * @access public
     * @param array $feed
     * @param array $entry
     * @param array $form
     * @return void
     */
	public function process_feed( $feed, $entry, $form ) {

        $this->log_debug( __METHOD__ . '(): Processing feed.' );

		/* If API instance is not initialized, exit. */
        if ( ! $this->initialize_api() ) {
        
            $this->log_error( __METHOD__ . '(): Failed to set up the API.' );
            return;
            
        }

        /* Create contact or lead */
        if ( rgars( $feed, 'meta/action' ) === 'contact' ) {
            
            $contact_id = $this->create_contact( $feed, $entry, $form );
            
            // if ( ! rgblank( $contact_id ) ) {
                
            //     $this->upload_attachments( $contact_id, 'contact', $feed, $entry, $form );
                
            //     $this->create_task( $contact_id, 'Contacts', $feed, $entry, $form );
                
            // }

        } else if ( rgars( $feed, 'meta/action' ) === 'lead' ) {

            $lead_id = $this->create_lead( $feed, $entry, $form );
            
            // if ( ! rgblank( $lead_id ) ) {
                
            //     $this->upload_attachments( $lead_id, 'lead', $feed, $entry, $form );
                
            //     $this->create_task( $lead_id, 'Leads', $feed, $entry, $form );
            
            // }
            
        }

	}

    /**
     * Create a new lead from a feed.
     * 
     * @access public
     * @param array $feed
     * @param array $entry
     * @param array $form
     * @return int $lead_id
     */
    public function create_lead( $feed, $entry, $form ) {
        
        /* Create lead object. */
        $lead = array(
            'Subject'         => GFCommon::replace_variables( $feed['meta']['leadTopic'], $form, $entry, false, false, false, 'text' ),
            'Description'     => GFCommon::replace_variables( $feed['meta']['leadDescription'], $form, $entry, false, false, false, 'text' ),
            //'DoNotBulkEMail'  => rgars( $feed, 'meta/leadDoNotAllowBulkEmail' ) == '1' ? 'true' : 'false',
            //'Lead Source'   => rgars( $feed, 'meta/leadSource' ),
            //'Lead Status'   => rgars( $feed, 'meta/leadStatus' ),
            //'Rating'        => rgars( $feed, 'meta/leadRating' ),
            'OwnerId'         => $this->get_user_entity( rgars( $feed, 'meta/leadOwner' ) ),
        );

        $update = rgars( $feed, 'meta/leadUpdate' ) == '1' ? true : false;
        $allow_duplicates = rgars( $feed, 'meta/leadDuplicateAllowed' ) == 1 ? true : false;
        $duplicate_check_key = 'EMailAddress1';
        
        /* If duplicate leads are allowed, remove the duplicate check. */
        if ( $allow_duplicates ) {
            $update = false;
        }
            
        /* Add standard fields. */
        $standard_fields = $this->get_field_map_fields( $feed, 'leadStandardFields' );
        $custom_fields   = $this->get_dynamic_field_map_fields( $feed, 'leadCustomFields' );
        
        $mapped_fields = array_merge( $standard_fields, $custom_fields );

        $module_fields = $this->get_module_fields( 'lead' );
        
        foreach ( $mapped_fields as $field_name => $field_id ) {
            
            $field_value = $this->get_field_value( $form, $entry, $field_id );
            
            if ( rgblank( $field_value ) )
                continue;
            
            $current_field = current( array_filter( $module_fields, function( $field ) use( $field_name ) {
            	return $field['name'] == $field_name;
            } ) );

            if ( in_array( $current_field['type'], array( 'Picklist', 'Money' ) ) ) {
        		$lead[ $field_name ] = $this->get_attribute_value( $field_value );
            } else {
            	$lead[ $field_name ] = $field_value;
            }
            
        }
        
        /* Filter lead. */
        $lead = gf_apply_filters( 'gform_dynamicscrm_lead', $form['id'], $lead, $feed, $entry, $form );
        
        /* Remove OwnerId if not set. */
        if ( rgblank( $lead['OwnerId']['Id'] ) ) {
            unset( $lead['OwnerId'] );
        }
        
        $this->log_debug( __METHOD__ . '(): Creating lead: ' . print_r( $lead, true ) );

        try {

            if ( !$update && $allow_duplicates ) {

            	/* Insert lead record. */
                $lead_record = $this->api->create_lead( $lead );

            } else if ( !$update && !$allow_duplicates ) {

            	/* Insert lead record if it does not exist. */
                $lead_record = $this->api->create_lead_if_not_exists( $lead, $duplicate_check_key );

            } else {

            	/* Insert lead record. */
                $lead_record = $this->api->create_or_update_lead( $lead, $duplicate_check_key );

            }
        
            /* Get lead ID of new lead record. */
            $lead_id = $lead_record['LeadId'];

            $settings = $this->get_plugin_settings();
			$dynamics_crm_url = rgar( $settings, 'dynamics_crm_url' );
        
            /* Save lead ID to entry meta. */
            gform_update_meta( $entry['id'], 'dynamicscrm_entity_id', $lead_id );
            gform_update_meta( $entry['id'], 'dynamicscrm_entity_type', 'lead' );
            gform_update_meta( $entry['id'], 'dynamicscrm_entity_url', $dynamics_crm_url . 'main.aspx?etn=lead&id=' . $lead_id . '&pagetype=entityrecord' );
            
            /* Log that lead was created. */
            $this->log_debug( __METHOD__ . '(): Lead #' . $lead_id . ' created.' );
            
            return $lead_id;
        
        } catch ( Exception $e ) {
            
            $this->log_error( __METHOD__ . '(): Could not create Lead; ' . $e->getMessage() );
            
            $this->send_email_error( 'lead', $lead, $e->getMessage() );

            return null;
            
        }
        
    } //end function create_lead

    /**
     * Create a new contact from a feed.
     * 
     * @access public
     * @param array $feed
     * @param array $entry
     * @param array $form
     * @return int $contact_id
     */
    public function create_contact( $feed, $entry, $form ) {
        
        /* Create contact object. */
        $contact = array(
            'Description'     => GFCommon::replace_variables( $feed['meta']['contactDescription'], $form, $entry, false, false, false, 'text' ),
            //'DoNotBulkEMail'  => rgars( $feed, 'meta/leadDoNotAllowBulkEmail' ) == '1' ? 'true' : 'false',
            //'Lead Source'   => rgars( $feed, 'meta/leadSource' ),
            //'Lead Status'   => rgars( $feed, 'meta/leadStatus' ),
            //'Rating'        => rgars( $feed, 'meta/leadRating' ),
            'OwnerId'         => $this->get_user_entity( rgars( $feed, 'meta/contactOwner' ) ),
        );

        $update = rgars( $feed, 'meta/contactUpdate' ) == '1' ? true : false;
        $allow_duplicates = rgars( $feed, 'meta/contactDuplicateAllowed' ) == 1 ? true : false;
        $duplicate_check_key = 'EMailAddress1';
        
        /* If duplicate leads are allowed, remove the duplicate check. */
        if ( $allow_duplicates ) {
            $update = false;
        }
            
        /* Add standard fields. */
        $standard_fields = $this->get_field_map_fields( $feed, 'contactStandardFields' );
        $custom_fields   = $this->get_dynamic_field_map_fields( $feed, 'contactCustomFields' );
        
        $mapped_fields = array_merge( $standard_fields, $custom_fields );

        $module_fields = $this->get_module_fields( 'contact' );
        
        foreach ( $mapped_fields as $field_name => $field_id ) {
            
            $field_value = $this->get_field_value( $form, $entry, $field_id );
            
            if ( rgblank( $field_value ) )
                continue;

            $current_field = current( array_filter( $module_fields, function( $field ) use( $field_name ) {
            	return $field['name'] == $field_name;
            } ) );

            if ( in_array( $current_field['type'], array( 'Picklist', 'Money' ) ) ) {
        		$contact[ $field_name ] = $this->get_attribute_value( $field_value );
            } else {
            	$contact[ $field_name ] = $field_value;
            }
            
        }
        
        /* Filter contact. */
        $contact = gf_apply_filters( 'gform_dynamicscrm_contact', $form['id'], $contact, $feed, $entry, $form );
        
        /* Remove OwnerId if not set. */
        if ( rgblank( $contact['OwnerId']['Id'] ) ) {
            unset( $contact['OwnerId'] );
        }
        
        $this->log_debug( __METHOD__ . '(): Creating contact: ' . print_r( $contact, true ) );

        try {

            if ( !$update && $allow_duplicates ) {

            	/* Insert contact record. */
                $contact_record = $this->api->create_contact( $contact );

            } else if ( !$update && !$allow_duplicates ) {

            	/* Insert contact record if it does not exist. */
                $contact_record = $this->api->create_contact_if_not_exists( $contact, $duplicate_check_key );

            } else {

            	/* Insert contact record. */
                $contact_record = $this->api->create_or_update_contact( $contact, $duplicate_check_key );

            }
        
            /* Get contact ID of new lead record. */
            $contact_id = $contact_record['ContactId'];
        
            $settings = $this->get_plugin_settings();
			$dynamics_crm_url = rgar( $settings, 'dynamics_crm_url' );
        
            /* Save contact ID to entry meta. */
            gform_update_meta( $entry['id'], 'dynamicscrm_entity_id', $contact_id );
            gform_update_meta( $entry['id'], 'dynamicscrm_entity_type', 'contact' );
            gform_update_meta( $entry['id'], 'dynamicscrm_entity_url', $dynamics_crm_url . 'main.aspx?etn=contact&id=' . $contact_id . '&pagetype=entityrecord' );
            
            /* Log that contact was created. */
            $this->log_debug( __METHOD__ . '(): Contact #' . $contact_id . ' created.' );
            
            return $contact_id;
        
        } catch ( Exception $e ) {
            
            $this->log_error( __METHOD__ . '(): Could not create Contact; ' . $e->getMessage() );
            
            $this->send_email_error( 'contact', $contact, $e->getMessage );

            return null;
            
        }
        
    } //end function create_contact

    /**
     * Create a new case from a feed.
     * 
     * @access public
     * @param array $feed
     * @param array $entry
     * @param array $form
     * @return guid $case_id
     */
    public function create_case( $feed, $entry, $form ) {
        
        /* Create contact object. */
        $case = array(
            'Description'     => GFCommon::replace_variables( $feed['meta']['caseDescription'], $form, $entry, false, false, false, 'text' ),
            //'DoNotBulkEMail'  => rgars( $feed, 'meta/leadDoNotAllowBulkEmail' ) == '1' ? 'true' : 'false',
            //'Lead Source'   => rgars( $feed, 'meta/leadSource' ),
            //'Lead Status'   => rgars( $feed, 'meta/leadStatus' ),
            //'Rating'        => rgars( $feed, 'meta/leadRating' ),
            'OwnerId'         => $this->get_user_entity( rgars( $feed, 'meta/caseOwner' ) ),
        );

        $update = rgars( $feed, 'meta/caseUpdate' ) == '1' ? true : false;
        $allow_duplicates = rgars( $feed, 'meta/caseDuplicateAllowed' ) == 1 ? true : false;
        $duplicate_check_key = 'EMailAddress1';
        
        /* If duplicate leads are allowed, remove the duplicate check. */
        if ( $allow_duplicates ) {
            $update = false;
        }
            
        /* Add standard fields. */
        $standard_fields = $this->get_field_map_fields( $feed, 'caseStandardFields' );
        $custom_fields   = $this->get_dynamic_field_map_fields( $feed, 'caseCustomFields' );
        
        $mapped_fields = array_merge( $standard_fields, $custom_fields );

        $module_fields = $this->get_module_fields( 'case' );
        
        foreach ( $mapped_fields as $field_name => $field_id ) {
            
            $field_value = $this->get_field_value( $form, $entry, $field_id );
            
            if ( rgblank( $field_value ) )
                continue;

            $current_field = current( array_filter( $module_fields, function( $field ) use( $field_name ) {
            	return $field['name'] == $field_name;
            } ) );

            if ( in_array( $current_field['type'], array( 'Picklist', 'Money' ) ) ) {
        		$case[ $field_name ] = $this->get_attribute_value( $field_value );
            } else {
            	$case[ $field_name ] = $field_value;
            }
            
        }
        
        /* Filter contact. */
        $case = gf_apply_filters( 'gform_dynamicscrm_case', $form['id'], $case, $feed, $entry, $form );
        
        /* Remove OwnerId if not set. */
        if ( rgblank( $case['OwnerId']['Id'] ) ) {
            unset( $case['OwnerId'] );
        }
        
        $this->log_debug( __METHOD__ . '(): Creating case: ' . print_r( $case, true ) );

        try {

            if ( !$update && $allow_duplicates ) {

            	/* Insert case record. */
                $case_record = $this->api->create_case( $case );

            } else if ( !$update && !$allow_duplicates ) {

            	/* Insert case record if it does not exist. */
                $case_record = $this->api->create_case_if_not_exists( $case, $duplicate_check_key );

            } else {

            	/* Insert case record. */
                $case_record = $this->api->create_or_update_case( $case, $duplicate_check_key );

            }
        
            /* Get case ID of new lead record. */
            $case_id = $contact_record['IncidentId'];
        
            $settings = $this->get_plugin_settings();
			$dynamics_crm_url = rgar( $settings, 'dynamics_crm_url' );
        
            /* Save contact ID to entry meta. */
            gform_update_meta( $entry['id'], 'dynamicscrm_entity_id', $case_id );
            gform_update_meta( $entry['id'], 'dynamicscrm_entity_type', 'case' );
            gform_update_meta( $entry['id'], 'dynamicscrm_entity_url', $dynamics_crm_url . 'main.aspx?etn=incident&id=' . $case_id . '&pagetype=entityrecord' );
            
            /* Log that contact was created. */
            $this->log_debug( __METHOD__ . '(): Case #' . $case_id . ' created.' );
            
            return $case_id;
        
        } catch ( Exception $e ) {
            
            $this->log_error( __METHOD__ . '(): Could not create Case; ' . $e->getMessage() );
            
            $this->send_email_error( 'case', $case, $e->getMessage );

            return null;
            
        }
        
    } //end function create_case

    /**
     * Return an array that represents an Dynamics attribute value
     * @param  [type] $value [description]
     * @since 1.1.0
     * @return [type]        [description]
     */
    public function get_attribute_value( $value ) {
        return array(
            'Value' => $value,
        );
    }

    /**
     * Return an array that represents an OData SystemUser entity
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function get_user_entity( $user_id ) {

    	if ( empty( $user_id ) ) {

    		$user_id = $this->api->get_current_user();
    	}

        return array(
            'Id' => $user_id,
            'LogicalName' => 'systemuser',
        );
    }

    /**
     * Sent email to the site admin if there is an error creating the entity
     * @param  [string] $entity_type  	[lead|contact|case]
     * @param  [type] 	$entity  		[description]
     * @param  [type] 	$error 			[description]
     * @return [void]
     */
    private function send_email_error( $entity_type, $entity, $error ) {
        // Sends email if it does not create a lead

        $subject = sprintf( __( 'Error creating %s:<br/>', 'gravityformsdynamicscrm' ), $entity_type );
        $message = sprintf( __( '<p>There was a problem creating the %s in Dynamics CRM.</p><p>Error:<br/>', 'gravityformsdynamicscrm' ), $entity_type ) . print_r( $error, true ) . __( '</p><br/><p><strong>Gravity Forms Dynamics CRM</strong>', 'gravityformsdynamicscrm' );

        wp_mail( get_bloginfo( 'admin_email'), $subject, $message );
    }

    private static function startsWith( $haystack, $needle ) {
    	// search backwards starting from haystack length characters from the end
    	return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
	}

	private static function endsWith( $haystack, $needle ) {
	    // search forward starting from end minus needle length characters
	    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
	}

    /**
     * Return whether we have an OAuth access token
     * @return boolean [description]
     */
    public function is_valid_crm_url( ) {
    	/* Get the plugin settings */
		$settings = $this->get_plugin_settings();
		$crm_url = rgar( $settings, 'dynamics_crm_url' );

		if ( !self::startsWith( $crm_url, 'https://' ) ) {// || !self::endsWith( $crm_url, '/' ) ) {
			return false;
		}
		return true;
    }

    /**
     * Return whether we have an OAuth access token
     * @return boolean [description]
     */
    private function is_valid_key( ) {

    	if ( !$this->has_required_settings() ) return false;

        $client = $this->get_auth_client();

        if ( $client->hasAccessToken() ) {
            return true;
        }

        echo '<div id="message" class="error below-h2">
                <p><strong>'.__('Please click "Connect to Dynamics" below to authorize the connection to Dynamics CRM.','gravityformsdynamicscrm' ).'</strong></p></div>';
        return false;

    } //end function is_valid_key

    /**
     * Get instance of this class.
     * 
     * @access private
     * @static
     * @return $auth_client
     */
	private function get_auth_client() {
        
        if ( $this->auth_client == null ) {
			// default to oauth client for CRM Online (Azure AD) and ADFS 3.X (Server 2012 R2)
	        $auth_client = 'GF_Dynamics_CRM_OAuth_Client';

	        /* Include the API library. */
	        if ( $this->is_on_premises() ) {
	        	// if on-premises we use SOAP ws-trust to get access token from ADFS
	        	$auth_client = 'GF_Dynamics_CRM_ADFS_Auth_Client';
	        }
	        if ( ! class_exists( $auth_client ) ) {
	        	$filename = 'class-' . str_replace( '_', '-', strtolower( $auth_client ) ) . '.php';
                
	            require_once(GF_DYNAMICS_CRM_DIR . 'includes/' . $filename);
	        }

			$this->auth_client = new $auth_client();
		}

		return $this->auth_client;
	}

    /**
     * Return whether the add-on required settings are populated
     * @return boolean [description]
     */
    function has_required_settings() {
    	$settings = $this->get_plugin_settings();

    	if ( rgar( $settings, 'license_key' ) && rgar( $settings, 'dynamics_crm_url' ) ) {
    		return true;
    	}
    	return false;
    }

    /**
     * Override the field map title
     * @return [string] [description]
     */
    public function field_map_title() {
		return sprintf( '<strong>%s</strong> <a href="#" onclick="return false;" class="gf_tooltip tooltip " title="<h6>%s</h6>%s"><i class="fa fa-question-circle"></i></a>', esc_html__( 'Dynamics CRM Field', 'gravityformsdynamicscrm' ), esc_html__( 'Dynamics CRM Fields', 'gravityformsdynamicscrm'), esc_html__( 'The columns on the left are the available fields in Microsoft Dynamics CRM. Map them to the corresponding fields on the right from your Gravity Form.', 'gravityformsdynamicscrm' ) );
	}

	/**
     * Override the field map title
     * @return [string] [description]
     */
    public function lookup_field_map_title() {
		return sprintf( '<strong>%s</strong> <a href="#" onclick="return false;" class="gf_tooltip tooltip " title="<h6>%s</h6>%s"><i class="fa fa-question-circle"></i></a>', esc_html__( 'Dynamics CRM Field', 'gravityformsdynamicscrm' ), esc_html__( 'Dynamics CRM Fields', 'gravityformsdynamicscrm'), esc_html__( 'The columns on the left are the available lookup fields in Microsoft Dynamics CRM. Select them and the desired values on the right.', 'gravityformsdynamicscrm' ) );
	}

    /**
     * Initialized Dynamics CRM API if authenticated.
     * 
     * @access public
     * @return bool
     */
    private function initialize_api() {

        if ( ! is_null( $this->api ) ) {
            return true;
        }

        if ( ! class_exists( 'Dynamics_CRM_API' ) ) {
            require_once 'includes/class-dynamics-crm-api.php';
        }

        /* Get the plugin settings */
        $settings = $this->get_plugin_settings();
            
        $this->log_debug( __METHOD__ . "(): Validating API credentials." );

        try {
            $client = $this->get_auth_client();

            $client->acquire_token();

            if ( !$client->hasAccessToken() ) {
                $this->log_debug( __METHOD__ . '(): API credentials not set. No access token on auth client.' );

                return null;
            }

            $dynamicscrm = new Dynamics_CRM_API( $client->get_tenant_url(), $client->access_token );

            /* Run API test. */
            //$dynamicscrm->who_am_i();
            
            /* Log that test passed. */
            $this->log_debug( __METHOD__ . '(): API credentials are valid.' );
            
            /* Assign Dynamics CRM object to the class. */
            $this->api = $dynamicscrm;
            
            return true;

        } catch ( Exception $e ) {

            /* Log that test failed. */
            $this->log_error( __METHOD__ . '(): API credentials are invalid: '. $e->getMessage() );         

            return false;

        }

    } //end function initialize_api

} //end class GFDynamicsCRM