<?php

require_once('functions.php');

if (isset($_GET['action'])) {
	$action = $_GET['action'];

	if ($action == 'login') {
		handleLogin($_GET);
	}

	if ($action == 'signup') {
		handleSignup($_GET);
	}
}




?>