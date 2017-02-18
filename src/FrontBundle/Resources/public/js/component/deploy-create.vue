<script>
var DeploysStore = require('../store/deploys.js');
var GithubClient = require('../lib/github-client.js');

module.exports = {
    data: function() {
        return {
            deploy: {
            },
            loading: true,
            pullrequest: false,
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
            GithubClient.getPullRequest(pr)
                .then(function(response) {
                    this.pullrequest = response.data;
                    this.deploy.description = this.pullrequest.title;
                    this.loading = false;
                }.bind(this))
                .catch(function(response) {
                    this.$router.push({ name: 'deploy-by-pullrequest' });
                }.bind(this))
            ;
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
            DeploysStore.dispatch('create', this.deploy)
                .then(function(response) {
                    this.$router.push({
                        name: 'deploy-process',
                        params: { id: response.data.entity.id }
                    });
                }.bind(this))
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
    },
    components: {
        'loading-spinner': require('./loading-spinner.vue'),
        'github-pullrequest': require('./github-pullrequest.vue')
    }
};
</script>

<template>
    <div>
        <div class="page-header">
            <h1>Let's go prod! <small>You're about to create a new deployment</small></h1>
        </div>
        <template v-if="!loading">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        New deployment
                    </h3>
                </div>
                <div class="panel-body">
                    <div class="alert alert-danger" role="alert" v-if="this.errors.global && this.errors.global.length > 0">
                        <p v-for="error in this.errors.global">{{ error }}</p>
                    </div>
                    <github-pullrequest v-bind:pullrequest="pullrequest"></github-pullrequest>
                    <template v-if="!pullrequest || (pullrequest.mergeable || pullrequest.merged)">
                        <div class="form-group">
                            <label for="repositoryName">Repository</label>
                            <input class="form-control" id="repositoryName" v-model="repositoryName" disabled="disabled">
                        </div>

                        <div class="form-group" v-bind:class="{'has-error':this.errors.fields.description}">
                            <label for="description">Description</label>
                            <input class="form-control" id="description" placeholder="Explain in a few words what you're about to deploy" v-model="deploy.description">
                            <span id="helpBlock" class="help-block" v-if="this.errors.fields.description">
                                {{ this.errors.fields.description }}
                            </span>
                        </div>
                    </template>
                    <div class="pull-right">
                        <template v-if="!pullrequest || (pullrequest.mergeable || pullrequest.merged)">
                            <button type="button" class="btn btn-primary" v-on:click="create">New deployment</button>
                        </template>
                    </div>
                </div>
            </div>
        </template>
        <loading-spinner v-else class="medium"></loading-spinner>
    </div>
</template>
