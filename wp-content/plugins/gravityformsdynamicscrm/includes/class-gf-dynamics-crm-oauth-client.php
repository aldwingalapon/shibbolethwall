<?php

class GF_Dynamics_CRM_OAuth_Client extends Azure_AD_OAuth_Client {

    protected $auth_endpoint = 'https://www.saintsystems.com/gfdcrm/oauth2/authorize'; //'http://wp.dev/gfdcrm/oauth2/authorize';
    protected $token_endpoint = 'https://www.saintsystems.com/gfdcrm/oauth2/token'; //'http://wp.dev/gfdcrm/oauth2/token'
    protected $redirect_uri = '/gfdcrm/callback';
    protected $dynamics_crm_url; // typically in the format: https://<tenant_id>.crm.dynamics.com/
    protected $license_key;
    const TOKEN_CREDENTIALS_OPTION = 'gravityformsdynamicscrm_credentials';

    public function __construct() {
        $settings = get_option( 'gravityformsaddon_gravityformsdynamicscrm_settings' );
        if ( isset( $settings['dynamics_crm_url'] ) ) $this->dynamics_crm_url = $settings['dynamics_crm_url']; else $this->dynamics_crm_url = '';
        if ( isset( $settings['client_id'] ) ) $this->client_id = $settings['client_id']; else $this->client_id = '';
        if ( isset( $settings['client_secret'] ) ) $this->client_secret = $settings['client_secret']; else $this->client_secret = '';
        if ( isset( $settings['license_key'] ) ) $this->license_key = $settings['license_key']; else $this->license_key = '';
        if ( $credentials = get_option( 'gravityformsdynamicscrm_credentials' ) ) {
            $access_token = rgar( $credentials, 'access_token' );
            $token_type = rgar( $credentials, 'token_type' );
            $this->setAccessToken( $access_token );
            $this->setAccessTokenType( $token_type );
        }
        $this->redirect_uri = home_url() . $this->redirect_uri;
        parent::__construct( $this->client_id, $this->client_secret, Azure_AD_OAuth_Client::AUTH_TYPE_URI, null );
    }

    private function generate_guid( $include_braces = false ) {

        // testing using license key as oauth state
        return $this->license_key;

        if ( function_exists( 'com_create_guid' ) ) {
            if ( $include_braces === true ) {
                return com_create_guid();
            } else {
                return substr( com_create_guid(), 1, 36 );
            }
        } else {
            mt_srand( (double) microtime() * 10000 );
            $charid = md5( uniqid( rand(), true) );

            $guid = substr( $charid,  0, 8 ) . '-' .
                    substr( $charid,  8, 4 ) . '-' .
                    substr( $charid, 12, 4 ) . '-' .
                    substr( $charid, 16, 4 ) . '-' .
                    substr( $charid, 20, 12 );

            if ( $include_braces ) {
                $guid = '{' . $guid . '}';
            }

            return $guid;
        }
    }

    public function get_tenant_id( ) {

        return $this->tenant_id;

    } //end function get_tenant_url

    public function get_tenant_url( ) {

        return trailingslashit( $this->dynamics_crm_url ); //str_replace( '<tenant_id>', $this->tenant_id, $this->dynamics_crm_url );

    } //end function get_tenant_url

    public function authorize() {
        $state = $this->generate_guid();
        // 86400 below = 1 day
        setcookie( 'gfdcrm.authstate', $state, 0, COOKIEPATH, COOKIE_DOMAIN );
        $params = array(
            'scope' => 'UserProfile.Read', 
            'state' => $state,
            'resource' => $this->get_tenant_url(),
            //'prompt' => 'admin_consent',
        );
        $auth_url = parent::getAuthenticationUrl( $this->auth_endpoint, $this->redirect_uri, $params );
        header('Location: ' . $auth_url);
        die();
    }

    public function acquire_token() {
        return $this->get_token_from_cache_with_refresh();
    }

    public function acquire_token_with_authorization_code() {
        return $this->get_token_with_authorization_code();
    }

    private function get_token_with_authorization_code() {
        $params = array( 'code' => $_GET['code'], 'redirect_uri' => $this->get_redirect_uri(), 'resource' => $this->dynamics_crm_url );

        return $this->get_token( 'authorization_code', $params );
    }

    private function get_token_from_cache_with_refresh() {

        if ( $credentials = get_option( self::TOKEN_CREDENTIALS_OPTION ) ) {
            if ( !isset( $credentials['expires_on'] ) ) return;

            $expires = $credentials['expires_on'];
            $now = time();

            // If the token expires under an hour from now, refresh it
            if ( $expires - $now < 900 ) {
                $credentials = $this->get_token_with_refresh_token( $credentials['refresh_token'] );
            }
        } else {
            // Get a fresh token
            $credentials = $this->acquire_token_with_authorization_code();
        }

        return $credentials;
    }

    private function get_token_with_refresh_token( $refresh_token ) {

        $params = array( 
            'refresh_token' => $refresh_token, 
            'resource' => $this->get_tenant_url()
        );

        return $this->get_token( 'refresh_token', $params );
    }

    private function get_token( $grant_type, $params ) {

        $response = parent::getAccessToken( $this->token_endpoint, $grant_type, $params );

        $credentials = $response['result'];

        update_option( self::TOKEN_CREDENTIALS_OPTION, $credentials );
        if ( isset( $credentials['access_token'] ) ) {
            $this->access_token = $credentials['access_token'];
        }

        return $credentials;

    } //end function get_token

    public function get_redirect_uri() {

        return $this->redirect_uri;

    } //end function get_redirect_uri

} //end class GF_Dynamics_CRM_OAuth_Client

class Azure_AD_OAuth_Client {

	/**
     * Different AUTH method
     */
    const AUTH_TYPE_URI                 = 0;
    const AUTH_TYPE_AUTHORIZATION_BASIC = 1;
    const AUTH_TYPE_FORM                = 2;

    /**
     * Different Access token type
     */
    const ACCESS_TOKEN_URI      = 0;
    const ACCESS_TOKEN_BEARER   = 1;
    const ACCESS_TOKEN_OAUTH    = 2;
    const ACCESS_TOKEN_MAC      = 3;

    /**
    * Different Grant types
    */
    const GRANT_TYPE_AUTH_CODE          = 'authorization_code';
    const GRANT_TYPE_PASSWORD           = 'password';
    const GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials';
    const GRANT_TYPE_REFRESH_TOKEN      = 'refresh_token';

    /**
     * HTTP Methods
     */
    const HTTP_METHOD_GET    = 'GET';
    const HTTP_METHOD_POST   = 'POST';
    const HTTP_METHOD_PUT    = 'PUT';
    const HTTP_METHOD_DELETE = 'DELETE';
    const HTTP_METHOD_HEAD   = 'HEAD';
    const HTTP_METHOD_PATCH   = 'PATCH';

    /**
     * HTTP Form content types
     */
    const HTTP_FORM_CONTENT_TYPE_APPLICATION = 0;
    const HTTP_FORM_CONTENT_TYPE_MULTIPART = 1;

    /**
     * Client ID
     *
     * @var string
     */
    protected $client_id = null;

    /**
     * Client Secret
     *
     * @var string
     */
    protected $client_secret = null;

    /**
     * Client Authentication method
     *
     * @var int
     */
    protected $client_auth = self::AUTH_TYPE_URI;

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
    protected $access_token_type = self::ACCESS_TOKEN_URI;

    /**
     * Access Token Secret
     *
     * @var string
     */
    protected $access_token_secret = null;

    /**
     * Access Token crypt algorithm
     *
     * @var string
     */
    protected $access_token_algorithm = null;

    /**
     * Access Token Parameter name
     *
     * @var string
     */
    protected $access_token_param_name = 'access_token';

    /**
     * The path to the certificate file to use for https connections
     *
     * @var string  Defaults to .
     */
    protected $certificate_file = null;

    /**
     * cURL options
     *
     * @var array
     */
    protected $curl_options = array();

    /**
     * Construct
     *
     * @param string $client_id Client ID
     * @param string $client_secret Client Secret
     * @param int    $client_auth (AUTH_TYPE_URI, AUTH_TYPE_AUTHORIZATION_BASIC, AUTH_TYPE_FORM)
     * @param string $certificate_file Indicates if we want to use a certificate file to trust the server. Optional, defaults to null.
     * @return void
     */
    public function __construct($client_id, $client_secret, $client_auth = self::AUTH_TYPE_URI, $certificate_file = null) {
        if (!extension_loaded('curl')) {
            throw new Exception('The PHP exention curl must be installed to use this library.', Exception::CURL_NOT_FOUND);
        }

        $this->client_id     = $client_id;
        $this->client_secret = $client_secret;
        $this->client_auth   = $client_auth;
        $this->certificate_file = $certificate_file;
        if (!empty($this->certificate_file)  && !is_file($this->certificate_file)) {
            throw new InvalidArgumentException('The certificate file was not found', InvalidArgumentException::CERTIFICATE_NOT_FOUND);
        }
    }

    /**
     * Get the client Id
     *
     * @return string Client ID
     */
    public function getClientId() {
        return $this->client_id;
    }

    /**
     * Get the client Secret
     *
     * @return string Client Secret
     */
    public function getClientSecret() {
        return $this->client_secret;
    }

    /**
     * getAuthenticationUrl
     *
     * @param string $auth_endpoint Url of the authentication endpoint
     * @param string $redirect_uri  Redirection URI
     * @param array  $extra_parameters  Array of extra parameters like scope or state (Ex: array('scope' => null, 'state' => ''))
     * @return string URL used for authentication
     */
    public function getAuthenticationUrl( $auth_endpoint, $redirect_uri, array $extra_parameters = array() ) {
        $parameters = array_merge(array(
            'response_type' => 'code',
            'client_id'     => $this->client_id,
            'redirect_uri'  => $redirect_uri
        ), $extra_parameters);
        return $auth_endpoint . '?' . http_build_query($parameters, null, '&');
    }

    /**
     * getAccessToken
     *
     * @param string $token_endpoint    Url of the token endpoint
     * @param int    $grant_type        Grant Type ('authorization_code', 'password', 'client_credentials', 'refresh_token', or a custom code (@see GrantType Classes)
     * @param array  $parameters        Array sent to the server (depend on which grant type you're using)
     * @param array  $extra_headers     Array of extra headers
     * @return array Array of parameters required by the grant_type (CF SPEC)
     */
    public function getAccessToken($token_endpoint, $grant_type, array $parameters, array $extra_headers = array()) {
        // if (!$grant_type) {
        //     throw new InvalidArgumentException('The grant_type is mandatory.', InvalidArgumentException::INVALID_GRANT_TYPE);
        // }
        // $grantTypeClassName = $this->convertToCamelCase($grant_type);
        // $grantTypeClass =  __NAMESPACE__ . '\\GrantType\\' . $grantTypeClassName;
        // if (!class_exists($grantTypeClass)) {
        //     throw new InvalidArgumentException('Unknown grant type \'' . $grant_type . '\'', InvalidArgumentException::INVALID_GRANT_TYPE);
        // }
        // $grantTypeObject = new $grantTypeClass();
        // $grantTypeObject->validateParameters($parameters);
        // if (!defined($grantTypeClass . '::GRANT_TYPE')) {
        //     throw new Exception('Unknown constant GRANT_TYPE for class ' . $grantTypeClassName, Exception::GRANT_TYPE_ERROR);
        // }
        $parameters['grant_type'] = $grant_type;
        $http_headers = $extra_headers;
        switch ($this->client_auth) {
            case self::AUTH_TYPE_URI:
            case self::AUTH_TYPE_FORM:
                $parameters['client_id'] = $this->client_id;
                $parameters['client_secret'] = $this->client_secret;
                break;
            case self::AUTH_TYPE_AUTHORIZATION_BASIC:
                $parameters['client_id'] = $this->client_id;
                $http_headers['Authorization'] = 'Basic ' . base64_encode($this->client_id .  ':' . $this->client_secret);
                break;
            default:
                throw new Exception('Unknown client auth type.', Exception::INVALID_CLIENT_AUTHENTICATION_TYPE);
                break;
        }

        return $this->executeRequest($token_endpoint, $parameters, self::HTTP_METHOD_POST, $http_headers, self::HTTP_FORM_CONTENT_TYPE_APPLICATION);
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
     * Check if there is an access token present
     *
     * @return bool Whether the access token is present
     */
    public function hasAccessToken() {
        return !!$this->access_token;
    }

    /**
     * Set the client authentication type
     *
     * @param string $client_auth (AUTH_TYPE_URI, AUTH_TYPE_AUTHORIZATION_BASIC, AUTH_TYPE_FORM)
     * @return void
     */
    public function setClientAuthType($client_auth) {
        $this->client_auth = $client_auth;
    }

    /**
     * Set an option for the curl transfer
     *
     * @param int   $option The CURLOPT_XXX option to set
     * @param mixed $value  The value to be set on option
     * @return void
     */
    public function setCurlOption($option, $value) {
        $this->curl_options[$option] = $value;
    }

    /**
     * Set multiple options for a cURL transfer
     *
     * @param array $options An array specifying which options to set and their values
     * @return void
     */
    public function setCurlOptions($options) {
        $this->curl_options = array_merge($this->curl_options, $options);
    }

    /**
     * Set the access token type
     *
     * @param int $type Access token type (ACCESS_TOKEN_BEARER, ACCESS_TOKEN_MAC, ACCESS_TOKEN_URI)
     * @param string $secret The secret key used to encrypt the MAC header
     * @param string $algorithm Algorithm used to encrypt the signature
     * @return void
     */
    public function setAccessTokenType($type, $secret = null, $algorithm = null) {
        $this->access_token_type = $type;
        $this->access_token_secret = $secret;
        $this->access_token_algorithm = $algorithm;
    }

    public function call( $url, $params, $http_method = self::HTTP_METHOD_GET ) {
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->access_token,
                'Content-Type' => 'application/json',
            ),
            'timeout' => '60',
            'body' => json_encode($params),
            'method' => $http_method,
        );
        $response = wp_remote_request( $url, $args );
        print_r($response);
        if ( !is_wp_error( $response ) ) {
            if ( $response['response']['code'] == 401 ) {
                //if ( $response['headers']['www-authenticate'] == '' )
            } else {
                $entityId = $response['headers']['odata-entityid'];
            }
            //echo $entityId;
        }
        die();
    }

    /**
     * Fetch a protected ressource
     *
     * @param string $protected_ressource_url Protected resource URL
     * @param array  $parameters Array of parameters
     * @param string $http_method HTTP Method to use (POST, PUT, GET, HEAD, DELETE)
     * @param array  $http_headers HTTP headers
     * @param int    $form_content_type HTTP form content type to use
     * @return array
     */
    public function fetch($protected_resource_url, $parameters = array(), $http_method = self::HTTP_METHOD_GET, array $http_headers = array(), $form_content_type = self::HTTP_FORM_CONTENT_TYPE_MULTIPART) {
        if ($this->access_token) {
            switch ($this->access_token_type) {
                case self::ACCESS_TOKEN_URI:
                    if (is_array($parameters)) {
                        $parameters[$this->access_token_param_name] = $this->access_token;
                    } else {
                        throw new InvalidArgumentException(
                            'You need to give parameters as array if you want to give the token within the URI.',
                            InvalidArgumentException::REQUIRE_PARAMS_AS_ARRAY
                        );
                    }
                    break;
                case self::ACCESS_TOKEN_BEARER:
                    $http_headers['Authorization'] = 'Bearer ' . $this->access_token;
                    break;
                case self::ACCESS_TOKEN_OAUTH:
                    $http_headers['Authorization'] = 'OAuth ' . $this->access_token;
                    break;
                case self::ACCESS_TOKEN_MAC:
                    $http_headers['Authorization'] = 'MAC ' . $this->generateMACSignature($protected_resource_url, $parameters, $http_method);
                    break;
                default:
                    throw new Exception('Unknown access token type.', Exception::INVALID_ACCESS_TOKEN_TYPE);
                    break;
            }
        }
        return $this->executeRequest($protected_resource_url, $parameters, $http_method, $http_headers, $form_content_type);
    }

    /**
     * Generate the MAC signature
     *
     * @param string $url Called URL
     * @param array  $parameters Parameters
     * @param string $http_method Http Method
     * @return string
     */
    private function generateMACSignature($url, $parameters, $http_method) {
        $timestamp = time();
        $nonce = uniqid();
        $parsed_url = parse_url($url);
        if (!isset($parsed_url['port']))
        {
            $parsed_url['port'] = ($parsed_url['scheme'] == 'https') ? 443 : 80;
        }
        if ($http_method == self::HTTP_METHOD_GET) {
            if (is_array($parameters)) {
                $parsed_url['path'] .= '?' . http_build_query($parameters, null, '&');
            } elseif ($parameters) {
                $parsed_url['path'] .= '?' . $parameters;
            }
        }

        $signature = base64_encode(hash_hmac($this->access_token_algorithm,
                    $timestamp . "\n"
                    . $nonce . "\n"
                    . $http_method . "\n"
                    . $parsed_url['path'] . "\n"
                    . $parsed_url['host'] . "\n"
                    . $parsed_url['port'] . "\n\n"
                    , $this->access_token_secret, true));

        return 'id="' . $this->access_token . '", ts="' . $timestamp . '", nonce="' . $nonce . '", mac="' . $signature . '"';
    }

    /**
     * Execute a request (with curl)
     *
     * @param string $url URL
     * @param mixed  $parameters Array of parameters
     * @param string $http_method HTTP Method
     * @param array  $http_headers HTTP Headers
     * @param int    $form_content_type HTTP form content type to use
     * @return array
     */
    private function executeRequest($url, $parameters = array(), $http_method = self::HTTP_METHOD_GET, array $http_headers = null, $form_content_type = self::HTTP_FORM_CONTENT_TYPE_MULTIPART) {
        $curl_options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CUSTOMREQUEST  => $http_method
        );

        switch($http_method) {
            case self::HTTP_METHOD_POST:
                $curl_options[CURLOPT_POST] = true;
                /* No break */
            case self::HTTP_METHOD_PUT:
			case self::HTTP_METHOD_PATCH:

                /**
                 * Passing an array to CURLOPT_POSTFIELDS will encode the data as multipart/form-data,
                 * while passing a URL-encoded string will encode the data as application/x-www-form-urlencoded.
                 * http://php.net/manual/en/function.curl-setopt.php
                 */
                if(is_array($parameters) && self::HTTP_FORM_CONTENT_TYPE_APPLICATION === $form_content_type) {
                    $parameters = http_build_query($parameters, null, '&');
                }
                $curl_options[CURLOPT_POSTFIELDS] = $parameters;
                break;
            case self::HTTP_METHOD_HEAD:
                $curl_options[CURLOPT_NOBODY] = true;
                /* No break */
            case self::HTTP_METHOD_DELETE:
            case self::HTTP_METHOD_GET:
                if (is_array($parameters) && count($parameters) > 0) {
                    $url .= '?' . http_build_query($parameters, null, '&');
                } elseif ($parameters) {
                    $url .= '?' . $parameters;
                }
                break;
            default:
                break;
        }

        $curl_options[CURLOPT_URL] = $url;

        if (is_array($http_headers)) {
            $header = array();
            foreach($http_headers as $key => $parsed_urlvalue) {
                $header[] = "$key: $parsed_urlvalue";
            }
            $curl_options[CURLOPT_HTTPHEADER] = $header;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $curl_options);
        // https handling
        if (!empty($this->certificate_file)) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_CAINFO, $this->certificate_file);
        } else {
            // bypass ssl verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        if (!empty($this->curl_options)) {
            curl_setopt_array($ch, $this->curl_options);
        }
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        if ($curl_error = curl_error($ch)) {
            throw new Exception($curl_error, Exception::CURL_ERROR);
        } else {
            $json_decode = json_decode($result, true);
        }
        curl_close($ch);

        return array(
            'result' => (null === $json_decode) ? $result : $json_decode,
            'code' => $http_code,
            'content_type' => $content_type
        );
    }

    /**
     * Set the name of the parameter that carry the access token
     *
     * @param string $name Token parameter name
     * @return void
     */
    public function setAccessTokenParamName($name) {
        $this->access_token_param_name = $name;
    }

    /**
     * Converts the class name to camel case
     *
     * @param  mixed  $grant_type  the grant type
     * @return string
     */
    private function convertToCamelCase($grant_type) {
        $parts = explode('_', $grant_type);
        array_walk($parts, function(&$item) { $item = ucfirst($item);});
        return implode('', $parts);
    }

} //end class Azure_AD_OAuth_Client

