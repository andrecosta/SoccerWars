Vue.component('matches', {
    template: '#matches',

    data: function() {
        return {
            loaded: false,
            matches: null
        }
    },

    ready: function(){
        var self = this;
        app.set_title("Matches");

        $.get(API_URL + '/matches')
            .done(function(response) {
                console.log(response);
                self.loaded = true;
                self.matches = response;
            })
            .fail(function(response) {
                var message = response.responseJSON;
                // notification error
            });
    }
});