<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function openDB() {
	$config_file = file_get_contents("config.json");
	$config = json_decode($config_file, true)["mysqli"];

	return new mysqli($config["host"], $config["username"], $config["password"], $config["database"]);
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

	if ($row[5] == '0') {
		$result['error'] = 'emailnotverified';
	} else if (password_verify($password, $row[4])) {
		$user_id = $row[0];

		$device = checkDevice($user_id, $conn);

		$phone = $row[3];

		if ($device['success'] == 'true') {
			$result['success'] = 'true';
		} else {
			$device_id = $device['deviceId'];
			$otp = generateOneTimePw();
			$query = "INSERT INTO one_time_password(UserId, DeviceId, OTP) VALUES('" . $user_id . "', '" . $device_id ."', '" . $otp . "');";
			mysqli_query($conn, $query); // Insert OTP to database
			$result['error'] = 'devicenotfound';

			send_otp_sms_code($phone, $otp);
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

	$verify_id = strval(generateOneTimePw());

	$verify_sent = verify_email($username, $email, $verify_id);

	if ($verify_sent) {
		$password_hashed = password_hash($password, PASSWORD_DEFAULT);

		$query = "INSERT INTO users (Username, Email, Phone, Password, Active, VerifyId) VALUES ('" . $username . "','" . $email . "','" . $phone . "','" . $password_hashed . "', '0', '" . $verify_id . "');";
		mysqli_query($conn, $query);

		$result['success'] = 'true';
		$result['error'] = 'false';
	} else {
		$result['error'] = 'emailnotsent';
	}

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

function send_mail($address, $subject, $body) {
	$config_file = file_get_contents("config.json");
	$config = json_decode($config_file, true);

	$mail = new PHPMailer();

	try {
   		//Server settings
	    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
	    $mail->isSMTP();                                            //Send using SMTP
	    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
	    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
	    $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
	    $mail->Username   = $config["smtp"]["username"];                     //SMTP username
	    $mail->Password   = $config["smtp"]["password"];                               //SMTP password
	    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
	    $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

	    //Recipients
	    $mail->setFrom($config["smtp"]["username"], 'David');
	    $mail->addAddress($address);               //Name is optional
	    $mail->addReplyTo($config["smtp"]["username"], 'David');

	    //Content
	    $mail->isHTML(true);                                  //Set email format to HTML
	    $mail->Subject = $subject;
	    $mail->Body    = $body;
	    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

	    $mail->send();
	    return true;
	} catch (Exception $e) {
	    return false;
	}
}

function verify_email($username, $email, $verify_id) {
	$host = gethostname();
	$link = gethostbyname($host);

	$subj = "Your account verification";

	$body = 'Hello ' . $username . ',

	<p>This email was registered to our app.</p>

	<p>Follow <a href="https://' . $link . '/2FA/verifyEmail.php?email=' . urlencode($email) . '&verifyId=' . $verify_id . '">this</a> link to verify your account:</p>

	- Some really secure app';

	return send_mail($email, $subj, $body);
}


function send_sms($phone, $body) {
	$config_file = file_get_contents("config.json");
	$config = json_decode($config_file, true);

	// Configure HTTP basic authorization: BasicAuth
	$config = ClickSend\Configuration::getDefaultConfiguration()
	              ->setUsername($config["clicksend"]["username"])
	              ->setPassword($config["clicksend"]["password"]);

	$apiInstance = new ClickSend\Api\SMSApi(new GuzzleHttp\Client(),$config);
	$msg = new \ClickSend\Model\SmsMessage();
	$msg->setBody($body); 
	$msg->setTo($phone);
	$msg->setSource("sdk");

	// \ClickSend\Model\SmsMessageCollection | SmsMessageCollection model
	$sms_messages = new \ClickSend\Model\SmsMessageCollection(); 
	$sms_messages->setMessages([$msg]);

	try {
	    $result = $apiInstance->smsSendPost($sms_messages);
	    print_r($result);
	    return true;
	} catch (Exception $e) {
	    return false;
	}
}

function send_otp_sms_code($phone, $otp) {
	$body = "Your verification code: " . $otp;
	send_sms($phone, $body);
}




?>