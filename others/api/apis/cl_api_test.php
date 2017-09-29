<?php

// simple test that uses the objects decoded data ( an array ) and returns encoded data.
function test_me( $object ) {
	
	// just pass back the decoded data so it can be built back up.	
	$object->send_encoded_response( $object->request_content );
}

?>
