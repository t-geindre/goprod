var Vue          = require('vue');
var UserStore    = require('../store/user');
var ApiClient    = require('../lib/api-client');
var GithubClient = require('../lib/github-client');

module.exports = Vue.component('deploy-create', {
    template: '#deploy-create-template',
    data: function() {
        return {
            deploy: {
            },
            loading: false,
            pullrequest: false,
            issue: false,
            errors: {
                fields: {},
                global: []
            }
        };
    },
    computed: {
        repositoryName: function() {
            return this.deploy.owner + '/' + this.deploy.repository;
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
                    this.deploy.description = this.pullrequest.title;
                    this.pull
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
            this.deploy = {
                owner: this.$route.params.owner,
                repository: this.$route.params.repo,
                description: '',
                pullRequestId: this.$route.params.number ? this.$route.params.number : null
            };
            this.errors = { fields: {} };
            if (this.$route.params.number) {
                this.loadPullRequest(this.$route.params);
            }
        },
        create: function() {
            this.loading = true;
            UserStore.dispatch('addDeploy', this.deploy)
                .catch(function(response) {
                    this.loading = false;
                    this.errors = response.data.errors;
                }.bind(this));
        }
    },
    watch: {
        $route: function() {
            this.update();
        }
    }
});
