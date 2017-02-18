var Vue          = require('vue');
var DeploysStore = require('../store/deploys');
var ApiClient    = require('../lib/api-client');

require('../component/deploy');

module.exports = Vue.component('user-deploys', {
    template: '#user-deploys-template',
    computed: {
        deploys: function() {
            return DeploysStore.state.deploys;
        },
        count: function() {
            return DeploysStore.state.count;
        }
    },
    methods: {
        goToDeploy: function(deploy) {
            this.$router.push({
                name: 'deploy-process',
                params: { id: deploy.id }
            });
        }
    }
});
