function switchLoginTab(option) {
	var login = document.getElementById('login');
	var signup = document.getElementById('signup');

	var loginForm = document.getElementById('loginForm');
	var signupForm = document.getElementById('signupForm');

	const errorMsg = document.getElementById('errorMessage');
	if (option == 'login') {
		login.style.textDecoration= 'underline #362b29 2px';
		signup.style.textDecoration = 'none';
		signupForm.style.display = 'none';
		loginForm.style.display = 'initial';
	} else if (option == 'signup') {
		signup.style.textDecoration= 'underline #362b29 2px';
		login.style.textDecoration = 'none';
		loginForm.style.display = 'none';
		signupForm.style.display = 'initial';
	}

	errorMsg.innerHTML = '';
}

function togglePassword(id) {
	var password = document.getElementById(id);
	const type = password.getAttribute("type") === "password" ? "text" : "password";
	password.setAttribute("type", type);
}

function ajax(option) {
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function() {
	  if (this.readyState == 4 && this.status == 200) {
	  	const response = JSON.parse(this.response);
	  	console.log(response);
	  	const errorMsg = document.getElementById('errorMessage');

	  	if (response.success == 'true') {
	  		const welcome = document.getElementById('welcome');
	  		welcome.style.display = 'initial';
	  		const frontForm = document.getElementById('frontForm');
	  		frontForm.style.display = 'none';
	  	}

	  	if (response.error !== 'false') {
	  		if (response.error == 'devicenotfound') {
	  			const verifyDevice = document.getElementById('verifyDevice');
		  		verifyDevice.style.display = 'initial';
		  		const frontForm = document.getElementById('frontForm');
		  		frontForm.style.display = 'none';
	  		} else {
	  			errorMsg.innerHTML = getErrorMessage(response.error);
	  		}
	  	}
	  }
	};
	var get = "handleForms.php";
	if (option == 'login') {
		const username = document.getElementById('username').value;
		const password = document.getElementById('password').value;
		get = get + "?action=login&username=" + username + "&password=" + password;
	} else if (option == 'signup') {
		const email = document.getElementById('emailSignup').value;
		const username = document.getElementById('usernameSignup').value;
		const phone = document.getElementById('phone').value;
		const password = document.getElementById('passwordSignup1').value;
		const password2 = document.getElementById('passwordSignup2').value;
		get = get + "?action=signup&email=" + email + "&username=" + username + "&phone=" + phone + "&password=" + password + "&password2=" + password2
	}
	xmlhttp.open("GET", get, true);
	xmlhttp.send();
}

function getErrorMessage(code) {
	switch (code) {
		case 'pwerror':
			return 'Invalid username or password';
			break;
		case 'pwdsdontmatch':
		    return 'Passwords don\'t match';
		    break;
		case 'pwshort':
		    return 'Password must be at least 7 characters long';
		    break;
		case 'usernamenotvalid':
			return 'Please enter a username';
			break;
		case 'emailnotvalid':
			return 'Email not valid';
			break;
		case 'usernametaken':
			return 'That username is already taken'
			break;
		case 'emailtaken':
			return 'That email is already taken';
			break;
	}
}

