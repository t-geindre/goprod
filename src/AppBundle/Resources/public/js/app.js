// jQuery required by bootsrap
// @todo fix this
window.jQuery = require('jquery');
require('bootstrap');

var Vue          = require('vue');
var Vuex         = require('vuex');
var VueResource  = require('vue-resource');
var Router       = require('./routing/router');
var GithubClient = require('./lib/github-client');
var UserStore    = require('./store/user');

var Components = {
    deployePullrequest: require('./component/deploy-pullrequest'),
    userDetails:        require('./component/user-details')
};

Vue.use(Vuex);
Vue.use(VueResource);

var app = new Vue({
    el: '#app',
    template: '#app-template',
    replace: false,
    data: {
        authError: false
    },
    router: Router,
    computed: {
        authenticated: function() {
            return UserStore.state.authenticated;
        },
        authenticating: function()
        {
            return UserStore.state.authenticating;
        }
    },
    mounted: function() {
        this.login(false);
    },
    methods: {
        login: function(redirect = true) {
            UserStore.dispatch('login', redirect).catch(
                function(error) {
                    app.authError = true;
                }
            );
        }
    }
});

