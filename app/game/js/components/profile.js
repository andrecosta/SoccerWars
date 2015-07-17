Vue.component('profile', {
    template: '#profile',

    data: function() {
        return {
            loaded: false,
            user: null
        }
    },

    ready: function(){
        var self = this;
        app.setTitle("My Profile");

        $.get(API_URL + '/me')
            .done(function(response) {
                console.log(response);
                self.loaded = true;
                self.user = response;
            })
            .fail(function(response) {
                var message = response.responseJSON;
                // notification error
            });
    }
});