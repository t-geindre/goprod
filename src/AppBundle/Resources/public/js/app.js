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
            console.log('okaa');
            this.user.authenticating = true;
            this.$http.get(
                Github.url +'login/oauth/access_token',
                {
                    params : {
                        client_id: Github.clientId,
                        client_secret: Github.clientSecret,
                        code: urlParams.get('code')
                    }
                }
            ).then(function(reponse) {
console.log(response);
            },
            function(response) {

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
