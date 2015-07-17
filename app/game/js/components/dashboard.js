var dashboard = Vue.extend({
    template: '#dashboard',

    data: function() {
        return {
            loaded: false
        }
    },

    ready: function() {
        app.setTitle("Dashboard");
        this.loaded = true;
    }
});

Vue.component('dashboard', dashboard);