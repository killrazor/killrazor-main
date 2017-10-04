<?php

// expects that you have composer respect/validate package installed
require __DIR__ . '/../../vendor/autoload.php';

// expects that you are using composer
use Respect\Validation\Validator;

//$v = new Validator;

function validate_date($date_string) {

	
	try {
		// static call -- no object has to be created.
		$date_validator = Validator::date()->notEmpty();

		$date_validator->assert($date_string);
		$date_time = strtotime($date_string);
		return date('Ymd', $date_time);
	} catch (NestedValidationException $e) {
		return $e->getMessages();
	}
}

?>
