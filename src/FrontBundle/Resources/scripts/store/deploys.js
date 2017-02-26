var Vuex         = require('vuex');
var Vue          = require('vue');
var ApiClient    = require('../lib/api-client');
var GithubClient = require('../lib/github-client');

Vue.use(Vuex);

module.exports = new Vuex.Store({
    state: {
        deploys: {},
        sortable: [],
        count: 0
    },
    mutations: {
        add: function(state, deploy) {
            state.sortable.push(deploy);
            state.deploys[deploy.id] = deploy;
            state.count++;
        },
        deploys: function(state, deploys) {
            state.deploys = {};
            state.sortable = [];
            state.count = 0;
            deploys.forEach(function(deploy) {
                state.sortable.push(deploy);
                state.deploys[deploy.id] = deploy;
                state.count++;
            });
        }
    },
    actions: {
        refresh: function(context, deploy = {}) {
            return new Promise(function(resolve, reject) {
                if (!deploy.id) {
                    ApiClient.getDeploysByCurrentUser()
                        .then(function(response) {
                            context.commit('deploys', response.data);
                            resolve(response);
                        }, reject);
                    return;
                }
                if (!context.state.deploys[deploy.id]) {
                    reject();
                }
                ApiClient.getDeploy(deploy.id)
                    .then((response) => {
                        return context.dispatch('refresh');
                    })
                    .then(resolve, reject);
            });
        },
        create: function(context, deploy) {
            return new Promise(function(resolve, reject) {
                ApiClient.createDeploy(deploy)
                    .then(function(response) {
                        context.commit('add', response.data.entity);
                        resolve(response);
                    }, reject);
            });
        },
        cancel: function(context, deploy) {
            return ApiClient.cancelDeploy(deploy.id)
                .then(function(response) {
                    return context.dispatch('refresh');
                });
        },
        confirm: function(context, deploy) {
            return ApiClient.confirmDeploy(deploy.id)
                .then(function(response) {
                    return context.dispatch('refresh');
                });
        },
        merge: function(context, deploy, sha) {
            return new Promise(function(resolve, reject) {
                GithubClient.mergePullRequest(
                    {
                        owner: deploy.owner,
                        repo: deploy.repository,
                        number: deploy.pull_request_id
                    },
                    sha
                )
                .then(() => {
                    return context.dispatch('refresh', deploy);
                })
                .then(resolve)
                .catch(reject)
            });
        },
        deploy: function(context, deploy) {
            return new Promise(function(resolve, reject) {
                ApiClient.deploy(deploy.id).then(() => {
                    return context.dispatch('refresh', deploy);
                })
                .then(resolve)
                .catch(reject);
            });
        }
    }
})
