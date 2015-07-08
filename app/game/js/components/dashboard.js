Vue.component('dashboard', {
    template: '#dashboard',

    data: function() {
        return {
            loaded: false
        }
    },

    ready: function() {
        app.set_title("Dashboard");
        this.loaded = true;
    }
});