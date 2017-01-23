var vue = new Vue({
    el: '#app',
    template: '#app-template',
    replace: false,
    data: {
        authenticated: false,
        authenticating: true,
        user: {}
    },
    mounted: function() {
        this.authenticate(false);
    },
    methods: {
        authenticate: function(redirect = true) {
            this.authenticating = true;
            this.$github.authenticate({
                redirect: redirect,
                success: function() {
                    this.$github.getCurrentUser().then(function(response) {
                        this.user = response.data;
                        this.authenticated = true;
                        this.authenticating = false;
                    })
                },
                error: function() {
                    this.authenticating = false;
                }
            });
        },
        clearAuth: function() {
            this.$github.clearAuthCookie();
            this.authenticated = false;
            this.user = {};
        }
    }
});

