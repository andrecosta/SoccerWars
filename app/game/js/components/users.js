Vue.component('users', {
    template: '#users',

    data: function() {
        return {
            loaded: false,
            users: null
        }
    },

    ready: function(){
        var self = this;
        app.set_title("Users");

        $.get(API_URL + '/users')
            .done(function(response) {
                console.log(response);
                self.loaded = true;
                self.users = response;
            })
            .fail(function(response) {
                var message = response.responseJSON;
                // notification error
            });
    }
});