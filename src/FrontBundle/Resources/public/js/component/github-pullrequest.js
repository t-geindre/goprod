var Vue          = require('vue');
var GithubClient = require('../lib/github-client');

Vue.component('github-pullrequest', {
    template: '#github-pullrequest-template',
    props: ['pullrequest'],
    data: function () {
        return {
            loading: true,
            issue: {}
        }
    },
    methods: {
        update: function() {
            this.loading = true;
            GithubClient.getIssue({
                owner: this.pullrequest.base.repo.owner.login,
                repo: this.pullrequest.base.repo.name,
                number: this.pullrequest.number
            })
            .then(function(response) {
                this.issue = response.data;
                this.loading = false;
            }.bind(this));
        }
    },
    mounted: function() {
        this.update();
    }
});
