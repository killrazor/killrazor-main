<?php 

/* PSR - PHP Standards Recommendations ( http://www.php-fig.org/psr/ )
 *	Best practices:
 *		check that variables exist	via empty($var)
 *		remove whitespace		via trim($var)
 *		sanitize output			via htmlspecialchars($var)
 *		vailidate email addresses	via filter_var($email, FILTER_VALIDATE_EMAIL)
 *		validate dates			via strtotime($var)
 *		convert dates			via date('ymd', $epoch)
 */


  /////////////////////////////
 // Best practice functions //
/////////////////////////////


function validate_date($date_string) {
	if( $time = strtotime($date_string) {
		return date('ymd', $time);
	}
}

function convert_date($epoch) {
	return date('ymd', $epoch);
}

function sanitaize($output) {
	return htmlspecialchars($output);
}

function validate_email($email) {
	if (filter_var( $email, FILTER_VALIDATE_EMAIL)) {
		return $email;
	}
}

?>

