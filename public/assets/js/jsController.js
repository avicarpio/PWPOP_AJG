
function jsRegister() {
    var name = document.forms["regiser-form"]["name"];
    var username = document.forms["regiser-form"]["username"];
    var email = document.forms["regiser-form"]["email"];
    var phone = document.forms["regiser-form"]["phone"];
    var password = document.forms["regiser-form"]["password"];
    var passwordConfirm = document.forms["regiser-form"]["passwordConfirm"];

    if (name.value == "") {
        document.getElementById('errorName').innerHTML = "Please enter your name.";
        return false;
    }else {
        document.getElementById('errorName').innerHTML = ("");
    }

    if (username.value == "") {
        document.getElementById('errorUsername').innerHTML = "Please enter your username.";
        name.focus();
        return false;
    }else {
        document.getElementById('errorUsername').innerHTML = ("");
    }

    if (email.value == "") {
        document.getElementById('errorEmail').innerHTML = "Please enter your email";
        email.focus();
        return false;
    }else {
        document.getElementById('errorEmail').innerHTML = ("");
    }

    if (email.value.indexOf("@", 0) < 0) {
        document.getElementById('errorEmail').innerHTML = ("Wrong email without @");
        email.focus();
        return false;
    }else {
        document.getElementById('errorEmail').innerHTML = ("");
    }

    if (email.value.indexOf(".", 0) < 0) {
        document.getElementById('errorEmail').innerHTML = ("Wrong email");
        email.focus();
        return false;
    }else {
        document.getElementById('errorEmail').innerHTML = ("");
    }

    if (phone.value == "") {
        document.getElementById('errorPhone').innerHTML = ("Please enter your Phone");
        phone.focus();
        return false;
    }else {
        document.getElementById('errorPhone').innerHTML = ("");
    }

    if (password.value == "") {
        document.getElementById('errorPass').innerHTML = ("Please enter your Password");
        password.focus();
        return false;
    }else {
        document.getElementById('errorPass').innerHTML = ("");
    }



    if (passwordConfirm.value == "") {
        document.getElementById('errorPass2').innerHTML = ("Please repeat your Password");
        password.focus();
        return false;
    }else {
        document.getElementById('errorPass2').innerHTML = ("");
    }
    return true;
}



function jsLogin() {

    var email = document.forms["login-form"]["email"];
    var password = document.forms["login-form"]["password"];


    if (email.value == "") {
        document.getElementById('errorEmail').innerHTML = "Please enter a valid e-mail address.";
        email.focus();
        return false;
    }else {
        document.getElementById('errorEmail').innerHTML = ("");
    }

    if (email.value.indexOf("@", 0) < 0) {
        document.getElementById('errorEmail').innerHTML = "Email without @!!!?";
        email.focus();
        return false;
    }else {
        document.getElementById('errorEmail').innerHTML = ("");
    }

    if (email.value.indexOf(".", 0) < 0) {
        document.getElementById('errorEmail').innerHTML = "Please enter a valid e-mail address.";
        email.focus();
        return false;
    }else {
        document.getElementById('errorEmail').innerHTML = ("");
    }


    if (password.value == "") {
        console.log('hola');
        document.getElementById('errorPass').innerHTML = "Please enter a valid password.";
        password.focus();
        return false;
    }else {
        document.getElementById('errorPass').innerHTML = ("");
    }

    return true;
}




function jsEditProfile() {

    var name = document.forms["update-form"]["name"];
    var email = document.forms["update-form"]["email"];
    var phone = document.forms["update-form"]["phone"];

    if (name.value == "") {
        document.getElementById('errorName').innerHTML = "Please enter a Name";
        name.focus();
        return false;
    }else {
        document.getElementById('errorName').innerHTML = ("");
    }

    if (email.value == "") {
        document.getElementById('errorEmail').innerHTML = "Please enter a valid e-mail address.";
        email.focus();
        return false;
    }else {
        document.getElementById('errorEmail').innerHTML = ("");
    }

    if (email.value.indexOf("@", 0) < 0) {
        document.getElementById('errorEmail').innerHTML = "Email without @!!!?";
        email.focus();
        return false;
    }else {
        document.getElementById('errorEmail').innerHTML = ("");
    }

    if (email.value.indexOf(".", 0) < 0) {
        document.getElementById('errorEmail').innerHTML = "Please enter a valid e-mail address.";
        email.focus();
        return false;
    }else {
        document.getElementById('errorEmail').innerHTML = ("");
    }


    if (phone.value == "") {
        document.getElementById('errorPhone').innerHTML = "Please enter a Phone.";
        phone.focus();
        return false;
    }else {
        document.getElementById('errorPhone').innerHTML = ("");
    }

    return true;
}