var Vuex         = require('vuex');
var Vue          = require('vue');
var GithubClient = require('../lib/github-client');

Vue.use(Vuex);

module.exports = new Vuex.Store({
    state: {
        authenticated: false,
        authenticating: false,
        user: {},
        auth: {}
    },
    mutations: {
        authenticated: function(state, authenticated) {
            state.authenticated = authenticated;
            state.authenticating = false;
        },
        authenticating: function(state, authenticating) {
            state.authenticating = authenticating;
        },
        user: function(state, user) {
            state.user = user;
        },
        auth: function(state, auth) {
            state.auth = auth;
        }
    },
    actions: {
        login: function(context, redirect = true) {
            context.commit('authenticating', true);
            return new Promise(function(resolve, reject) {
                GithubClient.authenticate({redirect: redirect}).then(
                    function(response) {
                        if (response.authenticated) {
                            context.commit('auth', response.auth);
                            return GithubClient.getCurrentUser().then(
                                function(response) {
                                    context.commit('authenticated', true);
                                    context.commit('user', response.data);
                                    resolve(true);
                                },
                                function(error) {
                                    context.commit('authenticated', false);
                                    context.commit('user', {});
                                    GithubClient.clearAuthCookie();
                                    resolve(false);
                                }
                            );
                        }
                        context.commit('authenticated', false);
                        context.commit('user', {});
                        resolve(false);
                    },
                    function(error) {
                        context.commit('authenticated', false);
                        context.commit('user', {});
                        reject(error);
                    }
                );
            })
        },
        logout: function(context) {
            context.commit('authenticated', false);
            context.commit('user', {});
            GithubClient.clearAuthCookie();
        }
    }
})
