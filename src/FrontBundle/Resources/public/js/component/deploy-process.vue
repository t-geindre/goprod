<script>
var DeploysStore = require('../store/deploys.js');
var GithubClient = require('../lib/github-client.js');
var jQuery       = require('jquery');

module.exports = {
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
    },
    components: {
        'loading-spinner': require('./loading-spinner.vue'),
        'github-pullrequest': require('./github-pullrequest.vue'),
        'deploy': require('./deploy.vue')
    }
};
</script>

<template>
    <div>
        <div class="page-header">
            <h1>
                Deployment
                <small>in progress</small>
            </h1>
        </div>
        <template v-if="!loading">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <button type="button" v-on:click="cancel(false)" class="btn btn-xs btn-danger pull-right" v-if="cancelButton">
                        <span class="glyphicon glyphicon-remove"></span>
                    </button>
                    <h3 class="panel-title">
                        {{ deploy.description }}
                    </h3>
                </div>
                <div class="panel-body">
                    <github-pullrequest
                        v-if="deploy.status == 'merge'"
                        v-bind:pullrequest="pullrequest"
                    ></github-pullrequest>
                    <div class="pull-right">
                        <button type="button" class="btn btn-primary" v-if="actionButton">
                            {{ actionLabel }}
                            <span class="glyphicon glyphicon-chevron-right"></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
        <loading-spinner class="medium" v-else></loading-spinner>
        <div class="modal fade confirm-dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Confirm deployment cancelling</h4>
                    </div>
                    <div class="modal-body">
                        <deploy v-bind:deploy="deploy"></deploy>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" v-on:click="cancel(true)" class="btn btn-danger">Cancel deployment</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
