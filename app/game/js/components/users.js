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
        app.setTitle("Users");

        function cycle() {
            $.get(API_URL + '/users')
                .done(function (response) {
                    console.log(response);
                    self.loaded = true;
                    self.users = response;
                })
                .fail(function (response) {
                    var message = response.responseJSON;
                    // notification error
                });
            setTimeout(cycle, 10000);
        }
        cycle();
    },

    methods: {
        go: function(id) {
            app.route_id = id;
            window.location.hash = '/user/' + id;
        }
    }
});