<script>
const UserStore = require('../../store/user.js')
const DeploysStore = require('../../store/deploys.js')
const ApiClient = require('../../lib/api-client.js')
const GithubClient = require('../../lib/github-client.js')
const jQuery = require('jquery')

module.exports = {
  props: ['id'],
  data: () => ({
    pullrequest: false,
    loading: true,
    modal: false,
    processing: false,
    errors: {},
    deployProcess: {},
    localDeploy: {user: {}},
    deleteBranch: true,
    newTag: '',
    latestRealease: false,
    semver: []
  }),
  mounted: function () {
    this.deployProcess = {
      queued: {
        cancel: true,
        action: false,
        actionLabel: 'Please wait...'
      },
      merge: {
        action: () => DeploysStore
          .dispatch('merge', this.deploy, this.pullrequest.merge_commit_sha)
          .then(() => {
            var promises = [
              this.loadPullrequest(false)
            ];
            if (this.deleteBranch) {
              promises.push(
                GithubClient.deleteReference(
                  this.deploy.owner,
                  this.deploy.repository,
                  'heads/' + this.pullrequest.head.ref
                )
              );
            }
            if (this.newTag.length > 0) {
              promises.push(
                GithubClient.createNewRelease(
                  this.deploy.owner,
                  this.deploy.repository,
                  { tag_name: this.newTag }
                )
              );
            }
            return Promise.all(promises);
          })
          .then(() => { this.processing = false; })
          .catch(() => {
            this.processing = false;
            this.errors.merge = true;
          }),
        actionLabel: 'Merge pullrequest',
        cancel: true
      },
      deploy: {
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
    this.modal = jQuery('.confirm-dialog', this.template).modal({ show: false });
    this.update();
  },
  computed: {
    actionButtons: function () {
      return (
       this.deploy.id &&
       this.deploy.user.login === this.user.login &&
       (
        this.deployProcess[this.deploy.status].action ||
        this.deployProcess[this.deploy.status].cancel
       )
      );
    },
    actionLabel: function () {
      return this.deployProcess[this.deploy.status].actionLabel;
    },
    actionButton: function () {
      return this.deployProcess[this.deploy.status].action && !this.processing;
    },
    cancelButton: function () {
      return this.deployProcess[this.deploy.status].cancel;
    },
    deploy: function () {
      var deploy = DeploysStore.state.deploys.find(
        (deploy) => deploy.id === this.id
      );
      if (deploy === undefined) {
        return this.localDeploy;
      }
      return deploy;
    },
    user: () => UserStore.state.user
  },
  methods: {
    next: function () {
      if (!this.processing && this.deployProcess[this.deploy.status].action) {
        this.processing = true;
        this.deployProcess[this.deploy.status].action();
      }
    },
    loadPullrequest: function (loading = true) {
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
        })
          .then((response) => {
            this.pullrequest = response.data;

            if (this.pullrequest.merged && this.deploy.status === 'merge') {
              DeploysStore.dispatch('refresh', this.deploy);
            }

            return GithubClient.getLastestRelease(
              this.deploy.owner,
              this.deploy.repository
            );
          })
          .then((response) => {
            this.latestRealease = response.data.tag_name;
            this.loading = false;
            resolve()
          })
          .catch(() => {
            this.loading = false;
            resolve();
          });
      });
    },
    update: function () {
      this.localDeploy = {user: {}};
      this.processing = false;
      this.deleteBranch = true;
      this.newTag = '';
      this.latestRealease = false;
      this.loadPullrequest();
      if (this.deploy.status === 'deploy' && this.deploy.golive_id) {
        this.processing = true;
      }
      if (!this.deploy.id) {
        this.loading = true;
        ApiClient
        .getDeploy(this.id)
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
    cancel: function (confirm) {
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
    goliveStatus: function (status) {
      if (status === 'success' && this.deploy.status === 'deploy') {
        DeploysStore.dispatch('refresh', this.deploy)
        .then(() => { this.processing = false });
      }
      if (status === 'failure') {
        this.processing = false;
      }
    }
  },
  watch: {
    id: function () {
      this.update();
    },
    latestRealease: function () {
      this.semver = [];
      if (this.latestRealease) {
        var matches = this.latestRealease.match(/^([v]*)([0-9]+)\.([0-9]+)\.([0-9]+)$/);
        if (matches) {
          this.semver = [
            { version: matches[1] + (parseInt(matches[2]) + 1) + '.0.0', label: 'major' },
            { version: matches[1] + matches[2] + '.' + (parseInt(matches[3]) + 1) + '.0', label: 'minor' },
            { version: matches[1] + matches[2] + '.' + matches[3] + '.' + (parseInt(matches[4]) + 1), label: 'patch' }
          ];
        }
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
          <h3 class="panel-title">Deployment process</h3>
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
              <h3 class="panel-title">Deployment</h3>
            </div>
            <div class="list-group">
              <deploy v-bind:deploy="deploy" class="list-group-item"></deploy>
            </div>
          </div>
          <github-pullrequest v-on:refresh="loadPullrequest" v-if="pullrequest" v-bind:pullrequest="pullrequest">
          </github-pullrequest>
          <div class="panel panel-info" v-if="deploy.status == 'merge' && actionButtons && !processing">
            <div class="panel-heading">
              <h3 class="panel-title">Merge options</h3>
            </div>
            <div class="panel-body">
              <div class="form-group" v-if="latestRealease">
                <label for="newTagInput">Create a new release (current <code>{{ latestRealease }}</code>):</label>
                <div v-bind:class="{ 'input-group': semver.length > 0 }">
                  <input class="form-control" v-model="newTag" id="newTagInput" />
                  <div class="input-group-btn" v-if="semver.length > 0">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      Semver <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                      <li v-for="version in semver">
                        <a href="#" v-on:click.prevent="newTag = version.version">
                          <code>{{ version.version }}</code>
                          <em>{{ version.label }}</em>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
              <label>Delete associated branch:</label>
              <div class="form-group checkbox">
                <label>
                  <input type="checkbox" v-model="deleteBranch" />
                  <code>{{ pullrequest.head.ref }}</code>
                </label>
              </div>
            </div>
          </div>
          <golive-deploy v-on:status="goliveStatus" v-if="deploy.golive_id" v-bind:id="deploy.golive_id">
          </golive-deploy>
          <template v-if="deploy.status == 'waiting' && actionButtons">
            <div class="alert alert-warning" role="alert">
              <p>
                Before confirming your deployment is over, dont forget to
                <strong>check that the project is still working in production</strong>.
              </p>
            </div>
          </template>
          <div class="pull-right" v-if="actionButtons">
            <button type="button" @click="cancel(false)" class="btn btn-danger cancel-deploy" v-if="cancelButton" v-bind:disabled="processing">
              <span class="glyphicon glyphicon-remove"></span> Cancel
            </button>
            <button type="button" class="btn btn-primary action-button" v-on:click="next" v-bind:disabled="!actionButton">
              <loading-spinner class="inline" v-if="processing"></loading-spinner>
              {{ actionLabel }} <span class="glyphicon glyphicon-chevron-right"></span>
            </button>
          </div>
        </div>
      </div>
    </template>
    <loading-spinner class="medium" v-else></loading-spinner>
    <div class="modal confirm-dialog cancel-deployment-modal">
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
            <button type="button" v-on:click="cancel(true)" class="btn btn-danger confirm-cancel-deploy">Cancel deployment</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
