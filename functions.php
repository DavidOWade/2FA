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
	$mysqli_result = mysqli_query($conn, $query);

	$row = mysqli_fetch_row($mysqli_result);

	if (password_verify($password, $row[4])) {
		$user_id = $row[0];

		$device = checkDevice($user_id, $conn);

		if ($device['success'] == 'true') {
			$result['success'] = 'true';
		} else {
			$device_id = $device['deviceId'];
			$otp = generateOneTimePw();
			$query = "INSERT INTO one_time_password(UserId, DeviceId, OTP) VALUES('" . $user_id . "', '" . $device_id ."', '" . $otp . "');";
			mysqli_query($conn, $query); // Insert OTP to database
			$result['error'] = 'devicenotfound';
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
	$mysqli_result = mysqli_query($conn, $query);
	$row = mysqli_fetch_row($mysqli_result);

	$num_accounts = intval($row[0]);

	if ($num_accounts > 0) {
		$result['error'] = 'usernametaken';
		echo json_encode($result);
		closeDB($conn);
		exit();
	}

	$query = "SELECT COUNT(Id) AS COUNT FROM users WHERE Email='" . $email . "';";
	$mysqli_result = mysqli_query($conn, $query);
	$row = mysqli_fetch_row($mysqli_result);

	$num_emails = intval($row[0]);

	if ($num_emails > 0) {
		$result['error'] = 'emailtaken';
		echo json_encode($result);
		closeDB($conn);
		exit();
	}

	$password_hashed = password_hash($password, PASSWORD_DEFAULT);

	$query = "INSERT INTO users (Username, Email, Phone, Password) VALUES ('" . $username . "','" . $email . "','" . $phone . "','" . $password_hashed . "');";
	mysqli_query($conn, $query);

	$result['success'] = 'true';
	$resiult['error'] = 'false';
	echo json_encode($result);

	closeDB($conn);
	exit();

}

function checkDevice($user_id, $conn) {
	$result = array();
	$result['success'] = 'false';

	if (isset($_COOKIE['deviceId'])) {
		$device_id = $_COOKIE['deviceId'];

		$query = "SELECT * FROM allowed_devices WHERE UserId='" . $user_id . "' AND DeviceId='" . $device_id . "';";
		$mysqli_result = mysqli_query($conn, $query);

		if ($mysqli_result->num_rows == 0) {
			$result['deviceId'] = $device_id;
			return $result;
		}

		$row = mysqli_fetch_row($mysqli_result);

		if ($row[0] == $user_id and $row[1] == $device_id) {
			$result['success'] = 'true';
			$result['deviceId'] = $device_id;
			return $result;
		} else {
			return $result;
		}
	} else {
		$str = rand();
		$hash = md5($str);
		setcookie('deviceId', $hash, time() + (86400 * 30));
		setcookie('attemptedUserLoginId', $user_id, time() + (86400 * 30));
		$result['deviceId'] = $hash;
		return $result;
	}
}

function verifyOtp($get) {
	$result = array();
	$result['success'] = 'false';

	if (isset($_COOKIE['deviceId']) and isset($_COOKIE['attemptedUserLoginId'])) {
		$user_id = $_COOKIE['attemptedUserLoginId'];
		$device_id = $_COOKIE['deviceId'];
		$otp = $_GET['otp'];

		$conn = openDB();

		$query = "SELECT COUNT(UserId) AS MatchExists FROM one_time_password WHERE UserId='" . $user_id . "' AND DeviceId='" . $device_id . "' AND OTP='" . $otp . "';";
		$mysqli_result = mysqli_query($conn, $query);

		if ($mysqli_result->num_rows == 0) {
			$result['error'] = 'nomatch';
			echo json_encode($result);
			closeDB($conn);
			exit();
		}

		$row = mysqli_fetch_row($mysqli_result);

		if (intval($row[0]) == 1) {
			$result['error'] = 'false';
			$result['success'] = 'true';
		} else {
			$result['error'] = 'nomatch';
		}

		if ($result['success'] == 'true') {
			$query = "DELETE FROM one_time_password WHERE UserId='" . $user_id . "' AND DeviceId='" . $device_id . "' AND OTP='" . $otp . "';";
			mysqli_query($conn, $query);
			$query = "INSERT INTO allowed_devices (UserId, DeviceId) VALUES ('" . $user_id . "', '" . $device_id . "');";
			mysqli_query($conn, $query);
		}

		echo json_encode($result);
		closeDB($conn);
		exit();
	}
}

function generateOneTimePw() {
	return rand(100000,999999);
}



?>