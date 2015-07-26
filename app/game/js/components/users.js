Vue.component('users', {
    template: '#users',

    data: function() {
        return {
            loaded: false,
            users: null,
            order: false
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
                    console.log(response.responseJSON);
                });
        }, 10000, true);
        this.$add('timer', timer);
    },

    methods: {
        go: function(id) {
            window.location.hash = '/users/' + id;
        },

        submenu: function(value, order, e) {
            $(".submenu span").removeClass("active");
            $(e.target).addClass("active");
            this.order = order;
        }
    },

    beforeDestroy: function() {
        clearInterval(this.$get('timer'));
    }
});