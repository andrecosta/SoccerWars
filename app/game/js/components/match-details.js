Vue.component('match-details', {
    template: '#match-details',

    data: function() {
        return {
            loaded: false,
            match: null,
            in_progress: false
        }
    },

    ready: function(){
        var self = this;
        app.setTitle("Match details");

        // Fetch data every second
        var timer = setInterval(function() {
            $.get(API_URL + '/matches/' + self.route.params.id)
                .done(function (response) {
                    console.log(response);
                    self.loaded = true;
                    self.match = response;
                })
                .fail(function (response) {
                    console.log(response.responseJSON);
                });
        }, 1000, true);
        this.$add('timer', timer);
    },

    beforeDestroy: function() {
        clearInterval(this.$get('timer'));
    }
});