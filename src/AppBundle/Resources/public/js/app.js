Vue.config.delimiters = ['[[', ']]'];

var vue = new Vue({
    el: '#app',
    template: '#app-template',
    replace: false,
    data: {
        authenticated: false,
        authenticating: true
    },
    mounted: function() {
        this.authenticate();
    },
    methods: {
        authenticate: function(redirect = false) {
            this.authenticating = true;
            this.$github.authenticate({
                redirect: redirect,
                success: function() {
                    this.authenticated = true;
                    this.authenticating = false;
                },
                error: function() {
                    this.authenticated = false;
                    this.authenticating = false;
                }
            });
        }
    }
});

