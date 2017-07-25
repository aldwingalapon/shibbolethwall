<?php
/**
 * Minimal Dynamics CRM OData REST API Wrapper (https://msdn.microsoft.com/en-us/library/gg334279.aspx)
 */
class Dynamics_CRM_API {

	protected $access_token;
	protected $api_endpoint = '%sXRMServices/2011/OrganizationData.svc/';
	protected $soap_endpoint = '%sXRMServices/2011/Organization.svc';
	private $verify_ssl   = false;

	/**
	 * Create a new instance
	 * @param string $tenant_id Dynamics CRM Tenant ID
	 * @param string $access_token Azure AD OAuth Access Token
	 */
	function __construct( $tenant_id, $access_token ) {
		$this->access_token = $access_token;
		$this->api_endpoint = sprintf( $this->api_endpoint, trailingslashit( $tenant_id ) );
		$this->soap_endpoint = sprintf( $this->soap_endpoint, trailingslashit( $tenant_id ) );

	} //end function __construct

	/**
	 * Call an API method. Every request needs the Access Token, so that is added automatically -- you don't need to pass it in.
	 * @param  string $resource The API resource to be called
	 * @param  array  $args   Assoc array of parameters to be passed
	 * @param  string $method HTTP method to be used for the request (GET, POST, PUT, PATCH, DELETE)
	 * @return array          Associative array of json decoded API response.
	 */
	public function call( $resource, $args = array(), $method = 'GET', $impersonate_user = null ) {

		return $this->api_request( $resource, $args, $method, $impersonate_user );

	} //end function call

	public function get( $resource, $args = array(), $impersonate_user = null ) {

		return $this->api_request( $resource, $args, 'GET', $impersonate_user );

	} //end function post

	public function post( $resource, $args = array(), $impersonate_user = null ) {

		return $this->api_request( $resource, $args, 'POST', $impersonate_user );

	} //end function post

	public function put( $resource, $args = array(), $impersonate_user = null ) {

		return $this->api_request( $resource, $args, 'PUT', $impersonate_user );

	} //end function put

	public function merge( $resource, $args = array(), $impersonate_user = null ) {

		return $this->api_request( $resource, $args, 'MERGE', $impersonate_user );

	} //end function put

	public function patch( $resource, $args = array(), $impersonate_user = null ) {

		return $this->api_request( $resource, $args, 'PATCH', $impersonate_user );

	} //end function patch

	public function delete( $resource, $args = array(), $impersonate_user = null ) {

		return $this->api_request( $resource, $args, 'DELETE', $impersonate_user );

	} //end function delete

	public static function dump( $d, $halt = 0 ) {
        print '<pre>' . print_r( $d, true ) . '</pre>';
        if ( $halt ) {
            die( 'Halted ...' );
        }
    }

    public static function dd( $d ) {
        return Dynamics_CRM_API::dump( $d, 1 );
    }

	/**
	 * Performs the underlying HTTP request. Not very exciting
	 * @param  string $resource The API resource to be called
	 * @param  array  $args   Assoc array of parameters to be passed
	 * @param  string $method HTTP method to be used for the request (GET, POST, PUT, PATCH, DELETE)
	 * @return array          Assoc array of decoded result
	 */
	private function api_request( $resource, $args = array(), $method, $impersonate_user = null ) {      

		$url = $this->api_endpoint . $resource;

		$old_method = $method;

		if ( $method == 'MERGE' ) {
			$method = 'POST';
		}

		$request_args = array(
			'method'		=> $method,
			'body' 			=> json_encode( $args ),
			'sslverify' 	=> false,
			'timeout' 		=> 60,
			'httpversion'   => '1.1',
			'headers'       => array(
				'Authorization' => 'Bearer ' . $this->access_token,
				'Content-Type'  => 'application/json',
				'Accept'		=> 'application/json',
			),
			'user-agent'	=> 'Gravity-Forms-Dynamics-CRM-Add-On/'.GFDCRM_Plugin::version,
		);

		if ( $old_method == 'MERGE' ) {
			$request_args['headers']['x-http-method'] = $old_method;
		}

		if ( $impersonate_user ) {
			$request_args['headers']['MSCRMCallerID'] = $impersonate_user;
		}

		$this->log_debug( __METHOD__ . '(): Attempting API Request (' . $url . '):' . print_r( $request_args, true ) );

		$response = wp_remote_request( $url, $request_args );

		$this->log_debug( __METHOD__ . '(): Response from API Request (' . $url . '):' . print_r( $response, true ) );

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
   			$this->log_error( __METHOD__ . '(): Error making api request for ' . $url . ':' . print_r( $request_args, true ) . '. Error: ' . $error_message );
   			die( 'Request failed. '. $error_message );
   		} else {
   			$response_body = json_decode( $response['body'], true );

			if ( isset( $response_body['response']['error'] ) ) {
				
				$this->log_error( __METHOD__ . '(): Response error: ' . $response_body['response']['error'] );
				throw new Exception( $response_body['response']['error']['message'] );
				
			} else {

				if ( !in_array( $response['response']['code'], array( '200', '201', '204' ) ) ) {
					//Dynamics_CRM_API::dd( $response );
					$this->log_error( __METHOD__ . '(): Response code (' . $response['response']['code'] . ' not in (200, 201, 204)' );
					throw new Exception( print_r( $response, true ) );
				}

				return $response_body['d'];
				
			}
   		}

   		$this->log_debug( __METHOD__ . '(): Success: API Request (' . $url . '):' . print_r( $request_args, true ) );

   		return $response;

	} //end function api_request

	/**
	 * has_access_token function.
	 *
	 * @access public
	 * @return void
	 */
	public function has_access_token() {
		if ( $this->access_token ) {
			return true;
		}
	}

	/**
     * Find a lead by email address
     * 
     * @access public
     * @param array $lead
     * @return mixed
     */
    public function find_lead_by( $key, $value ) {

        $resource = 'LeadSet?$filter='.urlencode( "$key eq '$value'" );

        return $this->get( $resource );
    }

    /**
     * Create a lead if it doesn't already exist (using passed key as lookup)
     * 
     * @access public
     * @param array $lead
     * @param string $key
     * @return mixed
     */
    public function create_lead_if_not_exists( $lead, $key, $lead_owner = null ) {

    	$response = $this->find_lead_by( $key, $lead[$key] );

    	if ( count( $response['results'] ) > 0 ) {
    		$this->log_error( __METHOD__ . '(): ' . sprintf( 'Lead with key (%s) already exists with value (%s): %s', $key, $lead[$key], print_r( $response['results'][0], true ) ) );
    		return;
    	}
        return $this->create_lead( $lead, $lead_owner );
    }

    /**
     * Create a lead if it doesn't already exist (using passed key as lookup)
     * 
     * @access public
     * @param array $lead
     * @param string $key
     * @return mixed
     */
    public function create_or_update_lead( $lead, $key, $lead_owner = null ) {

    	$response = $this->find_lead_by( $key, $lead[$key] );

    	if ( count( $response['results'] ) > 0 ) {
    		$lead['LeadId'] = $response['results'][0]['LeadId'];
    		return $this->update_lead( $lead, $lead_owner );
    	}
        return $this->create_lead( $lead, $lead_owner );

    }

	/**
     * Create a lead
     * 
     * @access public
     * @param array $lead
     * @return mixed
     */
    public function create_lead( $lead, $lead_owner = null ) {

        return $this->post( 'LeadSet', $lead, $lead_owner );

    }

    /**
     * Update a lead
     * 
     * @access public
     * @param array $lead
     * @return mixed
     */
    public function update_lead( $lead, $lead_owner = null ) {

    	$id = $lead['LeadId'];
    	unset( $lead['LeadId'] );

        $response = $this->merge( 'LeadSet(guid\''. $id . '\')', $lead, $lead_owner );
        
        $response['LeadId'] = $id;

        return $response;

    }

    /**
     * Find a contact by email address
     * 
     * @access public
     * @param array $contact
     * @return mixed
     */
    public function find_contact_by( $key, $value ) {

        $resource = 'ContactSet?$filter='.urlencode( "$key eq '$value'" );

        return $this->get( $resource );
    }

    /**
     * Create a contact if it doesn't already exist (using passed key as lookup)
     * 
     * @access public
     * @param array $contact
     * @param string $key
     * @return mixed
     */
    public function create_contact_if_not_exists( $contact, $key, $contact_owner = null ) {

    	$response = $this->find_contact_by( $key, $contact[$key] );

    	if ( count( $response['results'] ) > 0 ) {
    		$this->log_error( __METHOD__ . '(): ' . sprintf( 'Contact with key (%s) already exists with value (%s): %s', $key, $contact[$key], print_r( $response['results'][0], true ) ) );
    		return;
    	}
        return $this->create_contact( $contact, $contact_owner );
    }

    /**
     * Create a contact if it doesn't already exist (using passed key as lookup)
     * 
     * @access public
     * @param array $contact
     * @param string $key
     * @return mixed
     */
    public function create_or_update_contact( $contact, $key, $contact_owner = null ) {

    	$response = $this->find_contact_by( $key, $contact[$key] );

    	if ( count( $response['results'] ) > 0 ) {
    		$contact['ContactId'] = $response['results'][0]['ContactId'];
    		return $this->update_contact( $contact, $contact_owner );
    	}
        return $this->create_contact( $contact, $contact_owner );

    }

	/**
     * Create a contact
     * 
     * @access public
     * @param array $contact
     * @return mixed
     */
    public function create_contact( $contact, $contact_owner = null ) {

        return $this->post( 'ContactSet', $contact, $contact_owner );

    }

    /**
     * Update a contact
     * 
     * @access public
     * @param array $contact
     * @return mixed
     */
    public function update_contact( $contact, $contact_owner = null ) {

    	$id = $contact['ContactId'];
    	unset( $contact['ContactId'] );

        $response = $this->merge( 'ContactSet(guid\''. $id . '\')', $contact, $contact_owner );

        $response['ContactId'] = $id;
        
        return $response;

    }

    /**
     * Find a case by key
     * 
     * @access public
     * @param array $case
     * @return mixed
     */
    public function find_case_by( $key, $value ) {

        $resource = 'IncidentSet?$filter='.urlencode( "$key eq '$value'" );

        return $this->get( $resource );
    }

    /**
     * Create a case if it doesn't already exist (using passed key as lookup)
     * 
     * @access public
     * @param array $case
     * @param string $key
     * @return mixed
     */
    public function create_case_if_not_exists( $case, $key, $case_owner = null ) {

    	$response = $this->find_case_by( $key, $case[$key] );

    	if ( count( $response['results'] ) > 0 ) {
    		$this->log_error( __METHOD__ . '(): ' . sprintf( 'Case with key (%s) already exists with value (%s): %s', $key, $case[$key], print_r( $response['results'][0], true ) ) );
    		return;
    	}
        return $this->create_case( $case, $case_owner );
    }

    /**
     * Create a case if it doesn't already exist (using passed key as lookup)
     * 
     * @access public
     * @param array $case
     * @param string $key
     * @return mixed
     */
    public function create_or_update_case( $case, $key, $case_owner = null ) {

    	$response = $this->find_case_by( $key, $case[$key] );

    	if ( count( $response['results'] ) > 0 ) {
    		$case['IncidentId'] = $response['results'][0]['IncidentId'];
    		return $this->update_case( $case, $case_owner );
    	}
        return $this->create_case( $case, $case_owner );

    }

	/**
     * Create a case
     * 
     * @access public
     * @param array $case
     * @return mixed
     */
    public function create_case( $case, $case_owner = null ) {

        return $this->post( 'IncidentSet', $case, $case_owner );

    }

    /**
     * Update a case
     * 
     * @access public
     * @param array $case
     * @return mixed
     */
    public function update_case( $case, $case_owner = null ) {

    	$id = $case['IncidentId'];
    	unset( $case['IncidentId'] );

        $response = $this->merge( 'IncidentSet(guid\''. $id . '\')', $case, $case_owner );

        $response['IncidentId'] = $id;
        
        return $response;

    }

    /**
	 * Get fields for module.
	 * 
	 * @access public
	 * @param string $module (default: 'Leads')
	 * @return array
	 */
	public function get_fields( $module = 'lead', $field_type = null ) {

		$this->log_debug( __METHOD__ . '(): Attempting to retrieve fields for module (' . $module . ')' );

        try {

	        $field_list = $this->get_entity_metadata( $module );

	        $this->log_debug( __METHOD__ . '(): Successfully retrieved fields for module (' . $module . ').' . "\n" . print_r( $field_list, true ) );

	        $this->log_debug( __METHOD__ . '(): Filtering field list for module (' . $module . ').' );

			// Workaround for array_filter issue on some hosts (cache op code issue with lamdas on eAccelerator)
			// Moved lamda to its own class and method Dynamics_CRM_Module_Fields_Filter@filter_module_field_list
	        $field_list = array_filter( $field_list, array( new Dynamics_CRM_Module_Fields_Filter( $field_type ), 'filter_module_field_list' ) );

	        $this->log_debug( __METHOD__ . '(): Sorting field list for module (' . $module . ').' );

			usort( $field_list, array( 'Dynamics_CRM_API', 'sort_module_field_list' ) );

			$this->log_debug( __METHOD__ . '(): Module (' . $module . ') fields: ' . print_r( $field_list, true ) );

	        return $field_list;

        } catch (Exception $e) {
        	$this->log_error( __METHOD__ . '(): Error retrieving fields for module (' . $module . ').' . "\n" . $e->getMessage() );
        }

	} //end function get_lead_fields

	public static function sort_module_field_list( $a, $b ) {
	    return strcasecmp( $a['label'], $b['label'] );
	}

	/**
     * Calls Dynamics SOAP Endpoint to retrieve entity metadata
     * This data is not yet available in the existing OData API but might
     * eventually be available in the new Preview REST API. When it is, we'll switch to REST.
     * 
     * @access public
     * @since 1.1.0
     * @return mixed
     */
    public function get_entity_metadata( $entity ) {

    	$soap_body = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
		  <s:Body>
		    <Execute xmlns="http://schemas.microsoft.com/xrm/2011/Contracts/Services" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
		      <request xmlns:a="http://schemas.microsoft.com/xrm/2011/Contracts">
		        <a:Parameters xmlns:b="http://schemas.datacontract.org/2004/07/System.Collections.Generic">
		          <a:KeyValuePairOfstringanyType>
		            <b:key>EntityFilters</b:key>
		            <b:value i:type="c:EntityFilters" xmlns:c="http://schemas.microsoft.com/xrm/2011/Metadata">Attributes</b:value>
		          </a:KeyValuePairOfstringanyType>
		          <a:KeyValuePairOfstringanyType>
		            <b:key>MetadataId</b:key>
		            <b:value i:type="c:guid" xmlns:c="http://schemas.microsoft.com/2003/10/Serialization/">00000000-0000-0000-0000-000000000000</b:value>
		          </a:KeyValuePairOfstringanyType>
		          <a:KeyValuePairOfstringanyType>
		            <b:key>RetrieveAsIfPublished</b:key>
		            <b:value i:type="c:boolean" xmlns:c="http://www.w3.org/2001/XMLSchema">true</b:value>
		          </a:KeyValuePairOfstringanyType>
		          <a:KeyValuePairOfstringanyType>
		            <b:key>LogicalName</b:key>
		            <b:value i:type="c:string" xmlns:c="http://www.w3.org/2001/XMLSchema">' . $entity . '</b:value>
		          </a:KeyValuePairOfstringanyType>
		        </a:Parameters>
		        <a:RequestId i:nil="true" />
		        <a:RequestName>RetrieveEntity</a:RequestName>
		      </request>
		    </Execute>
		  </s:Body>
		</s:Envelope>';

		$url = $this->soap_endpoint . '/web';

		$request_args = array(
			'method'		=> 'POST',
			'body' 			=> $soap_body,
			'sslverify' 	=> false,
			'timeout' 		=> 60,
			'httpversion'   => '1.1',
			'headers'       => array(
				'Authorization' => 'Bearer ' . $this->access_token,
				'Content-Type'  => 'text/xml; charset=utf-8',
				'SOAPAction'	=> 'http://schemas.microsoft.com/xrm/2011/Contracts/Services/IOrganizationService/Execute',
			),
			'user-agent'	=> 'Gravity-Forms-Dynamics-CRM-Add-On/'.GFDCRM_Plugin::version,
		);

		$response = wp_remote_request( $url, $request_args );

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
   			$this->log_error( __METHOD__ . '(): Error making api request for:' . print_r( $request_args, true ) . '. Error: ' . $error_message );
   			die( 'Request failed. '. $error_message );
   		}

   		$soap_response = $response['body'];

		$xml = new DomDocument();
        $xml->loadXML( $soap_response );

        $attributes = $xml->getElementsbyTagName("AttributeMetadata");

        $fields = array();

        foreach ( $attributes as $attribute ) {

        	$field = array();

			if ( $attribute->getElementsbyTagName('DisplayName')->item(0) != null && $attribute->getElementsbyTagName('DisplayName')->item(0)->getElementsbyTagName('Label')->item(0) != null ) {
				$field['label'] = $attribute->getElementsbyTagName('DisplayName')->item(0)->getElementsbyTagName('Label')->item(0)->textContent;
			} else {
				continue;
			}

			$field['name'] = $attribute->getElementsbyTagName('SchemaName')->item(0)->textContent;
			$field['value'] = $attribute->getElementsbyTagName('SchemaName')->item(0)->textContent;

			$field['type'] = $attribute->getElementsbyTagName('AttributeType')->item(0)->textContent;

			// Fix handful of Address 1 fields that aren't grouped with "Address 1:"
			if ( Dynamics_CRM_API::startsWith( $field['name'], 'Address1' ) && !Dynamics_CRM_API::startsWith( $field['label'], 'Address 1:' ) ) {
				$field['label'] = sprintf( __( 'Address 1: %s', 'gravityformsdynamicscrm' ), $field['label'] );
			}

            $required = $attribute->getElementsbyTagName('RequiredLevel')->item(0)->getElementsbyTagName('Value')->item(0)->textContent;

            $field['required'] = ( $required == 'ApplicationRequired' || strtolower( $field['name'] ) == 'emailaddress1' ) ? true : false;

            //if ( strpos( strtolower( $field['name'] ), 'email' ) !== false ) {
            //	$field['field_type'] = array( 'email', 'hidden' );
            //}

            $fields[] = $field;

        }

        return $fields;
        
    }

    /**
     * Returns system users
     * 
     * @access public
     * @since 1.0.0
     * @return mixed
     */
    public function get_system_users( ) {

        $resource = 'SystemUserSet?$select=FullName,DomainName,IsLicensed,SystemUserId';

    	$response = $this->get( $resource );
    	$results = $response['results'];

        $results = $this->get_next( $resource, $response, $results );

    	$licensed_users = array();

    	// Only returned licensed users (excludes dynamics default and sysadmin users)
    	
    	// Workaround for array_filter issue on some hosts (cache op code issue with lamdas on eAccelerator)
		// Moved lamda to its own method Dynamics_CRM_API@filter_licensed_users
    	$licensed_users = array_filter( $results, array( 'Dynamics_CRM_API', 'filter_licensed_users' ) );

        return $licensed_users;
        
    }

    function get_next( $resource, $response, $results ) {
        
        if ( !isset( $response['__next'] ) ) return $results;

        $next = $response['__next'];
        $next = substr( $next, strpos( $next, '&$skiptoken=' ) );
        $response = $this->get( $resource . $next );
        $results = array_merge( $results, $response['results'] );
        $results = $this->get_next( $resource, $response, $results );

        return $results;
    }

    public function filter_licensed_users( $user ) {
    	//return $user['IsLicensed'] == true;
    	return !in_array( $user['FullName'], array( 'SYSTEM', 'INTEGRATION' ) );
    }

    /**
     * Returns user id of currently authenticated ser
     * 
     * @access public
     * @since 1.2.0
     * @return mixed
     */
    public function get_current_user() {

    	$soap_body = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
		  <s:Body>
		    <Execute xmlns="http://schemas.microsoft.com/xrm/2011/Contracts/Services" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
		      <request i:type="b:WhoAmIRequest" xmlns:a="http://schemas.microsoft.com/xrm/2011/Contracts" xmlns:b="http://schemas.microsoft.com/crm/2011/Contracts">
		        <a:Parameters xmlns:c="http://schemas.datacontract.org/2004/07/System.Collections.Generic" />
		        <a:RequestId i:nil="true" />
		        <a:RequestName>WhoAmI</a:RequestName>
		      </request>
		    </Execute>
		  </s:Body>
		</s:Envelope>';

		$url = $this->soap_endpoint . '/web';

		$request_args = array(
			'method'		=> 'POST',
			'body' 			=> $soap_body,
			'sslverify' 	=> false,
			'timeout' 		=> 60,
			'httpversion'   => '1.1',
			'headers'       => array(
				'Authorization' => 'Bearer ' . $this->access_token,
				'Content-Type'  => 'text/xml; charset=utf-8',
				'SOAPAction'	=> 'http://schemas.microsoft.com/xrm/2011/Contracts/Services/IOrganizationService/Execute',
			),
			'user-agent'	=> 'Gravity-Forms-Dynamics-CRM-Add-On/'.GFDCRM_Plugin::version,
		);

		$response = wp_remote_request( $url, $request_args );

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
   			$this->log_error( __METHOD__ . '(): Error making api request for:' . print_r( $request_args, true ) . '. Error: ' . $error_message );
   			die( 'Request failed. '. $error_message );
   		}

   		$soap_response = $response['body'];

		$xml = new DomDocument();
        $xml->loadXML( $soap_response );

        $values = $xml->getElementsbyTagName ( "value" );
        $current_user_id = $values->item(0)->textContent;

        return $current_user_id;
        
    }

    /**
     * Returns option set values (not being used yet, but this is in preparation for it)
     * 
     * @access public
     * @since 1.1.0
     * @return mixed
     */
    public function get_option_set_values( $module = 'lead', $field_name ) {

    	$fields = $this->get_fields( $module, 'lookup' );

    	// Workaround for array_filter issue on some hosts (cache op code issue with lamdas on eAccelerator)
		// Moved lamda to its own class and method Dynamics_CRM_Current_Field_Filter@filter_current_field
    	$lookup_field = current( array_filter( $fields, array( new Dynamics_CRM_Current_Field_Filter($field_name), 'filter_current_field' ) ) );

    	$response = $this->get( 'PickListMappingSet?$filter=ColumnMappingId/Id%20eq%20(guid\'' . $lookup_field['column_mapping_id'] . '\')' );

    	// Only returned licensed users (excludes dynamics default and sysadmin users)
    	$option_set_values = $response['results'];

        return $option_set_values;
        
    }

	/**
     * Get the Dynamics CRM OData API Entity Sets
     * 
     * @access public
     * @return mixed
     */
    public function get_entity_sets() {

        return $this->get( '' );

    }

    public static function startsWith( $haystack, $needle ) {
    	// search backwards starting from haystack length characters from the end
    	return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
	}

	public static function endsWith( $haystack, $needle ) {
	    // search forward starting from end minus needle length characters
	    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
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

} //end class Dynamics_CRM_API

// method of this class can be uses as a callback function
class Dynamics_CRM_Module_Fields_Filter {

	private $field_type;
 
	function __construct( $field_type ) {
		$this->field_type = $field_type;
	}
 
	public function filter_module_field_list($field) {
      	if ( $this->field_type == 'lookup' ) {
	    	return isset( $field['type'] ) && $field['type'] == $this->field_type;
		} else {
			return ( !isset( $field['type'] ) || $field['type'] != $this->field_type ) 
				&& $field['name'] != 'Subject'
				&& !Dynamics_CRM_API::endsWith( $field['name'], '_Base' )
				&& strpos( $field['label'] , 'deprecated' ) === false
				&& !in_array( $field['type'] , array( 'Uniqueidentifier', 'Lookup', 'Owner' ) );
		}
	}
}

// method of this class can be uses as a callback function
class Dynamics_CRM_Current_Field_Filter {

	private $field_name;
 
	function __construct($field_name) {
		$this->field_name = $field_name;
	}

	public function filter_current_field($field) {
		return $field['name'] == $this->field_name;
	}

}