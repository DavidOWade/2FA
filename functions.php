<?php
function openDB() {
	return new mysqli("localhost", "root", "", "2FA");
}

function closeDB($db) {
	$db->close();
}


function handleLogin($get) {
	$username = $get['username'];
	$password = $get['password'];

	$result = array();
	$result['success'] = 'false';

	$conn = openDB();

	$query = "SELECT * FROM users WHERE Username='" . $username . "';";
	$result = mysqli_query($conn, $query);

	$row = mysqli_fetch_row($result);

	if (password_verify($password, $row[4])) {
		$user_id = $row[0];
		$result = verifyDevice($user_id, $conn);
		if ($result) {
			$result['success'] = 'true';
			$result['error'] = 'false';
		} else {
			$result['error'] = 'devicenotfound';
			generateOneTimePw();
		}
	} else {
	    $result['error'] = 'pwerror';

	}

	echo json_encode($result);

	closeDB($conn);
	exit();
}

function handleSignup($get) {
	$username = $get['username'];
	$email = $get['email'];
	$phone = $get['phone'];
	$password = $get['password'];
	$password2 = $get['password2'];

	$result = array();
	$result['success'] = 'false';

	if ($password !== $password2) {
		$result['error'] = 'pwdsdontmatch';
		echo json_encode($result);
		exit();
	}

	if (strlen($password) < 7) {
		$result['error'] = 'pwshort';
		echo json_encode($result);
		exit();
	}

	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$result['error'] = 'emailnotvalid';
		echo json_encode($result);
		exit();
	}

	if (strlen($username) == 0) {
		$result['error'] = 'usernamenotvalid';
		echo json_encode($result);
		exit();
	}

	$conn = openDB();

	$query = "SELECT COUNT(Id) AS COUNT FROM users WHERE Username='" . $username . "';";
	$result = mysqli_query($conn, $query);
	$row = mysqli_fetch_row($result);

	$num_accounts = intval($row[0]);

	if ($num_accounts > 0) {
		$result['error'] = 'usernametaken';
		echo json_encode($result);
		closeDB($conn);
		exit();
	}

	$query = "SELECT COUNT(Id) AS COUNT FROM users WHERE Email='" . $email . "';";
	$result = mysqli_query($conn, $query);
	$row = mysqli_fetch_row($result);

	$num_emails = intval($row[0]);

	if ($num_emails > 0) {
		$result['error'] = 'emailtaken';
		echo json_encode($result);
		closeDB($conn);
		exit();
	}

	$password_hashed = password_hash($password, PASSWORD_DEFAULT);

	$query = "INSERT INTO users (Username, Email, Phone, Password) VALUES ('" . $username . "','" . $email . "','" . $phone . "','" . $password_hashed . "');";
	$result = mysqli_query($conn, $query);

	$result['success'] = 'true';
	$resiult['error'] = 'false';
	echo json_encode($result);

	closeDB($conn);
	exit();

}

function verifyDevice($user_id, $conn) {
	if (isset($_COOKIE['deviceId'])) {
		$device_id = $_COOKIE['deviceId'];

		$query = "SELECT * FROM allowed_devices WHERE DeviceId='" . $device_id . "';";
		$result = mysqli_query($conn, $query);

		if ($result->num_rows == 0) {
			return false;
		}

		$row = mysqli_fetch_row($result);

		if ($row[0] == $user_id and $row[1] == $device_id) {
			return true;
		} else {
			return false;
		}
	} else {
		$str = rand();
		$hash = md5($str);
		setcookie('deviceId', $hash, 86400);
		return false;
	}
}

function generateOneTimePassword() {
	
}



?>