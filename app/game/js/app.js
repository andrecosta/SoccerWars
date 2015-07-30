/* Global variables
 **********************************************************************************************************************/
var API_URL = 'https://api.soccerwars.xyz';
var TOKEN = null;



/* Default options and actions
 **********************************************************************************************************************/
// Check if user has successfully obtained a token from the login page
if (Cookies.get('token')) {
    // If the cookie is set, login was successful and the user is redirected
    TOKEN = Cookies.get('token');
    if (!window.location.hash)
        window.location.hash = '#!/profile';
} else {
    // If not, redirect him back to login page
    window.location.href = '/';
}

// Set default headers to be sent with every ajax request
$.ajaxSetup({
    beforeSend: function(xhr) {
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('Token', TOKEN);
    }
});

// Set default options for the humane.js notification plugin
humane.timeout = 5000;
humane.addnCls = 'custom';



/* Main application
 **********************************************************************************************************************/
Vue.use(VueRouter);

var app = new Vue({
    // The element where the framework will bind to
    el: '#app',

    // Default data objects
    data: {
        user: {
            avatar: {
                big: ''
            }
        },
        title: null,
        isMaximized: false,
        loaded: false
    },

    // Runs when the application is loaded
    ready: function () {
        var self = this;

        var timer = setInterval(function() {
            $.get(API_URL + '/me')
                .done(function (response) {
                    self.loaded = true;
                    self.user = response;
                })
                .fail(function (response) {
                    humane.log(response.responseJSON['error']);
                });
        }, 1000, true);
        this.$add('timer', timer);
    },

    // Watch for changes in data objects dynamically
    watch : {
        // Watch for changes in user points balance
        'user.points': function(newVal, oldVal) {
            if (oldVal != undefined && newVal != oldVal) {
                $('.change').remove();
                var change = $("<span />").addClass("change");
                var diff = newVal - oldVal;

                if (diff > 0) change.addClass('positive');
                else if (diff < 0) change.addClass('negative');

                change.text(Math.abs(diff));
                $("#points").prepend(change.delay(5000).fadeOut());
            }
        },

        // Watch for changes in new unlocked badges
        'user.badges': function(newVal, oldVal) {
            if (oldVal != undefined) {
                $.each(oldVal, function () {
                    var oldBadge = this;
                    $.each(newVal, function () {
                        if (this.id == oldBadge.id && this.unlocked && !oldBadge.unlocked) {
                            humane.log("Badge unlocked<br><br>"
                                + "<img src='" + this.image + "' width='32' height='32'><br>"
                                + "<b>" + this.name + "</b>");
                        }
                    });
                });
            }
        },

        // Watch for changes in bailouts
        'user.bailouts': function(newVal, oldVal) {
            if (oldVal != undefined && newVal > oldVal) {
                humane.log("<b>Ouch!</b><br><br>"
                    + "You have received a bailout of <b>$1000</b> for spending all your points<br><br>"
                    + "<b>TOTAL BAILOUTS: <span style='color: red'>" + newVal + "</span></b>");
            }
        }
    },

    // Custom methods for the application
    methods: {
        // Set new title with animation
        setTitle: function (title) {
            this.title = title;
            scrambleText("#frame h1");
        },

        // Maximize the central section
        maximize: function () {
            this.isMaximized = true;
            $("#left").velocity({marginLeft: -200});
            $("#right").velocity({marginRight: -200});
            $("#middle").css("border-radius", "50% / 2%");
        },

        // Restore the central section
        restore: function () {
            this.isMaximized = false;
            $("#left").velocity({marginLeft: 0});
            $("#right").velocity({marginRight: 0});
            $("#middle").css("border-radius", "50% / 3%");
        },

        // Goes to the match page of the clicked bet in 'My bets'
        bet_click: function(id) {
            window.location.hash = '/matches/' + id;
        },

        // Calculates a bet payout depending on its type
        payout: function(type, points) {
            if (type == 1) return "+$ " + parseInt(points) * 3; // Simple (x3)
            else if (type == 2) return "+$ " + parseInt(points) * 2; // Advanced (x2)
        },

        // Logout and expire the token cookie
        logout: function () {
            Cookies.expire('token');
            window.location.href = '/';
        }
    }
});



/* Custom filters
 **********************************************************************************************************************/
// Filters a list of objects which have a date span in the future
Vue.filter('future', function (value) {
    var newValues = [];
    $.each(value, function () {
        if (moment().isBefore(this.start_time))
            newValues.push(this);
    });
    return newValues;
});

// Filters a list of objects which have a date span in the past
Vue.filter('past', function (value) {
    var newValues = [];
    $.each(value, function () {
        if (moment().isAfter(this.end_time))
            newValues.push(this);
    });
    return newValues;
});

// Filters a list of objects which have a date span in the present
Vue.filter('present', function (value) {
    var newValues = [];
    $.each(value, function () {
        if (moment().isBetween(this.start_time, this.end_time))
            newValues.push(this);
    });
    return newValues;
});

// Formats a date to show it relative to present time
Vue.filter('from_now', function (value, nosuffix) {
    nosuffix = nosuffix || false;
    value = moment(value).fromNow(nosuffix);
    return value;
});

// Converts an integer bet result to an html string
Vue.filter('to_result_string', function (value) {
    if (value) {
        if (value == 1) return "<span class='won'>won</span>";
        else return "<span class='lost'>lost</span>";
    } else return "in progress"
});



/* Custom functions
 **********************************************************************************************************************/
// Override setInterval function to use seconds and an option to run immediately
var originalSetInterval = window.setInterval;
window.setInterval = function(fn, delay, runImmediately) {
    if(runImmediately) fn();
    return originalSetInterval(fn, delay);
};

// Setup charts
Chart.defaults.global.animation = false;
function setupCharts(id, stats) {
    var ctx = document.getElementById(id).getContext("2d");
    var user_classes = new Chart(ctx).Bar({
        labels: ["Poor", "Middle class", "Rich", "Capitalist"],
        datasets: [
            {
                fillColor: "rgba(0,255,255,0.6)",
                data: [
                    stats.wealth_gap.users_class1,
                    stats.wealth_gap.users_class2,
                    stats.wealth_gap.users_class3,
                    stats.wealth_gap.users_class4
                ]
            }
        ]
    });
}