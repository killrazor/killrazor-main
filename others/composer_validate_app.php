<?php

require __DIR__ . '/../../vendor/autoload.php';

use Respect\Validation\Validator;
use Respect\Validation\Exceptions\NestedValidationException;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$date = trim($_POST['date']);
	$email = trim($_POST['email']);
	$desc = trim($_POST['desc']);

	$date_validator = Validator::notEmpty()->date();
	$email_validator = Validator::email()->notEmpty();
	$desc_validator = Validator::stringType()->length(1,750);

	try {
		$date_validator->assert($date);
		$email_validator->assert($email);
		$desc_validator->assert($desc);

		echo date('Ymd', strtotime($date));
		echo $email;
		echo $desc;
	} catch (NestedValidationException $e) {
		echo '<ul';
		foreach ($e->getMessages() as $message) {
			echo "<li>$message</li>";
		}
		echo '</ul>';
	}

	echo htmlspecialchars($reason);
}

?>
