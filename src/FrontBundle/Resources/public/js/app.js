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
var ConfigStore  = require('./store/config');

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
        },
        configured: function() {
            return ConfigStore.state.configured;
        }
    },
    mounted: function() {
        ConfigStore.dispatch('loadConfig');
    },
    methods: {
        login: function(redirect = true) {
            UserStore.dispatch('login', redirect).catch(
                function(error) {
                    app.authError = true;
                }
            );
        }
    },
    watch: {
        configured: function() {
            if (this.configured) {
                GithubClient.setupUrls(ConfigStore.state.config.github.urls);
                GithubClient.setupApp({
                    client_id: ConfigStore.state.config.github.client_id,
                    scope: 'repo'
                });
                this.login(false);
            }
        }
    }
});

