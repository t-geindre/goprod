var Vue          = require('vue');
var ApiClient    = require('../lib/api-client');
var DeploysStore = require('../store/deploys');
var GithubClient = require('../lib/github-client');
var jQuery       = require('jquery');

module.exports = Vue.component('deploy-process', {
    template: '#deploy-process-template',
    data: function() {
        return {
            pullrequest: {},
            loading: true,
            modal: false
        }
    },
    mounted: function() {
        this.modal = jQuery('.confirm-dialog', this.template).modal({ show: false});
        this.update();
    },
    computed: {
        actionLabel: function() {
            return ({
                'merge': 'Merge pullrequest',
                'deploy': 'Deploy project',
                'waiting': 'Confirm deployment is over'
            })[this.deploy.status];
        },
        actionButton: function() {
            return ['merge', 'deploy', 'waiting'].indexOf(this.deploy.status) > -1;
        },
        cancelButton: function() {
            return ['done', 'new', 'queued', 'canceled'].indexOf(this.deploy.status) == -1;
        },
        deploy: function() {
            if (DeploysStore.state.deploys[this.$route.params.id]) {
                return DeploysStore.state.deploys[this.$route.params.id];
            }

            this.$router.push({ name: 'user-deploys' });

            return { user:{} };
        }
    },
    methods: {
        update: function() {
            if (!this.deploy.pull_request_id || this.deploy.status != 'merge') {
                this.loading = false;
                return;
            }

            this.loading = true;
            GithubClient.getPullRequest({
                owner: this.deploy.owner,
                repo: this.deploy.repository,
                number: this.deploy.pull_request_id
            }).then(function(response) {
                this.pullrequest = response.data;
                this.loading = false;
            }.bind(this));
        },
        cancel: function(confirm) {
            if (!confirm) {
                this.modal.modal('show');
                return;
            }
            this.modal.modal('hide');
            DeploysStore.dispatch('cancel', this.deploy);
        }
    },
    watch: {
        $route: function() {
            this.update();
        }
    }
});
