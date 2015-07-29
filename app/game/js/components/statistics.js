Vue.component('statistics', {
    template: '#statistics',

    data: function() {
        return {
            loaded: false,
            stats: null
        }
    },

    ready: function(){
        var self = this;
        app.setTitle("Statistics");

        var timer = setInterval(function() {
            $.get(API_URL + '/stats')
                .done(function (response) {
                    self.loaded = true;
                    self.stats = response;
                    setupCharts("chart_global_user_classes", self.stats);
                })
                .fail(function (response) {
                    humane.log(response.responseJSON['error']);
                });
        }, 1000, true);
        this.$add('timer', timer);
    },

    beforeDestroy: function() {
        clearInterval(this.$get('timer'));
    }
});