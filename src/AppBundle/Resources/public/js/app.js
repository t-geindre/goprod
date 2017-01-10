Vue.config.delimiters = ['[[', ']]'];

var vue = new Vue({
    el: '#app',
    data: {
        user : {
            authenticated : false,
            authenticating: false
        }
    },
    mounted: function() {
        var urlParams = new URLSearchParams(window.location.search.slice(1));
        if (urlParams.has('code')) {
            this.user.authenticating = true;
            this.$http.get(
                'auth?code='+urlParams.get('code') // @todo: js routing
            ).then(function(response) {
                this.user = Object.assign(this.user, response.data);
                this.user.authenticated = true;
                this.user.authenticating = false;
                console.log(this.user);
            },
            function(response) {
                // @todo: gérer l'erreur
            });
        }
    },
    methods: {
        auth: function() {
            this.user.authenticating = true;
            window.location = Github.url + 'login/oauth/authorize?scope=repo&client_id=' + Github.clientId;
        }
    }
});
// @todo: github auth component
