/* Defaults
 ******************************************************************************/
var API_URL = 'http://localhost:5000/api';

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


/* Modal windows
 ******************************************************************************/
$("#login-button").click(function(){
    swal({
            title: "Login",
            text: $('#login-form').html(),
            html: true,
            showCancelButton: true,
            confirmButtonText: 'Submit'
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
                            swal({
                                title: "Greetings, " + response.name + "!",
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
                            console.log(response)
                        });
                }
            }
        }
    );
    scrambleText('h2');
    $(':input[autofocus]').focus();
});

$("#signup-button").click(function(){
    swal({
            title: "Sign Up",
            text: $("#signup-form").html(),
            html: true,
            showCancelButton: true,
            confirmButtonText: 'Create account'
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
                            swal("Account created!", "Check your email for your password", "success");
                        })
                        .fail(function(response) {
                            var message = response.responseJSON;
                            swal.showInputError(message.error);
                        })
                        .always(function(response){
                            console.log(response)
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


/* Text neon effect
 ******************************************************************************/
$(".neon > span").novacancy({
    'reblinkProbability': 0.5,
    'blinkMin': 0.1,
    'blinkMax': 0.5,
    'loopMin': 10,
    'loopMax': 20,
    'color': 'white',
    'glow': ['0 0 100px rgb(234, 105, 255)', '0 0 50px cyan', '0 0 10px cyan'],
    'off': 0,
    'blink': 0,
    'classOn': 'on',
    'classOff': 'off',
    'autoOn': true
});


/* Text scambling effect
 ******************************************************************************/
function Ticker(elem) {
    elem.lettering();
    this.done = false;
    this.cycleCount = 5;
    this.cycleCurrent = 0;
    this.chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()-_=+{}|[]\\;\':"<>?,./`~'.split('');
    this.charsCount = this.chars.length;
    this.original = elem.html();
    this.letters = elem.find('span');
    this.letterCount = this.letters.length;
    this.letterCurrent = 0;

    this.letters.each(function () {
        var $this = $(this);
        $this.attr('data-orig', $this.text());
        $this.text('-');
    });
}

Ticker.prototype.getChar = function () {
    return this.chars[Math.floor(Math.random() * this.charsCount)];
};

Ticker.prototype.reset = function () {
    this.done = false;
    this.cycleCurrent = 0;
    this.letterCurrent = 0;
    this.letters.each(function () {
        var $this = $(this);
        $this.text($this.attr('data-orig'));
        $this.removeClass('done');
    });
    this.loop();
};

Ticker.prototype.loop = function () {
    var self = this;

    this.letters.each(function (index, elem) {
        var $elem = $(elem);
        if (index >= self.letterCurrent) {
            if ($elem.text() !== ' ') {
                $elem.text(self.getChar());
                $elem.css('opacity', Math.random());
            }
        }
    });

    if (this.cycleCurrent < this.cycleCount) {
        this.cycleCurrent++;
    } else if (this.letterCurrent < this.letterCount) {
        var currLetter = this.letters.eq(this.letterCurrent);
        this.cycleCurrent = 0;
        currLetter.text(currLetter.attr('data-orig')).css('opacity', 1).addClass('done');
        this.letterCurrent++;
    } else {
        this.done = true;
    }

    if (!this.done) {
        requestAnimationFrame(function () {
            self.loop();
        });
    } else {
        /* var self = this;
         setTimeout( function() {
         self.reset();
         }, 750 );*/
    }
};


/* Functions
 ******************************************************************************/
function scrambleText(selector) {
    $(selector).each(function () {
        var $this = $(this), ticker = new Ticker($this).reset();
        $this.data('ticker', ticker);
    });

}

function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i);
    return pattern.test(emailAddress);
};