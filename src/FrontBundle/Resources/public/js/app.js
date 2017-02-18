// jQuery required by bootsrap
// @todo fix this
window.jQuery = require('jquery');
require('bootstrap');
require('./component/loading-spinner');
require('./component/user-details');

var Vue          = require('vue');
var Vuex         = require('vuex');
var VueResource  = require('vue-resource');
var Router       = require('./routing/router');
var GithubClient = require('./lib/github-client');
var ApiClient    = require('./lib/api-client');
var UserStore    = require('./store/user');
var DeploysStore = require('./store/deploys');
var ConfigStore  = require('./store/config');

Vue.use(Vuex);
Vue.use(VueResource);

var app = new Vue({
    el: '#app',
    template: '#app-template',
    replace: false,
    data: {
        authError: false,
        authenticating: false,
        deploysRefresh: null
    },
    router: Router,
    delimiters: ['[[', ']]'],
    computed: {
        authenticated: function() {
            return UserStore.state.authenticated;
        },
        configured: function() {
            return ConfigStore.state.configured;
        },
        deploysCount: function() {
            return DeploysStore.state.count;
        }
    },
    mounted: function() {
        ConfigStore.dispatch('loadConfig');
    },
    methods: {
        login: function(redirect = true) {
            this.authenticating = true;
            UserStore.dispatch('login', redirect)
                .then(function() {
                    if (this.authenticated) {
                        ApiClient.setCredentials(
                            UserStore.state.user.login,
                            GithubClient.auth.access_token
                        );
                        return DeploysStore.dispatch('refresh');
                    }
                    console.log(this);
                    this.authenticating = false;
                }.bind(this))
                .then(function() {
                    this.registerDeploysRefresh();
                    this.authenticating = false;
                }.bind(this))
                .catch(function(response) {
                    this.authenticating = false;
                    app.authError = true;
                    GithubClient.clearAuthCookie();
                });
        },
        registerDeploysRefresh: function() {
            this.deploysRefresh = setInterval(function(){
                DeploysStore.dispatch('refresh');
            }, 5000);
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
    },
    beforeDestroy: function() {
        clearInterval(this.deploysRefresh);
    }
});

