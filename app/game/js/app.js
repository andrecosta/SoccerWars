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
        window.location.hash = '#!/dashboard';
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
        isMaximized: false
    },

    // Runs when the application is loaded
    ready: function () {
        var self = this;

        var timer = setInterval(function() {
            $.get(API_URL + '/me')
                .done(function (response) {
                    self.user = response;
                })
                .fail(function (response) {
                    console.log(response.responseJSON);
                });
        }, 1000, true);
        this.$add('timer', timer);
    },

    // Watch for changes in data objects dynamically
    watch : {
        // Watch for changes in user points balance
        'user.points': function(newVal, oldVal) {
            if (oldVal != undefined && newVal != oldVal) {
                var change = $("<span />");
                var diff = newVal - oldVal;
                if (diff > 0) {
                    change.addClass('change positive').text('+' + diff);
                } else if (diff < 0) {
                    change.addClass('change negative').text('-' + diff);
                }
                $("#points").prepend(change.delay(3000).fadeOut());
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
        }
    },

    // Custom methods for the root application
    methods: {
        // Set new title with animation
        setTitle: function (title) {
            this.title = title;
            scrambleText("#frame h1");
        },

        // Maximize the central section
        maximize: function () {
            this.isMaximized = true;
            $("#left").velocity({marginLeft: -150});
            $("#right").velocity({marginRight: -150});
            $("#middle").css("border-radius", "50% / 2%");
        },

        // Restore the central section
        minimize: function () {
            this.isMaximized = false;
            $("#left").velocity({marginLeft: 0});
            $("#right").velocity({marginRight: 0});
            $("#middle").css("border-radius", "50% / 3%");
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



/* Custom functions
 **********************************************************************************************************************/
// Override setInterval function to use seconds and an option to run immediately
var originalSetInterval = window.setInterval;
window.setInterval = function(fn, delay, runImmediately) {
    if(runImmediately) fn();
    return originalSetInterval(fn, delay);
};