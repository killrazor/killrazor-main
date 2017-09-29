<?php


////////////////////////////////////////////////////////////////
//
//			Payor API			
//
//		If addions are made here, be sure that they are
//	added to the switching function.
//
////////////////////////////////////////////////////////////////


// Add new functions here. 
function choose_function( $function_name, $object ) {

	$accepted_functions = array( 
		'payor_list',
		'ym_payors'
	);
	if ( $object->http_method == 'GET' && in_array( $function_name, $accepted_functions ) ) {
		$function_name( $object );
	} else {
		$object->send_response_exit();
	}
}


// TODO: Just examples. Remove me?
// Example of executing the query yourself and sending back to the class to encode and respond.
function payor_list( $object ) {

	$cursor = $object->cursor;
	$query = "SELECT * FROM YOUR_PAYOR_TABLE";
	$statement = oci_pexec( $cursor, $query );
	$result_array = array();
	while ( ( $result = oci_fetch_assoc( $statement ) ) !== false ) {
		array_push( $result_array, $result );
	}
	$object->send_encoded_response( $result_array );
}


// TODO: Just examples. Remove me?
// Example of getting your own cursor and then letting the Class handle the execution and response.
function ym_payors( $object ) {

	$cursor = @real_connect_alt_db();
	$account = $object->user_id;
	$current_ym = date( "Ym" );
	$query = "SELECT UNIQUE PAYOR_ID FROM YOUR_PAYOR_DB_$current_ym WHERE USER_ID = '$user_id'";
	$object->pef_encoded_response( $cursor, $query );
}
?>
