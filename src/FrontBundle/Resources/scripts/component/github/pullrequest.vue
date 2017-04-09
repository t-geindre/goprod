<script>
const GithubClient = require('../../lib/github-client.js')

module.exports = {
  props: ['pullrequest'],
  data: () => ({
    loading: true,
    issue: {}
  }),
  methods: {
    update: function () {
      this.loading = true;
      GithubClient.getIssue({
        owner: this.pullrequest.base.repo.owner.login,
        repo: this.pullrequest.base.repo.name,
        number: this.pullrequest.number
      })
      .then((response) => {
        this.issue = response.data;
        this.loading = false;
      });
    }
  },
  computed: {
    unmergeable: function () {
      return !this.pullrequest.merged && !this.pullrequest.mergeable;
    },
    unstable: function () {
      return (
       !this.pullrequest.merged &&
       this.pullrequest.mergeable &&
       this.pullrequest.mergeable_state !== 'clean'
      );
    }
  },
  mounted: function () {
    this.update();
  },
  components: {
    'loading-spinner': require('../loading-spinner.vue'),
    'github-issue': require('./issue.vue')
  },
  watch: {
    pullrequest: function () {
      this.update();
    }
  }
};
</script>

<template>
  <div>
    <div
      class="panel panel-success"
      v-if="issue && pullrequest"
      v-bind:class="{
        'panel-danger': unmergeable,
        'panel-warning': unstable
      }"
    >
      <div class="panel-heading">
        <div class="btn-group-xs pull-right">
          <a href="#" class="btn btn-link" v-on:click.prevent="$emit('refresh')">
            <span class="glyphicon glyphicon-refresh"></span> Refresh
          </a>
          <a :href="pullrequest.html_url" target="_blank" class="btn btn-link">
            <span class="glyphicon glyphicon-share"></span> View on Github
          </a>
        </div>
        <h3 class="panel-title">Pull Request</h3>
      </div>
      <div class="list-group">
        <github-issue v-bind:issue="issue" class="list-group-item" v-if="!loading">
        </github-issue>
        <loading-spinner v-else></loading-spinner>
      </div>
    </div>
    <div class="alert alert-danger" role="alert" v-if="unmergeable">
      <p>
        This Pull Request is not merged yet and is not mergeable.
        Please <a :href="pullrequest.html_url" target="_blank" class="alert-link">fix this</a>
        before going forward.
      </p>
    </div>
    <div class="alert alert-warning" role="alert" v-else-if="unstable">
      <p>
        This Pull Request is mergeable but is in an unstable state. You shlould consider
        <a :href="pullrequest.html_url" target="_blank" class="alert-link">fixing it</a>
        before going forward.
      </p>
    </div>
  </div>
</template>
