var Vue          = require('vue');
var UserStore    = require('../store/user');
var ApiClient    = require('../lib/api-client');

require('../component/deploy');

module.exports = Vue.component('user-deploys', {
    template: '#user-deploys-template',
    computed: {
        deploys: function() {
            return UserStore.state.deploys;
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
