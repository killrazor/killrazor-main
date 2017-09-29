<?php






/*
			API CLASS
		
	Add public and private methods here. 

	This class is obstantiated by html/api.php
 */






  /////////////////////////////////////////////////////////////////////////////////////////////////
 //					APIRequest CLASS					//
/////////////////////////////////////////////////////////////////////////////////////////////////



class APIRequest {


	  /////////////////////////////////
	 //	OBJECT VARIABLES	//
	/////////////////////////////////


	// JSON/XML data variables. Might change to getters and setters.
	var $user_id;
	var $password;
	var $request_name;
	var $request_content = array();
	// setup vars
	var $cursor;
	var $inTest;
	var $DEBUG = false;
	// sub-apis can use these vars. 
	var $auth_type; 
	var $response_headers = array();
	var $content_type = "application/json";
	var $http_method;
	var $http_accept;
	var $http_raw;
	var $encoding = "UTF-8";


	  /////////////////////////////////
	 //	PUBLIC CLASS METHODS	//
	/////////////////////////////////
	

	// Use this when you don't need to return any data.
	public function send_response_exit( $code = 400, $content_length = 0 ) {
		header( "Content-length: $content_length" );
		header( "Content-Type: $this->content_type" );
		header( "$this->http_accept" );
		exit( http_response_code( $code ) );
	}


	// verify the user or exit. Used in api.php.
	public function verify_user() {
		$your_db = "sample_db";
		$your_other_db = "other_sample_db";
		$query = "
			SELECT ACCOUNT_ID
			FROM   $your_db$this->inTest
			WHERE  USER_ID = :user_id
			       AND PASSWORD = :password
			       AND ACCOUNT_ID IN (
				       SELECT ACCOUNT_ID
			               FROM   $your_other_db
			               WHERE  TERM_DATE IS NULL)"; 
		$statement = oci_parse( $this->cursor, $query );
		oci_bind_by_name( $statement,':user_id', $this->user_id );
		oci_bind_by_name( $statement,':password', $this->password );
		oci_execute( $statement );
		if ( !oci_fetch( $statement ) ) { // could set account_id here.
			$this->send_response_exit( 401 );
		}
	}


	// if you have data to return, use this method. Expecting an array.
	public function send_encoded_response( $data_to_encode = array(), $code = 200 ) {
		$content_type = explode( '/', $this->content_type );
		if ( count( $content_type ) > 1 ) {
			$content_type = $content_type[1];
		} else {
			$this->send_response_exit();
		}
		if ( $content_type == 'json' ) {
			// wrap in cl-info to mimic XML data. Completely useless otherwise.
			$data_to_encode = array( 'cl-info' => $data_to_encode );
			$content = json_encode( $data_to_encode ); 
		} elseif ( $content_type == 'xml' ) {
			$content = $this->encode_xml( $data_to_encode );
		}
		header( "Content-Type: $this->content_type" );
		header( "$this->http_accept" );
		header( "Content-length: " . strlen( $content ) );
		echo $content;
		exit( http_response_code( $code ) );
	}


	// parse, execute, and fetch a query and return the encoded response.
	public function pef_encoded_response ( $cursor, $query ) {
		$statement = oci_pexec( $cursor, $query );
		$result_array = array();
		while ( ( $result = oci_fetch_assoc( $statement ) ) !== false ) {
			array_push( $result_array, $result );
		}
		$this->send_encoded_response( $result_array );
	}


	  /////////////////////////////////
	 //	PRIVATE CLASS METHODS	//
	/////////////////////////////////


	// treverse an array and addChild
	private function array_to_xml( $data_to_encode, $xml_data ) {
		foreach( $data_to_encode as $key => $value ) {
			if ( is_numeric( $key ) ) { // this should only happen when you return multiple rows from your associative arrays.
				$key = 'item_' . $key;
			}
			if ( is_array( $value ) ) {
				$subnode = $xml_data->addChild( $key );
				$this->array_to_xml( $value, $subnode );
			} else {
				// htmlspec has to be used so we don't make malformed XML.
				$xml_data->addChild( "$key", htmlspecialchars( "$value" ) );
			}
		}
	}


	// encode array and return as XML.
	private function encode_xml( $data_to_encode ) {
		// wrapper name ( same as what we take in );
		$xml_data = new SimpleXMLElement( '<?xml version="1.0" encoding="' . $this->encoding .'"?><cl-info/>' );
		$this->array_to_xml( $data_to_encode, $xml_data );
		return $xml_data->asXML();
	}
}







  /////////////////////////////////////////////////////////////////////////////////////////////////
 //					PHP 5.3.3 NECESSITY					//
/////////////////////////////////////////////////////////////////////////////////////////////////



// Fix for PHP V5.3.3
// NOTE: it is important to notice that header() must be called before any actual output is sent ( you can use output buffering to solve this problem ).
if ( !function_exists( 'http_response_code' ) ) {
	function http_response_code( $code = NULL ) {
		if ( $code !== NULL ) {
			switch ( $code ) {
				case 100: $text = 'Continue'; break;
				case 101: $text = 'Switching Protocols'; break;
				case 200: $text = 'OK'; break;
				case 201: $text = 'Created'; break;
				case 202: $text = 'Accepted'; break;
				case 203: $text = 'Non-Authoritative Information'; break;
				case 204: $text = 'No Content'; break;
				case 205: $text = 'Reset Content'; break;
				case 206: $text = 'Partial Content'; break;
				case 300: $text = 'Multiple Choices'; break;
				case 301: $text = 'Moved Permanently'; break;
				case 302: $text = 'Moved Temporarily'; break;
				case 303: $text = 'See Other'; break;
				case 304: $text = 'Not Modified'; break;
				case 305: $text = 'Use Proxy'; break;
				case 400: $text = 'Bad Request'; break;
				case 401: $text = 'Unauthorized'; break;
				case 402: $text = 'Payment Required'; break;
				case 403: $text = 'Forbidden'; break;
				case 404: $text = 'Not Found'; break;
				case 405: $text = 'Method Not Allowed'; break;
				case 406: $text = 'Not Acceptable'; break;
				case 407: $text = 'Proxy Authentication Required'; break;
				case 408: $text = 'Request Time-out'; break;
				case 409: $text = 'Conflict'; break;
				case 410: $text = 'Gone'; break;
				case 411: $text = 'Length Required'; break;
				case 412: $text = 'Precondition Failed'; break;
				case 413: $text = 'Request Entity Too Large'; break;
				case 414: $text = 'Request-URI Too Large'; break;
				case 415: $text = 'Unsupported Media Type'; break;
				case 500: $text = 'Internal Server Error'; break;
				case 501: $text = 'Not Implemented'; break;
				case 502: $text = 'Bad Gateway'; break;
				case 503: $text = 'Service Unavailable'; break;
				case 504: $text = 'Gateway Time-out'; break;
				case 505: $text = 'HTTP Version not supported'; break;
				default:
					exit( 'Unknown http status code "' . htmlentities( $code ) . '"' );
				break;
			}
			$protocol = ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' );
			header( $protocol . ' ' . $code . ' ' . $text );
			$GLOBALS['http_response_code'] = $code;
		} else {
			$code = ( isset( $GLOBALS['http_response_code'] ) ? $GLOBALS['http_response_code'] : 200 );
		}
		return $code;
	}
}

?>
