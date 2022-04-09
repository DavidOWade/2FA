<?php

require_once('functions.php');

if (isset($_GET['verifyId']) and isset($_GET['email'])) {
	$success = 'false';

	$email = $_GET['email'];
	$verify_id = $_GET['verifyId'];

	$conn = openDB();

	$query = "SELECT * FROM users WHERE VerifyId='" . $verify_id . "';";

	$mysqli_result = mysqli_query($conn, $query);

	if ($mysqli_result->num_rows == 1) {
		$row = mysqli_fetch_row($mysqli_result);
		if ($row[2] == $email) {
			$success = 'true';
			$query = "UPDATE users SET Active='1' WHERE Email='" . $email . "' AND VerifyId='" . $verify_id . "';";
			mysqli_query($conn, $query);
		}
	}

	closeDB($conn);

	header("Location: /2FA/index.php?verifySuccess=" . $success);
	die();
}