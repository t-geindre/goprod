var Vuex = require('vuex');
var Vue  = require('vue');
var ApiClient = require('../lib/api-client');

Vue.use(Vuex);

module.exports = new Vuex.Store({
    state: {
        configured: false,
        config: {}
    },
    mutations: {
        config: function(state, config) {
            state.config = config;
            state.configured = true;
        }
    },
    actions: {
        loadConfig: function(context) {
            ApiClient.getAppConfig().then(function(response) {
                context.commit('config', response.data);
            });
        }
    }
})
