<?php

class GF_Dynamics_CRM_ADFS_Auth_Client {

    const ACCESS_TOKEN_BEARER   = 1;

    /**
     * Access Token
     *
     * @var string
     */
    public $access_token = null;

    /**
     * Access Token Type
     *
     * @var int
     */
    protected $access_token_type = self::ACCESS_TOKEN_BEARER;

    protected $dynamics_crm_url; // typically in the format: https://<tenant_id>.crm.dynamics.com/
    protected $license_key;
    protected $adfs_url;
    protected $organization_service_url;
    protected $usernamemixed;
    protected $username;
    protected $password;
    const TOKEN_CREDENTIALS_OPTION = 'gravityformsdynamicscrm_credentials';

    public function __construct() {
        $settings = get_option( 'gravityformsaddon_gravityformsdynamicscrm_settings' );
        if ( isset( $settings['dynamics_crm_url'] ) ) $this->dynamics_crm_url = $settings['dynamics_crm_url']; else $this->dynamics_crm_url = '';
        if ( isset( $settings['license_key'] ) ) $this->license_key = $settings['license_key']; else $this->license_key = '';
        $credentials = $settings['credentials'];
        $this->username = $this->get_username_from_credentials( $credentials );
        $this->password = $this->get_password_from_credentials( $credentials );
        $this->adfs_url = $this->get_adfs_url( $this->dynamics_crm_url );
        $this->organization_service_url = $this->dynamics_crm_url . 'XRMServices/2011/Organization.svc';
        $this->usernamemixed = $this->adfs_url . '/13/usernamemixed';
        if ( $credentials = get_option( 'gravityformsdynamicscrm_credentials' ) ) {
            $access_token = rgar( $credentials, 'access_token' );
            $token_type = rgar( $credentials, 'token_type' );
            $this->setAccessToken( $access_token );
            $this->setAccessTokenType( $token_type );
        }
    }

    public function get_tenant_url( ) {

        return trailingslashit( $this->dynamics_crm_url );

    } //end function get_tenant_url

    public function acquire_token() {
        return $this->acquire_saml_bearer_token();
    }

    /**
     * setToken
     *
     * @param string $token Set the access token
     * @return void
     */
    public function setAccessToken($token) {
        $this->access_token = $token;
    }

    /**
     * Set the access token type
     *
     * @param int $type Access token type (ACCESS_TOKEN_BEARER, ACCESS_TOKEN_MAC, ACCESS_TOKEN_URI)
     * @return void
     */
    public function setAccessTokenType($type) {
        $this->access_token_type = $type;
    }

    /**
     * Check if there is an access token present
     *
     * @return bool Whether the access token is present
     */
    public function hasAccessToken() {
        return !!$this->access_token;
    }

    private function acquire_saml_bearer_token() {

        $uuid = $this->generate_guid();

        try {
        
            $xml = "<s:Envelope xmlns:s=\"http://www.w3.org/2003/05/soap-envelope\" xmlns:a=\"http://www.w3.org/2005/08/addressing\">";
            $xml .= "<s:Header>";
            $xml .= "<a:Action s:mustUnderstand=\"1\">http://docs.oasis-open.org/ws-sx/ws-trust/200512/RST/Issue</a:Action>";
            $xml .= "<a:MessageID>urn:uuid:" . $uuid . "</a:MessageID>";
            $xml .= "<a:ReplyTo>";
            $xml .= "<a:Address>http://www.w3.org/2005/08/addressing/anonymous</a:Address>";
            $xml .= "</a:ReplyTo>";
            $xml .= "<Security s:mustUnderstand=\"1\" xmlns:u=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd\" xmlns=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\">";
            $xml .= "<UsernameToken u:Id=\"" . $uuid . "\">";
            $xml .= "<Username>" . $this->username . "</Username>";
            $xml .= "<Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">" . htmlspecialchars( $this->password ) . "</Password>";
            $xml .= "</UsernameToken>";
            $xml .= "</Security>";
            $xml .= "<a:To s:mustUnderstand=\"1\">" . $this->usernamemixed . "</a:To>";
            $xml .= "</s:Header>";
            $xml .= "<s:Body>";
            $xml .= "<trust:RequestSecurityToken xmlns:trust=\"http://docs.oasis-open.org/ws-sx/ws-trust/200512\">";
            $xml .= "<wsp:AppliesTo xmlns:wsp=\"http://schemas.xmlsoap.org/ws/2004/09/policy\">";
            $xml .= "<a:EndpointReference>";
            $xml .= "<a:Address>" . $this->dynamics_crm_url . "</a:Address>";
            $xml .= "</a:EndpointReference>";
            $xml .= "</wsp:AppliesTo>";
            $xml .= "<trust:KeyType>http://docs.oasis-open.org/ws-sx/ws-trust/200512/Bearer</trust:KeyType>";
            $xml .= "<trust:RequestType>http://docs.oasis-open.org/ws-sx/ws-trust/200512/Issue</trust:RequestType>";
            $xml .= "<trust:TokenType>urn:oasis:names:tc:SAML:2.0:assertion</trust:TokenType>";
            $xml .= "</trust:RequestSecurityToken>";
            $xml .= "</s:Body>";
            $xml .= "</s:Envelope>";
    
            $this->log_debug( __METHOD__ . '(): SAML Bearer Token Request XML: ' . $xml );
    
            $this->log_debug( __METHOD__ . '(): Attempting to acquire SAML token from: ' . $this->usernamemixed );

            // $request_args = array(
            //     'method'        => 'POST',
            //     'body'          => $xml,
            //     'sslverify'     => false,
            //     'timeout'       => 60,
            //     //'redirection'   => 5,
            //     'httpversion'   => '1.1',
            //     'headers' => array(
            //         'POST'              => parse_url ( $this->usernamemixed, PHP_URL_PATH ) . ' HTTP/1.1',
            //         'Content-Type'      => 'application/soap+xml; charset=UTF-8',
            //         'Connection'        => 'Keep-Alive',
            //         'Content-length'    => strlen( $xml ),
            //         'Host'              => parse_url ( $this->adfs_url, PHP_URL_HOST ),
            //     ),
            //     //'user-agent'    => 'Gravity-Forms-Dynamics-CRM-Add-On/'.GFDCRM_Plugin::version,
            // );
    
            // $response = wp_remote_post( $this->usernamemixed, $request_args );

            // $this->log_debug( __METHOD__ . '(): response:' . print_r( $response, true ) );

            $headers = array (
                "POST " . parse_url ( $this->usernamemixed, PHP_URL_PATH ) . " HTTP/1.1",
                "Host: " . parse_url ( $this->adfs_url, PHP_URL_HOST ),
                'Connection: Keep-Alive',
                "Content-type: application/soap+xml; charset=UTF-8",
                "Content-length: " . strlen ( $xml ) 
            );

            $ch = curl_init ();
            curl_setopt ( $ch, CURLOPT_URL, $this->usernamemixed );
            curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt ( $ch, CURLOPT_TIMEOUT, 60 );
            curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt ( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
            curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
            curl_setopt ( $ch, CURLOPT_POST, 1 );
            curl_setopt ( $ch, CURLOPT_POSTFIELDS, $xml );
            
            $response = curl_exec ( $ch );
            curl_close ( $ch );

            $this->log_debug( __METHOD__ . '(): curl response:' . $response );
    
            if ( is_wp_error( $response ) ) {
                $error_message = $response->get_error_message();
                $this->log_error( __METHOD__ . '(): Error acquiring ADFS auth token:' . print_r( $request_args, true ) . '. Error: ' . $error_message );
                die( 'Request failed. '. $error_message );
            }
    
            $response_body = $response;
    
            $responsedom = new DomDocument();
            $responsedom->loadXML( $response_body );
            
            $saml_token = $responsedom->getElementsbyTagName( 'EncryptedAssertion' )->item(0);
    
            $token = $responsedom->saveXML( $saml_token );

            $this->log_debug( __METHOD__ . '(): SAML token acquired: ' . $token );
    
            $credentials = array(
                'token_type' => 'Bearer',
                'access_token' => $token
            );

            $this->log_debug( __METHOD__ . '(): Saving SAML token in credentials in option(' . self::TOKEN_CREDENTIALS_OPTION . '): ' . print_r( $credentials, true ) );
    
            update_option( self::TOKEN_CREDENTIALS_OPTION, $credentials );
    
            $this->setAccessToken($token);
    
            return $credentials;
            
        } catch ( Exception $e ) {
            $this->log_error( __METHOD__ . '(): Error acquiring ADFS auth token:' . $e->getMessage() );
        }

    }

    /**
     * Gets the name of the ADFS server CRM uses for authentication.
     * 
     * @return String The ADFS server url.
     * @param String $url
     *          The Url of the CRM On Premise (IFD) organization (https://org.domain.com).
     */
    public function get_adfs_url( $dynamics_crm_url ) {
        
        $discovery_url = $dynamics_crm_url . 'XrmServices/2011/Organization.svc?wsdl=wsdl0';
        
        $this->log_debug( __METHOD__ . '(): Attempting to retrieve ADFS URL from $discovery_url: ' . $discovery_url );

        $args = array(
            'method'        => 'GET',
            'timeout'       => '60',
            'sslverify'     => false,
            'httpversion'   => '1.1',
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
            ),
            'user-agent'    => 'Gravity-Forms-Dynamics-CRM-Add-On/'.GFDCRM_Plugin::version,
        );

        $response = wp_remote_request( $discovery_url, $args );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            $this->log_error( __METHOD__ . '(): Error making api request for:' . print_r( $request_args, true ) . '. Error: ' . $error_message );
            die( 'Request failed. '. $error_message );
        }

        $soap_response = $response['body'];
        
        $xml = new DomDocument();
        $xml->loadXML( $soap_response );
        
        $identifiers = $xml->getElementsbyTagName ( "Identifier" );
        $identifier = $identifiers->item(0)->textContent;

        $mex_endpoint = $xml->getElementsbyTagName ( "MetadataReference" )->item(0)->getElementsbyTagName( "Address" );
        $mex_address = $mex_endpoint->item(0)->textContent;

        $mex_identifier = str_replace( '/mex', '', $mex_address );
         
        $this->log_debug( __METHOD__ . '(): ADFS URL retrieved: ' . $identifier );
        
        $adfs_url = str_replace ( 'http://', 'https://', $identifier );

        if ( $adfs_url !== $mex_identifier ) {
            $adfs_url = $mex_identifier;
        }

        return $adfs_url;
    }

    private function get_username_from_credentials( $credentials ) {
        $credentials_array = explode( ':', base64_decode( $credentials ) );
        return $credentials_array[0];
    }

    private function get_password_from_credentials( $credentials ) {
        $credentials_array = explode( ':', base64_decode( $credentials ) );
        return $credentials_array[1];
    }

    private function generate_guid( ) {

        mt_srand( (double) microtime() * 10000 );
        $charid = md5( uniqid( rand(), true) );

        $guid = substr( $charid,  0, 8 ) . '-' .
                substr( $charid,  8, 4 ) . '-' .
                substr( $charid, 12, 4 ) . '-' .
                substr( $charid, 16, 4 ) . '-' .
                substr( $charid, 20, 12 );

        $guid = 'uuid-' . $guid . '-1';

        return $guid;

    }
    
    /**
     * Writes an error message to the Gravity Forms log. Requires the Gravity Forms logging Add-On.
     */
    public function log_error( $message ) {
        if ( class_exists( 'GFDynamicsCRM' ) ) {
            $gfdcrm = GFDynamicsCRM::get_instance();
            $gfdcrm->log_error( $message );
        }
    }

    /**
     * Writes an error message to the Gravity Forms log. Requires the Gravity Forms logging Add-On.
     */
    public function log_debug( $message ) {
        if ( class_exists( 'GFDynamicsCRM' ) ) {
            $gfdcrm = GFDynamicsCRM::get_instance();
            $gfdcrm->log_debug( $message );
        }
    }

} //end class GF_Dynamics_CRM_ADFS_Auth_Client