<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once "${path}/php/configs/db_config.php";
require_once "${path}/php/utils/helpers.php";

if ( $_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['action']) ) {
	exit;
}	

/* NOTES:
 * 	These functions are to be accessed via POST data commonly by jQuery AJAX.
 */

$DEGBUG=false;

if ( $DEBUG ) {
	error_reporting(E_ALL);
	ini_set('display_errors', 'on');
}

if ( file_exists( '/u/system_test_flag' ) ) {
	$db_conn = @test_db_connect();
	$inTest = "@REAL_DB_LINK";
} else {
	$db_conn = @real_db_connect();
	$inTest = "";
}

$mock_global = ( empty( $_POST['mock_var'] ) )	? false	: $_POST['mock_var'];


switch ( $_POST['action'] ) {
	case "mockCallWithParm"	: some_other_name( "some_parm" );	break;
	case "mockCall"		: more_of_the_same();			break;
}


  ///////////////////
 // Function List //
///////////////////


// mock function that does nothing
function some_other_name($param) {
	global $db_conn, $inTest;
	echo jsonEncodeSelect( "
		SELECT Count(*)
		FROM   MOCK_TABLE$inTest
		WHERE  tag = 'something_you_need'
	", $db_conn );
}

// mock function with no parm
function more_of_the_same() {
	global $db_conn, $inTest;
	echo jsonEncodeSelect( "
		SELECT ds.*,
		       po.something_specific
		FROM   MOCK_TABLE_2 ds,
		       MOCK_TABLE_3$inTest po
		WHERE  ds.mt2_info = po.mt3_info
	", $db_conn );
}

?>
