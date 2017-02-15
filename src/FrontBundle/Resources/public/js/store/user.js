var Vuex         = require('vuex');
var Vue          = require('vue');
var GithubClient = require('../lib/github-client');
var ApiClient    = require('../lib/api-client');

Vue.use(Vuex);

module.exports = new Vuex.Store({
    state: {
        authenticated: false,
        authenticating: false,
        user: {},
        deploys: [],
        complete: true
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
        complete: function(state, complete) {
            state.complete = complete;
        },
        addDeploy: function(state, deploy) {
            state.deploys.push(deploy);
        },
        deploys: function(state, deploys) {
            state.deploys = deploys;
        }
    },
    actions: {
        update: function(context, data) {
            return new Promise(function(resolve, reject) {
                ApiClient.updateUser(
                    Object.assign(context.state.user, data)
                ).then(function(response) {
                    context.commit('user', response.data.entity);
                    context.commit('complete', true);
                    resolve(response);
                }, function(response) {
                    reject(response);
                });
            })
        },
        login: function(context, redirect = true) {
            context.commit('authenticating', true);
            return new Promise(function(resolve, reject) {
                GithubClient.authenticate({redirect: redirect})
                    .then(function(data) {
                        if (data.authenticated) {
                            return ApiClient.getUser({
                                params: {
                                    access_token: data.auth.access_token,
                                    login: data.auth.login
                                }
                            })
                        }
                        resolve(data);
                    })
                    .then(function(response) {
                        context.commit('user', response.data.user);
                        context.commit('complete', response.data.complete);
                        context.commit('authenticated', true);
                        resolve(response);
                    })
                    .catch(function(response) {
                        context.commit('authenticated', false);
                        reject(response);
                    });
            });
        },
        logout: function(context) {
            context.commit('authenticated', false);
            context.commit('user', {});
            context.commit('deploys', []);
            GithubClient.clearAuthCookie();
        },
        loadDeploys: function(context) {
            return new Promise(function(resolve, reject) {
                ApiClient.getDeploysByCurrentUser()
                    .then(function(response) {
                        context.commit('deploys', response.data);
                        resolve(response);
                    })
                    .catch(reject);
            });
        },
        addDeploy: function(context, deploy) {
            return new Promise(function(resolve, reject) {
                ApiClient.createDeploy(deploy)
                    .then(function(response) {
                        context.commit('addDeploy', response.data.entity);
                        resolve(response);
                    })
                    .catch(reject);
            });
        }
    }
})
