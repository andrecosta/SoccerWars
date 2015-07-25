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

        if (window.location.hash == '#!/profile') {
            app.setTitle("My Profile");

            $.get(API_URL + '/me')
                .done(function (response) {
                    console.log(response);
                    self.loaded = true;
                    self.user = response;
                })
                .fail(function (response) {
                    console.log(response.responseJSON);
                });
        } else {
            app.setTitle("User Profile");

            $.get(API_URL + '/users/' + self.route.params.id)
                .done(function (response) {
                    console.log(response);
                    self.loaded = true;
                    self.user = response;
                })
                .fail(function (response) {
                    console.log(response.responseJSON);
                });
        }
    }
});