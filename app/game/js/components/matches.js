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
        app.setTitle("Matches");

        // Fetch data every 10 seconds
        var timer = setInterval(function() {
            $.get(API_URL + '/matches')
                .done(function (response) {
                    console.log(response);
                    self.loaded = true;
                    self.matches = response;
                })
                .fail(function (response) {
                    console.log(response.responseJSON);
                });
        }, 10000, true);
        this.$add('timer', timer);
    },

    methods: {
        go: function(id) {
            window.location.hash = '/matches/' + id;
        },

        submenu: function(value, e) {
            $(".submenu span").removeClass("active");
            $(e.target).addClass("active");
            $(".content").hide();
            $(this.$$[value]).show();
        }
    },

    beforeDestroy: function() {
        clearInterval(this.$get('timer'));
    }
});