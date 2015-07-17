Vue.component('user-details', {
    template: '#user-details',

    data: function() {
        return {
            loaded: false,
            user: null
        }
    },

    ready: function(){
        var self = this;
        app.setTitle("User details");

        $.get(API_URL + '/users/' + app.route_id)
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