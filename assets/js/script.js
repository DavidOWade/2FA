function switchLoginTab(option) {
	var login = document.getElementById('login');
	var signup = document.getElementById('signup');

	var loginForm = document.getElementById('loginForm');
	var signupForm = document.getElementById('signupForm');
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
	  	const response = this.response;
	  	if (response == 'dberror') {

	  	} else if (response == 'pwerror') {

	  	} else if (response == '1') {
	  		
	  	}
	  }
	};
	var get = "";
	if (option == 'login') {
		const username = document.getElementById('username').value;
		const password = document.getElementById('password').value;
		get = "?action=login&username=" + username + "&password=" + password;
	} else if (option == 'signup') {
		const email = document.getElementById('emailSignup').value;
		const username = document.getElementById('usernameSignup').value;
		const phone = document.getElementById('phone').value;
		const password = document.getElementById('passwordSignup1').value;
		const password2 = document.getElementById('passwordSignup2').value;
		get = "?action=signup&email=" + email + "&username=" + username + "&password=" + password + "&password2=" + password2
	}
	xmlhttp.open("GET", get, true);
	xmlhttp.send();
}


