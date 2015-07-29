Vue.component('profile', {
    template: '#profile',

    data: function() {
        return {
            loaded: false,
            user: null,
            stats: null
        }
    },

    ready: function(){
        var self = this;

        var timer = setInterval(function() {
            if (window.location.hash == '#!/profile') {
                app.setTitle("My Profile");

                $.get(API_URL + '/me')
                    .done(function (response) {
                        self.loaded = true;
                        self.user = response;
                        $.get(API_URL + '/users/' + self.user.id + '/stats')
                            .done(function (response) {
                                self.stats = response;
                            })
                    })
                    .fail(function (response) {
                        humane.log(response.responseJSON['error']);
                    });
            } else {
                app.setTitle("User Profile");

                $.get(API_URL + '/users/' + self.route.params.id)
                    .done(function (response) {
                        self.loaded = true;
                        self.user = response;
                        $.get(API_URL + '/users/' + self.user.id + '/stats')
                            .done(function (response) {
                                self.stats = response;
                            })
                    })
                    .fail(function (response) {
                        humane.log(response.responseJSON['error']);
                    });
            }
        }, 10000, true);
        this.$add('timer', timer);
    },

    methods: {
        submenu: function(value, e) {
            $(".submenu span").removeClass("active");
            $(e.target).addClass("active");
            $(".content").hide();
            $(this.$$[value]).show();
            if (value == 'stats') setupCharts("chart_user_classes", this.stats);
        },

        match_click: function(id) {
            window.location.hash = '/matches/' + id;
        }
    },

    beforeDestroy: function() {
        clearInterval(this.$get('timer'));
    }
});