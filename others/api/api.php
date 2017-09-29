<?php

require_once "APIRequest.php";
require_once "your_db_config_calls.php";





/*
					Generic API
	
		To use read docs and add your API to the switch near the bottom. Socket calls can also be
	done from the switch but a method should be added to the class to handle socketing.

		POST and GET are all this will handle because that is all we need currently.
	POST is being used as GET + body.

		XML should be structured like JSON. Attributes, Namespace, and Schema are not handled. 
	If the client data doesn't contain the correct fields, they will see failure.
	The data descriptors sent by the client must be lowercase.
	Exmaple data at the bottom of this script.

		Versioning should be done in the ACCEPT headers not in the switch.
	And your implementation could check the version in object->http_accept.
	E.g.:
		Accept: "Content-type:application/json;version=1"

	All sub-apis should be housed in apis/ subdirectory.

		Return responses by calling object->send_encoded_response() from your implementation,
	with an array as the parameter and optional code.

	Apache will be modded so that api.php can be called without the php extension.

*/





  /////////////////////////////////////////////////////////////////////////////////////////////////
 //						FUNCTIONS					//
/////////////////////////////////////////////////////////////////////////////////////////////////



// move the array into the object.
function object_assign_or_fail( $array, $property, $failure_code = 400 ) {

	global $current_request;

	if ( ! empty( $array[$property] ) ) {
		if ( property_exists( 'APIRequest', $property ) ) {
			$current_request->$property = $array[$property];
		}
		return true;
	} else {
		$current_request->send_response_exit( $failure_code );
	}
}


// See if the request has known authentication methods.
function has_alternate_auth() {

	global $current_request;

	// basic auth
	if ( ! empty( $_SERVER['PHP_AUTH_USER'] ) && ! empty( $_SERVER['PHP_AUTH_PW'] ) ) {
		$current_request->user_id = $_SERVER['PHP_AUTH_USER'];
		$current_request->password = $_SERVER['PHP_AUTH_PW'];
		return true;
	}
	// make use of OAUTH here
	// NOTE: PHP has methods for OAUTH
	return false;
}






  /////////////////////////////////////////////////////////////////////////////////////////////////
 //						SETUP						//
/////////////////////////////////////////////////////////////////////////////////////////////////



// NOTE:  split out $_SERVER['PATH_INFO'] to use the URI to identify appropriate app.


// Debug and db choice.
$DEBUG = false;
// TODO: Change for production
if ( file_exists( '/u/system_test_flag' ) ) {
        $cursor = @test_db_connect();
        $inTest = "@REAL_DB_LINK";
} else {
        $cursor = @real_db_connect();
        $inTest = "";
}


//// Setup the request object
$current_request = new APIRequest();
$current_request->cursor = $cursor;
$current_request->inTest = $inTest;
$current_request->DEBUG = $DEBUG;

// immediately fail if there is no CONTENT_TYPE
if ( empty( $_SERVER['CONTENT_TYPE'] ) ) {
	$current_request->send_response_exit();
}


// I think only GET and POST are necessary at this time.
$current_request->http_method = ( ! empty( $_SERVER['REQUEST_METHOD'] ) && in_array( $_SERVER['REQUEST_METHOD'], array( 'GET', 'POST' ) ) ) ? $_SERVER['REQUEST_METHOD'] : $current_request->send_response_exit();
// can capture versioning here. E.g. "Accept: application/json
$current_request->http_accept = ( ! empty( $_SERVER['HTTP_ACCEPT'] ) ) 	  ? $_SERVER['HTTP_ACCEPT'] : "Accept: application/json, application/xml";


// using this rather than $HTTP_RAW_POST_DATA. Putting it into the object so it can be looked at downstream if necessary.
// 	NOTE: If the request is too large, this may need to be changed to bite the file into chunks and save to php://temp.
$current_request->http_raw = file_get_contents("php://input");








  /////////////////////////////////////////////////////////////////////////////////////////////////
 //					JSON/XML DECODING					//
/////////////////////////////////////////////////////////////////////////////////////////////////



$http_request_content = array();
switch ( $_SERVER['CONTENT_TYPE'] ) {


	// attempt to parse the request data for JSON into Associative array
	case "application/json"	:
		
		$current_request->content_type = "application/json";

		if ( $current_request->http_method == 'POST' ) { 
			// NOTE: recursion limit is set to 5 which we should need to exceed with current data model. See example below.
			$http_request_content = json_decode( $current_request->http_raw, true, 5 );
		}
		break;


	// attempt to parse the request data for XML
	case "application/xml"	:

		$current_request->content_type = "application/xml";

		if ( $current_request->http_method == 'POST' ) {

			// if the first line doesn't have the encoding set then reject.
			// 	if the encoding isn't one that PHP will let us set, then reject ( ISO-8859-1, US-ASCII and UTF-8 ).
			$parser = xml_parser_create();
	
			preg_match( '/<?xml.*encoding="(.*)".*?>/', (string)$current_request->http_raw, $xml_encoding );
			if ( count( $xml_encoding ) > 1 ) {
				$xml_encoding = $xml_encoding[1];
			} else {
				$current_request->send_response_exit();
			}
			if ( ! in_array( $xml_encoding, array( 'ISO-8859-1', 'US-ASCII', 'UTF-8' ) ) ) {
				$current_request->send_response_exit();
			}
			$current_request->encoding = $xml_encoding;
			// decode using the same format given. 
			xml_parser_set_option( $parser, XML_OPTION_TARGET_ENCODING, $xml_encoding );
			// Do not change case of tags, this is default but setting explictly so you know.
			xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 );
			// Dont bother with empty info
			xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 1 );
			xml_parse_into_struct( $parser, $current_request->http_raw, $tags_array );
			xml_parser_free( $parser );
			
			$elements = array();
			$stack = array();
			foreach ( $tags_array as $tag ) {
				// push new elements with their tag names as the array key.
				if ( $tag['type'] == "complete" || $tag['type'] == "open" ) {
					if ( $tag['type'] == "open" ) {
						$elements[$tag['tag']] = array();
						$stack[count( $stack )] = &$elements;
						$elements = &$elements[$tag['tag']];
					} else {
						$elements[$tag['tag']] = $tag['value'];
					}
				}
				// pop off the last element.
				if ( $tag['type'] == "close" ) {
					$elements = &$stack[count( $stack ) - 1];
					unset( $stack[count( $stack ) - 1] );
				}
			}
			$http_request_content = $elements;
		}
		break;


	// Error back the the client if the request is neither JSON nor XML.
	default:

		$current_request->send_response_exit();
}






  /////////////////////////////////////////////////////////////////////////////////////////////////
 //					ARRAY MAPPING						//
/////////////////////////////////////////////////////////////////////////////////////////////////



$request_headers = apache_request_headers();
// Remove wrappers, reassign just the necessary data back to the array. If the data isn't set up
// 	correctly or is empty, exit.
if ( $current_request->http_method == 'POST' && object_assign_or_fail( $http_request_content, 'cl-info' ) ) {

	$http_request_content = $http_request_content['cl-info'];

	if ( ! has_alternate_auth() ) {
		if ( object_assign_or_fail( $http_request_content, 'authentication', 401 ) ) {
			$auth_content = $http_request_content['authentication'];
			object_assign_or_fail( $auth_content, 'user_id', 401 );
			object_assign_or_fail( $auth_content, 'password', 401 );
		}
	}

	if ( object_assign_or_fail( $http_request_content, 'request' ) ) {
		$http_request_content = $http_request_content['request'];
		object_assign_or_fail( $http_request_content, 'request_name' );
		if ( $current_request->http_method == 'POST' ) { 
			object_assign_or_fail( $http_request_content, 'request_content'	);
		}
	}
// using temporary variable assignment because of a restriction in php 5.3 which doesn't allow indexing an array returned by a function.
//	e.g. my_call()[1]
} elseif ( $current_request->http_method == 'GET' && has_alternate_auth() && ! empty( $request_headers['request-type'] ) ) {
	$current_request->request_name = $request_headers['request-type'];
} else {
	$current_request->send_response_exit( 401 );
}






  /////////////////////////////////////////////////////////////////////////////////////////////////
 //					INTERNAL AUTH						//
/////////////////////////////////////////////////////////////////////////////////////////////////



// Authenticate the user or exit ( failure exits 401 ).
$current_request->verify_user();
// TODO: MAYBE. check that the client is enrolled in the product prior to sending them over to it.






  /////////////////////////////////////////////////////////////////////////////////////////////////
 //				 MAIN SWTICH. Add your API here. 				//
/////////////////////////////////////////////////////////////////////////////////////////////////



// COULD SWITCH TO URI USE HERE.
// 	Try not to add more variables to this list. Pass to your API if necessary. 
$api_choice = explode( '/', $current_request->request_name );


// Your API should accept the $current_request object and use it's methods to return data to the client.
switch ( $api_choice[0] ) {

	// TODO: remove this test case and code
	case "test_me":
		require_once "apis/cl_api_test.php";
		test_me( $current_request );
		break;

	case "payors":
		if ( count( $api_choice ) > 1 ) {
			require_once "apis/payor_api.php";
			// function switching should probably be handled in the file itself.
			choose_function( $api_choice[1],  $current_request );
		} else {
			$current_request->send_response_exit();
		}
		break;

	default:

		$current_request->send_response_exit();
}






  /////////////////////////////////////////////////////////////////////////////////////////////////
 //					Some DEBUG output	 				//
/////////////////////////////////////////////////////////////////////////////////////////////////



if ( $DEBUG ) {
	// just using to verify that the API setup everything correctly.
	var_dump( $http_request_content );
	print_r( $HTTP_RAW_POST_DATA );
	print_r( $_SERVER );
	print_r( $_REQUEST );
	print_r( apache_request_headers() );
}

