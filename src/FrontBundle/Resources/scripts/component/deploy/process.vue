<script>
var UserStore    = require('../../store/user.js');
var DeploysStore = require('../../store/deploys.js');
var ApiClient    = require('../../lib/api-client.js');
var GithubClient = require('../../lib/github-client.js');
var jQuery       = require('jquery');

module.exports = {
    data: () => ({
        pullrequest: false,
        loading: true,
        modal: false,
        processing: false,
        errors: {},
        deployProcess: {},
        localDeploy: {user: {}}
    }),
    mounted: function() {
        this.deployProcess = {
            queued: {
                cancel: true,
                action: false,
                actionLabel: 'Please wait...'
            },
            merge : {
                action: () => DeploysStore
                    .dispatch('merge', this.deploy, this.pullrequest.merge_commit_sha)
                    .then(() => this.loadPullrequest(false))
                    .then(() => { this.processing = false; })
                    .catch(() => {
                        this.processing = false;
                        this.errors.merge = true;
                    }),
                actionLabel: 'Merge pullrequest',
                cancel: true
            },
            deploy : {
                action: () => DeploysStore.dispatch('deploy', this.deploy),
                actionLabel: 'Deploy project',
                cancel: true
            },
            waiting: {
                action: () => DeploysStore.dispatch('confirm', this.deploy)
                    .then(() => { this.$router.push({ name: 'user-deploys' }) }),
                actionLabel: 'Confirm deployment is over',
                cancel: false
            },
            canceled: {
                action: false,
                cancel: false
            },
            done: {
                action: false,
                cancel: false
            }
        };
        this.modal = jQuery('.confirm-dialog', this.template).modal({ show: false});
        this.update();
    },
    computed: {
        actionButtons: function() {
            return (
                this.deploy.id
                && this.deploy.user.login == this.user.login
                && (
                    this.deployProcess[this.deploy.status].action
                    || this.deployProcess[this.deploy.status].cancel
                )
            );
        },
        actionLabel: function() {
            return this.deployProcess[this.deploy.status].actionLabel;
        },
        actionButton: function() {
            return this.deployProcess[this.deploy.status].action && !this.processing;
        },
        cancelButton: function() {
            return this.deployProcess[this.deploy.status].cancel;
        },
        deploy: function() {
            var deploy = DeploysStore.state.deploys.find(
                (deploy) => deploy.id == this.$route.params.id
            );
            if (deploy == undefined) {
                return this.localDeploy;
            }
            return deploy;
        },
        user: () => UserStore.state.user
    },
    methods: {
        next: function() {
            if (!this.processing && this.deployProcess[this.deploy.status].action) {
                this.processing = true;
                this.deployProcess[this.deploy.status].action();
            }
        },
        loadPullrequest: function(loading = true) {
            this.loading = loading;
            return new Promise((resolve, reject) => {
                if (!this.deploy.pull_request_id) {
                    this.loading = false;
                    resolve();
                    return;
                }

                GithubClient.getPullRequest({
                    owner: this.deploy.owner,
                    repo: this.deploy.repository,
                    number: this.deploy.pull_request_id
                }).then((response) => {
                    this.pullrequest = response.data;
                    this.loading = false;

                    if (this.pullrequest.merged && this.deploy.status == 'merge') {
                        DeploysStore.dispatch('refresh', this.deploy);
                    }
                    resolve();
                }).catch(reject);
            });
        },
        update: function() {
            this.processing = false;
            this.loadPullrequest();
            if (this.deploy.status == 'deploy' && this.deploy.golive_id) {
                this.processing = true;
            }
            if (!this.deploy.id) {
                this.loading = true;
                ApiClient
                    .getDeploy(this.$route.params.id)
                    .then(
                        (response) => {
                            this.loading = false;
                            this.localDeploy = response.data;
                            this.loadPullrequest();
                        },
                        () => {
                            this.$router.push({ name: 'user-deploys' });
                        }
                    )
            }
        },
        cancel: function(confirm) {
            if (!confirm) {
                this.modal.modal('show');
                return;
            }

            this.modal.modal('hide');
            DeploysStore.dispatch('cancel', this.deploy)
                .then(() => {
                    this.$router.push({ name: 'user-deploys' });
                });
        },
        goliveStatus: function(status) {
            if (status == 'success' && this.deploy.status == 'deploy') {
                DeploysStore.dispatch('refresh', this.deploy)
                    .then(() => { this.processing = false });
            }
        }
    },
    watch: {
        $route: function() {
            this.update();
        },
        deploy: function() {
            if (!this.deploy.id) {
                this.update();
            }
        }
    },
    components: {
        'loading-spinner': require('../loading-spinner.vue'),
        'github-pullrequest': require('../github/pullrequest.vue'),
        'deploy': require('./deploy.vue'),
        'deploy-queue': require('./queue.vue'),
        'golive-deploy': require('../golive/deploy.vue')
    }
};
</script>

<template>
    <div>
        <div class="page-header">
            <h1>Deployment <small>{{ deploy.owner }}/{{ deploy.repository }}</small></h1>
        </div>
        <template v-if="!loading">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        Deployment process
                    </h3>
                </div>
                <div class="panel-body">
                   <div class="alert alert-danger" role="alert" v-if="errors.merge">
                        <p>
                            An error occured during the merge of this pullrequest. Please,
                            <a :href="pullrequest.html_url" target="_blank" class="alert-link">make sure it has not been updated</a>
                            since you loaded this page.
                        </p>
                        <p>
                            You should <a href="#" v-on:click.prevent="update" class="alert-link">refresh this pullrequest</a>
                            before trying to merge it again.
                        </p>
                    </div>
                    <deploy-queue v-if="deploy.status == 'queued'" v-bind:deploy="deploy"></deploy-queue>
                    <div class="panel panel-default" v-else>
                        <div class="panel-heading">
                            <h3 class="panel-title">
                                Deployment
                            </h3>
                        </div>
                        <div class="list-group">
                            <deploy v-bind:deploy="deploy" class="list-group-item"></deploy>
                        </div>
                    </div>
                    <github-pullrequest v-on:refresh="loadPullrequest" v-if="pullrequest" v-bind:pullrequest="pullrequest">
                    </github-pullrequest>
                    <golive-deploy v-on:status="goliveStatus" v-if="deploy.golive_id" v-bind:id="deploy.golive_id">
                    </golive-deploy>
                    <template v-if="deploy.status == 'waiting' && actionButtons">
                        <div class="alert alert-warning" role="alert"><p>
                            Before confirming your deployment is over, dont forget to
                            <strong>check that the project is still working in production</strong>.
                        </p></div>
                    </template>
                    <div class="pull-right" v-if="actionButtons">
                        <button type="button" @click="cancel(false)" class="btn btn-danger" v-if="cancelButton" v-bind:disabled="processing">
                            <span class="glyphicon glyphicon-remove"></span> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" v-on:click="next" v-bind:disabled="!actionButton">
                            <loading-spinner class="inline" v-if="processing"></loading-spinner>
                            {{ actionLabel }} <span class="glyphicon glyphicon-chevron-right"></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
        <loading-spinner class="medium" v-else></loading-spinner>
        <div class="modal confirm-dialog">
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
