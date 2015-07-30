/* Defaults
 ******************************************************************************/
var API_URL = 'https://api.soccerwars.xyz';

// Set popup window default options
swal.setDefaults({
    showCancelButton: false,
    closeOnConfirm: false,
    animation: false,
    customClass: 'popup'
});

// Set default headers to be sent with every request
$.ajaxSetup({
    beforeSend: function(xhr) {
        xhr.setRequestHeader('Content-Type', 'application/json');
    }
});

// Show appropriate buttons depending on whether the user has already logged in
if (Cookies.get('token')) {
    $("#play-button").show();
} else {
    $("#login-button, #signup-button").show();
}


/* Modal windows
 ******************************************************************************/
$("#login-button").click(function() {
    swal({
            title: "Login",
            text: $('#login-form').html(),
            html: true,
            showCancelButton: true,
            confirmButtonText: 'Submit',
            showLoaderOnConfirm: true
        },
        function(isConfirm){
            if (isConfirm) {
                var email = $.trim($("#login-email").val());
                var password = $.trim($("#login-password").val());

                if (email == '' || password == '') {
                    swal.showInputError("You need to enter your credentials");
                } else {
                    $.ajax(API_URL + '/login', {
                        type: 'POST',
                        data: JSON.stringify({
                            "email": email,
                            "password": password
                        })
                    })
                        .done(function(response) {
                            Cookies.set('token', response.token, {expires: 3600 * 24 * 7, secure: true});
                            swal({
                                title: "Greetings, " + "<img src='"+ response.avatar.small + "'> " + response.name + "!",
                                html: true,
                                text: "Redirecting you do your Dashboard...",
                                type: "success",
                                showConfirmButton: false
                            });
                            setTimeout(function(){
                                window.location.href = '/game/';
                            }, 3000);
                        })
                        .fail(function(response) {
                            var message = response.responseJSON;
                            swal.showInputError(message.error);
                        })
                        .always(function(response){
                            console.log(response);
                        });
                }
            }
        }
    );
    scrambleText('h2');
    $(':input[autofocus]').focus();
    $("#reset-link a").click(reset_click);
});

$("#signup-button").click(function() {
    swal({
            title: "Sign Up",
            text: $("#signup-form").html(),
            html: true,
            showCancelButton: true,
            confirmButtonText: 'Create account',
            showLoaderOnConfirm: true
        },
        function(isConfirm){
            if (isConfirm) {
                var email = $.trim($("#signup-email").val());
                var name = $.trim($("#signup-name").val());
                var captcha = grecaptcha.getResponse();

                if (email == '') {
                    swal.showInputError("You need to enter an email address, human!");
                } else if (!isValidEmailAddress(email)) {
                    swal.showInputError("That's not a valid email address, human!");
                } else if (name == '') {
                    swal.showInputError("Please enter a name for your character");
                } else if (captcha == null) {
                    swal.showInputError("Are you not a human?");
                } else {
                    $.ajax(API_URL + '/users', {
                        type: 'POST',
                        data: JSON.stringify({
                            "email": email,
                            "name": name,
                            "captcha": captcha
                        })
                    })
                        .done(function() {
                            swal("Account created!", "Check your email", "success");
                        })
                        .fail(function(response) {
                            var message = response.responseJSON;
                            swal.showInputError(message.error);
                        })
                        .always(function(response){
                            console.log(response);
                        });
                }
            }
        }
    );
    scrambleText('h2');
    $(':input[autofocus]').focus();

    // Load Captcha widget for human validation
    grecaptcha.render('captcha', {
        'sitekey': '6Lf1UQkTAAAAAH3BPkgcwn7yzxm_RAn_Neklgz_V',
        'theme': 'dark',
        'callback': function (response) {
            console.log(response);
        }
    });
});

function reset_click() {
    swal({
            title: "Reset Password",
            text: $("#reset-form").html(),
            html: true,
            showCancelButton: true,
            confirmButtonText: 'Reset',
            showLoaderOnConfirm: true
        },
        function (isConfirm) {
            if (isConfirm) {
                var email = $.trim($("#reset-email").val());
                var captcha = grecaptcha.getResponse();

                if (email == '') {
                    swal.showInputError("You need to enter an email address, human!");
                } else if (!isValidEmailAddress(email)) {
                    swal.showInputError("That's not a valid email address, human!");
                } else if (captcha == null) {
                    swal.showInputError("Are you not a human?");
                } else {
                    $.ajax(API_URL + '/login/reset', {
                        type: 'POST',
                        data: JSON.stringify({
                            "email": email,
                            "captcha": captcha
                        })
                    })
                        .done(function () {
                            swal("Password reset!", "Check your email", "success");
                        })
                        .fail(function (response) {
                            var message = response.responseJSON;
                            swal.showInputError(message.error);
                        })
                        .always(function (response) {
                            console.log(response);
                        });
                }
            }
        }
    );
    $(':input[autofocus]').focus();

    // Load Captcha widget for human validation
    grecaptcha.render('captcha-reset', {
        'sitekey': '6Lf1UQkTAAAAAH3BPkgcwn7yzxm_RAn_Neklgz_V',
        'theme': 'dark',
        'callback': function (response) {
            console.log(response);
        }
    });
}

$("#play-button").click(function(){
    swal({
        title: "Welcome back!",
        text: "Redirecting you do your Dashboard...",
        type: "success",
        showConfirmButton: false
    });
    setTimeout(function(){
        window.location.href = '/game/';
    }, 2000);
});


/* Helper functions
 ******************************************************************************/
function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i);
    return pattern.test(emailAddress);
};