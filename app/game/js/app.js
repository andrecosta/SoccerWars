/* Global variables
 ******************************************************************************/
var API_URL = 'http://api.drymartini.eu/api';
var TOKEN = null;

/* Check if user has obtained a token from the login page
 ******************************************************************************/
if (Cookies.get('token')) {
    TOKEN = Cookies.get('token');
    if (!window.location.hash)
        window.location.hash = '#!/dashboard';
} else {
    window.location.href = '/';
}

/* Set default headers to be sent with every request
 ******************************************************************************/
$.ajaxSetup({
    beforeSend: function(xhr) {
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('Token', TOKEN);
    }
});


/* Main application
 ******************************************************************************/
Vue.use(VueRouter);

var app = new Vue({
    el: '#app',

    data: {
        user: null,
        title: null,
        isMaximized: false,
        route_id: null
    },

    ready: function() {

        $.get(API_URL + '/me')
            .done(function(response) {
                console.log(response);
                app.user = response;
            })
            .fail(function(response) {
                var message = response.responseJSON;
                // notification error
            });
    },

    methods: {

        // Set title
        setTitle: function(title) {
            app.title = title;
            scrambleText("#frame h1");
        },

        // Maximize the central section
        maximize: function(e) {
            app.isMaximized = true;
            $("#left").velocity({marginLeft: -150});
            $("#right").velocity({marginRight: -150});
            $("#middle").css("border-radius", "50% / 2%");
        },

        // Restore the central section
        minimize: function(e) {
            app.isMaximized = false;
            $("#left").velocity({marginLeft: 0});
            $("#right").velocity({marginRight: 0});
            $("#middle").css("border-radius", "50% / 3%");
        },

        // Logout and expire the token cookie
        logout: function() {
            Cookies.expire('token');
            window.location.href = '/';
        }

    }
});

Vue.filter('future', function(value) {
    var newValues = [];
    $.each(value, function(){
        if (Date.parse(this.start_time) > Date.now())
            newValues.push(this);
    });
    return newValues;
});

Vue.filter('past', function(value) {
    var newValues = [];
    $.each(value, function(){
        if (Date.parse(this.start_time) < Date.now())
            newValues.push(this);
    });
    return newValues;
});

Vue.filter('live', function(value) {
    var newValues = [];
    $.each(value, function(){
        if (Date.parse(this.start_time) > Date.now() && Date.now() < Date.parse(this.end_time))
            newValues.push(this);
    });
    return newValues;
});