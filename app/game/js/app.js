var API_URL = 'http://localhost:5000';
var API_KEY = 'webapp';

// Set default headers to be sent with every request
$.ajaxSetup({
    beforeSend: function(xhr) {
        xhr.setRequestHeader('Content-Type', 'application/json');
    }
});

var app = new Vue({
    el: '#app',

    ready: function() {

        $.ajax(API_URL + '/users/1', {
            type: 'GET',
            headers: {'Secret': API_KEY}
        })
            .done(function(response) {
                console.log(response);
                var hash = CryptoJS.MD5(response.email);
                app.profile_picture = "https://www.gravatar.com/avatar/" + hash + "?s=200";
                app.profile_picture_set = true;
            })
            .fail(function(response) {
                var message = response.responseJSON;
                alert(message.error);
            });
    },

    data: {
        profile_picture: 'http://placehold.it/200',
        profile_picture_set: false
    },

    methods: {
        alert: function()
        {
            console.log('alert');
        }
    }
});
