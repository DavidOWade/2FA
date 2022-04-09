<?php

function openDB() {
	$dbhost = "localhost";
 	$dbuser = "root";
 	$dbpass = "";
 	$db = "2FA";
 	$conn = new mysqli($dbhost, $dbuser, $dbpass,$db) or die("Connect failed: %s\n". $conn -> error);
 
 	return $conn;
 }
 
function closeDB($conn) {
	$conn -> close();
}

function handleLogin($get) {
	$username = $get['username'];
	$password = $get['password'];
	$password_hashed = password_hash($password, PASSWORD_DEFAULT);

	$db = openDB();

	$query = "SELECT * FROM users WHERE Username='" . $username . "' AND Password='" . "';";

	$result = $db->query($query);

	if ($rows->num_rows !== 1) {
		echo 'dberror';
		die();
	}

	$account = $result->fetch_assoc()[0];

	if ($password_hashed !== $account["Password"]) {
		echo 'pwerror';
		die();
	}

	echo '1';

	die();
}




?>