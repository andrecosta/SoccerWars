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
        app.setTitle("Leaderboards");

        var timer = setInterval(function() {
            $.get(API_URL + '/users')
                .done(function (response) {
                    //console.log(response);
                    self.loaded = true;
                    self.users = response;
                })
                .fail(function (response) {
                    humane.log(response.responseJSON['error']);
                });
        }, 10000, true);
        this.$add('timer', timer);
    },

    methods: {
        go: function(id) {
            window.location.hash = '/users/' + id;
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