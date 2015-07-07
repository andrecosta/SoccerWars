/* Global variables
 ******************************************************************************/
var API_URL = 'http://localhost:5000/api';
var TOKEN = null;

/* Check if user has obtained a token from the login page
 ******************************************************************************/
if (Cookies.get('token')) {
    TOKEN = Cookies.get('token');
} else {
    window.location.href = '/';
}

/* Set default headers to be sent with every request
 ******************************************************************************/
$.ajaxSetup({
    beforeSend: function(xhr) {
        xhr.setRequestHeader('Content-Type', 'application/json');
    }
});

// Application
var app = new Vue({
    el: '#app',

    data: {
        user: {
            avatar: '',
            points: 0
        },
        isMaximized: false
    },

    ready: function() {

        $.ajax(API_URL + '/me', {
            type: 'GET',
            headers: {'Token': TOKEN}
        })
            .done(function(response) {
                /* GRAVATAR IMAGE
                var hash = CryptoJS.MD5(response.email);
                app.profile_picture = "https://www.gravatar.com/avatar/" + hash + "?s=150";*/
                app.$data.user = response.user
            })
            .fail(function(response) {
                var message = response.responseJSON;
            })
            .always(function(response) {
                console.log(response);
            });
    },

    methods: {

        maximize: function(e) {
            app.isMaximized = true;
            $("#left").velocity({marginLeft: -150});
            $("#right").velocity({marginRight: -150});
            $("#middle").css("border-radius", "50% / 2%");
        },

        minimize: function(e) {
            app.isMaximized = false;
            $("#left").velocity({marginLeft: 0});
            $("#right").velocity({marginRight: 0});
            $("#middle").css("border-radius", "50% / 3%");
        },

        logout: function() {
            Cookies.expire('token');
            window.location.href = '/';
        }
    }
});
