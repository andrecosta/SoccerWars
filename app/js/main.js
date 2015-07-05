// Set popup window default options
swal.setDefaults({
    showCancelButton: false,
    closeOnConfirm: false,
    animation: false,
    customClass: 'popup',
    confirmButtonColor: 'transparent'
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
    'glow': ['0 0 100px deeppink', '0 0 50px cyan', '0 0 10px cyan'],
    'off': 0,
    'blink': 0,
    'classOn': 'on',
    'classOff': 'off',
    'autoOn': true
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
            //swal.showInputError("You need to write something!");
            //swal("Nice!", "Login success");
        }
    );
    scrambleText('h2');
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
                var input = $("#signup-email");
                if ($.trim(input.val()) == '' && input.is(":valid")) {
                    swal.showInputError("You need to enter an email address, human!");
                } else {
                    // check captcha api!! + send email
                    swal("Your account created!", "Check your email for your password");
                }
            }
        }
    );
    scrambleText('h2');

    // Load Captcha widget for human validation
    grecaptcha.render('captcha', {
        'sitekey' : '6Lf1UQkTAAAAAH3BPkgcwn7yzxm_RAn_Neklgz_V',
        'theme': 'dark',
        'callback' : function(response) {
            console.log(response);
        }
    });
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

function scrambleText(selector) {
    $(selector).each(function () {
        var $this = $(this), ticker = new Ticker($this).reset();
        $this.data('ticker', ticker);
    });

}