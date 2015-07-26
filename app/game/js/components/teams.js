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

        $.get(API_URL + '/teams')
            .done(function(response) {
                console.log(response);
                self.loaded = true;
                self.teams = response;
            })
            .fail(function (response) { console.log(response.responseJSON); });
    }
});