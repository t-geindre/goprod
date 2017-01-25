// jQuery required by bootsrap
// @todo fix this
window.jQuery = require('jquery');
require('bootstrap');

var Vue         = require('vue');
var VueRouter   = require('vue-router');
var Vuex        = require('vuex');
var VueResource = require('vue-resource');

var GithubClient = require('./lib/github-client');

var Components = {
    deployePullrequest: require('./component/deploy-pullrequest'),
    userDetails:        require('./component/user-details')
};

Vue.use(Vuex);
Vue.use(VueResource);
Vue.use(VueRouter);

new Vue({
    el: '#app',
    template: '#app-template',
    replace: false,
    data: {
        authenticating: false
    },
    router: new VueRouter({
        routes: [
            {
                name: 'home',
                path: '/',
                redirect: { name: 'deploy-by-pullrequest' }
            },
            {
                name: 'deploy-by-pullrequest',
                path: '/deploy/pullrequest',
                component: Components.deployePullrequest
            },
            {
                name: 'deploy-create-by-pullrequest',
                path: '/deploy/pullrequest/:id',
                component: Components.userDetails
            }
        ]
    }),
    store: new Vuex.Store({
        state: {
            authenticated: false,
            user: {}
        },
        mutations: {
            authenticated: function(state, authenticated) {
                state.authenticated = authenticated;
            },
            loadUser: function(state, user) {
                state.user = user;
            },
            clearUser: function(state) {
                state.user = {};
            }
        }
    }),
    computed: {
        authenticated: function() {
            return this.$store.state.authenticated;
        }
    },
    mounted: function() {
        this.authenticate(false);
    },
    methods: {
        authenticate: function(redirect = true) {
            this.authenticating = true;
            GithubClient.authenticate({
                redirect: redirect,
                success: function() {
                    GithubClient.getCurrentUser().then(function(response) {
                        this.$store.commit('loadUser', response.data);
                        this.$store.commit('authenticated', true);
                        this.authenticating = false;
                    }.bind(this), function(response) {
                        this.$store.commit('authenticated', false);
                        GithubClient.clearAuthCookie();
                        this.authenticating = false;
                    }.bind(this))
                }.bind(this),
                error: function() {
                    this.authenticating = false;
                }.bind(this)
            });
        },
        clearAuth: function() {
            GithubClient.clearAuthCookie();
            this.$store.commit('loadUser', {});
            this.$store.commit('authenticated', false);
        }
    }
});

