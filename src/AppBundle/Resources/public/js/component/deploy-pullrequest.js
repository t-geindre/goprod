var Vue = require('vue');
require('./github-issues');

module.exports = Vue.component('deploy-pullrequest', {
    template: '#deploy-pullrequest-template',
    computed: {
        user: function() {
            return this.$store.state.user;
        }
    },
    methods: {
        deploy: function(pr) {
            console.log(pr.id);
            this.$router.push({ name: 'deploy-create-by-pullrequest', params: {  id: pr.id }});
        }
    }
});
