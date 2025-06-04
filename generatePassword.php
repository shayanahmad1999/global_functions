<?php

function generatePassword($length): string
{
	$characters = str_split('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

	$password = '';
	$password .= $characters[rand(10, 35)];
	$password .= $characters[rand(36, 61)];
	$password .= $characters[rand(0, 9)];

	while (strlen($password) < $length) {
		$password .= $characters[array_rand($characters)];
	}

	return $password;
}

echo generatePassword(15);

exit;

?>
