Vue.component('match-details', {
    template: '#match-details',

    data: function() {
        return {
            loaded: false,
            match: null
        }
    },

    ready: function(){
        var self = this;
        app.setTitle("Match details");

        function cycle() {
            $.get(API_URL + '/matches/' + app.route_id)
                .done(function (response) {
                    console.log(response);
                    self.loaded = true;
                    self.match = response;
                })
                .fail(function (response) {
                    var message = response.responseJSON;
                    // notification error
                });
            setTimeout(cycle, 3000);
        }
        cycle();
    }
});