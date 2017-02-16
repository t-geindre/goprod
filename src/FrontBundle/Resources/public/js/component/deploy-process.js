var Vue          = require('vue');
var ApiClient    = require('../lib/api-client');
var UserStore    = require('../store/user');
var GithubClient = require('../lib/github-client');
var jQuery       = require('jquery');

module.exports = Vue.component('deploy-process', {
    template: '#deploy-process-template',
    data: function() {
        return {
            issue: {},
            pullrequest: {},
            deploy: { user: {} },
            owned: false,
            loading: false,
            modal: false
        }
    },
    mounted: function() {
        this.modal = jQuery('.confirm-dialog', this.template).modal({ show: false});
        this.update();
    },
    computed: {
        user: function() {
            return UserStore.state.user;
        }
    },
    methods: {
        update: function() {
            if (!this.$route.params.id) {
                return;
            }

            this.loading = true;
            ApiClient.getDeploy(this.$route.params.id)
                .then(function(response) {
                    this.deploy = response.data;
                    this.owned = (this.user.login == this.deploy.user.login);
                    this.loading = false;
                    this.loadPullRequest();
                }.bind(this))
                .catch(function(response) {
                    this.$router.push({ name: 'user-deploys' });
                }.bind(this));
        },
        loadPullRequest: function() {
            if (!this.deploy.pull_request_id) {
                return;
            }

            var pr = {
                owner: this.deploy.owner,
                repo: this.deploy.repository,
                number: this.deploy.pull_request_id
            };

            this.loading = true;

            GithubClient.getIssue(pr)
                .then(function(response) {
                    this.issue = response.data;
                    return GithubClient.getPullRequest(pr);
                }.bind(this))
                .then(function(response) {
                    this.pullrequest = response.data;
                    this.loading = false;
                }.bind(this))
                .catch(function(response) {
                    this.$router.push({ name: 'deploy-by-pullrequest' });
                }.bind(this))
        },
        cancel: function(confirm = false) {
            if (!confirm) {
                this.modal.modal('show');
                return;
            }
        }
    },
    watch: {
        $route: function() {
            this.update();
        }
    }
});
