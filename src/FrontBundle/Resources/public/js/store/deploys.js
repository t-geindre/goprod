var Vuex      = require('vuex');
var Vue       = require('vue');
var ApiClient = require('../lib/api-client');

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
        refresh: function(context) {
            return new Promise(function(resolve, reject) {
                ApiClient.getDeploysByCurrentUser()
                    .then(function(response) {
                        context.commit('deploys', response.data);
                        resolve(response);
                    })
                    .catch(reject);
            });
        },
        create: function(context, deploy) {
            return new Promise(function(resolve, reject) {
                ApiClient.createDeploy(deploy)
                    .then(function(response) {
                        context.commit('add', response.data.entity);
                        resolve(response);
                    })
                    .catch(reject);
            });
        },
        cancel: function(context, deploy) {
                return ApiClient.cancelDeploy(deploy.id)
                    .then(function(response) {
                        return context.dispatch('refresh');
                    });
        }
    }
})
