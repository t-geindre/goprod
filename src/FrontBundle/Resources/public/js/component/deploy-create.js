var Vue = require('vue');
var GithubClient = require('../lib/github-client');

module.exports = Vue.component('deploy-create', {
    template: '#deploy-create-template',
    data: function() {
        return {
            deploy: {
                owner: '',
                repo: ''
            },
            description: '',
            loading: false,
            pullrequest: false,
            issue: false
        };
    },
    computed: {
        repositoryName: function() {
            return this.deploy.owner + '/' + this.deploy.repo;
        }
    },
    mounted: function() {
        this.update()
    },
    methods: {
        loadPullRequest: function(pr) {
            this.loading = true;
            GithubClient.getIssue(pr)
                .then(function(response) {
                    this.issue = response.data;
                    return GithubClient.getPullRequest(pr);
                }.bind(this))
                .then(function(response) {
                    this.pullrequest = response.data;
                    this.description = this.pullrequest.title;
                    this.loading = false;
                }.bind(this))
                .catch(function(response) {
                    this.$router.push({ name: 'deploy-by-pullrequest' });
                }.bind(this))
            ;
        },
        cancel: function() {
            this.$router.back('/');
        },
        update: function() {
            this.deploy = this.$route.params;
            if (this.deploy.number) {
                this.loadPullRequest(this.deploy);
            }
        }
    },
    watch: {
        $route: function() {
            this.update();
        }
    }
});
