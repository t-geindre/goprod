<script>
var DeploysStore = require('../store/deploys.js');
var GithubClient = require('../lib/github-client.js');
var jQuery       = require('jquery');

module.exports = {
    data: function() {
        return {
            pullrequest: {},
            loading: true,
            modal: false,
            processing: false,
            mergeError: false,
            previousStatus: false
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
                'waiting': 'Confirm deployment is over',
                'queued': 'Please wait...'
            })[this.deploy.status];
        },
        actionButton: function() {
            return ['merge', 'deploy', 'waiting'].indexOf(this.deploy.status) > -1 && !this.processing;
        },
        cancelButton: function() {
            return ['done', 'new', 'canceled'].indexOf(this.deploy.status) == -1;
        },
        deploy: function() {
            if (DeploysStore.state.deploys[this.$route.params.id]) {
                return DeploysStore.state.deploys[this.$route.params.id];
            }

            this.$router.push({ name: 'user-deploys' });

            return { user:{} };
        },
        previousStatusMessage: function() {
            if (this.previousStatus
                && this.previousStatus != this.deploy.status
            ) {
                var messages = {
                    'merge': 'Pullrequest has been merged'
                };
                if (messages[this.previousStatus]) {
                    return messages[this.previousStatus];
                }
            }

            return false;
        }
    },
    methods: {
        update: function() {
            this.mergeError = false;
            this.previousStatus = this.deploy.status;
            if (
                !this.deploy.pull_request_id
                || this.deploy.status != 'merge'
                || this.processing
            ) {
                this.loading = false;
                return;
            }

            this.loading = true;
            GithubClient.getPullRequest({
                owner: this.deploy.owner,
                repo: this.deploy.repository,
                number: this.deploy.pull_request_id
            }).then((response) => {
                this.pullrequest = response.data;
                this.loading = false;
            });
        },
        cancel: function(confirm) {
            if (!confirm) {
                this.modal.modal('show');
                return;
            }

            this.modal.modal('hide');
            DeploysStore.dispatch('cancel', this.deploy);
        },
        next: function() {
            if (this.processing) {
                return;
            }
            this.previousStatus = this.deploy.status;
            this.processing = true;
            this[{
                'merge': 'merge',
                'waiting': 'confirm'
            }[this.deploy.status]]();
        },
        merge : function() {
            DeploysStore.dispatch(
                'merge',
                this.deploy,
                this.pullrequest.merge_commit_sha
            ).then(
                () => { this.processing = false; },
                () => {
                    this.processing = false;
                    this.mergeError = true;
                }
            );
        },
        confirm: function() {
            DeploysStore.dispatch('confirm', this.deploy);
        }
    },
    watch: {
        $route: function() {
            this.processing = false;
            this.update();
        },
        pullrequest: function() {
            if (this.pullrequest.merged && this.deploy.status == 'merge') {
                DeploysStore.dispatch('refresh', this.deploy);
            }
        }
    },
    components: {
        'loading-spinner': require('./loading-spinner.vue'),
        'github-pullrequest': require('./github-pullrequest.vue'),
        'deploy': require('./deploy.vue'),
        'deploy-queue': require('./deploy-queue.vue')
    }
};
</script>

<template>
    <div>
        <div class="page-header">
            <h1>
                Deployment
                <small>{{ deploy.owner }}/{{ deploy.repository }}</small>
            </h1>
        </div>
        <template v-if="!loading">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        {{ deploy.description }}
                    </h3>
                </div>
                <div class="panel-body">
                    <div class="alert alert-success" role="alert" v-if="previousStatusMessage">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <p>{{ previousStatusMessage }}</p>
                    </div>
                   <div class="alert alert-danger" role="alert" v-if="mergeError">
                        <p>
                            An error occured during the merge of this pullrequest. Please,
                            <a :href="pullrequest.html_url" target="_blank" class="alert-link">make sure it has not been updated</a>
                            since you loaded this page.
                        </p>
                        <p>
                            You should<a href="#" v-on:click.prevent="update" class="alert-link">refresh this pullrequest</a>
                            before trying to merge it again.
                        </p>
                    </div>
                    <github-pullrequest v-on:refresh="update" v-if="deploy.status == 'merge'" v-bind:pullrequest="pullrequest">
                    </github-pullrequest>
                    <deploy-queue v-if="deploy.status == 'queued'" v-bind:deploy="deploy"></deploy-queue>
                    <template v-if="deploy.status == 'waiting'">
                        <div class="alert alert-warning" role="alert"><p>
                            Before confirming your deployment is over, dont forget to
                            <strong>check that the project is still working in production</strong>.
                        </p></div>
                    </template>
                    <div class="pull-right">
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
