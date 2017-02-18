require('./github-issues');

var Vue = require('vue');
var UserStore = require('../store/user');

module.exports = Vue.component('deploy-pullrequest', {
    template: '#deploy-pullrequest-template',
    computed: {
        user: function() {
            return UserStore.state.user;
        }
    },
    methods: {
        deploy: function(pr) {
            var repo = pr.repository_url.split('/');
            this.$router.push({
                name: 'deploy-create-by-pullrequest',
                params: {
                    owner: repo[repo.length - 2],
                    repo: repo[repo.length - 1],
                    number: pr.number
                }
            });
        }
    }
});
