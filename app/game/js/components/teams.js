Vue.component('teams', {
    template: '#teams',

    data: function() {
        return {
            loaded: false,
            teams: null
        }
    },

    ready: function(){
        var self = this;
        app.setTitle("Teams");

        var timer = setInterval(function() {
        $.get(API_URL + '/teams')
            .done(function(response) {
                self.loaded = true;
                self.teams = response;
            })
            .fail(function (response) {
                humane.log(response.responseJSON['error']);
            });
        }, 10000, true);
        this.$add('timer', timer);
    },

    beforeDestroy: function() {
        clearInterval(this.$get('timer'));
    }
});