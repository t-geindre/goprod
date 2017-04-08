<script>
const GithubClient = require('../../lib/github-client.js')
const UserStore = require('../../store/user.js')

module.exports = {
  props: {
    sort: { default: 'created' },
    order: { default: 'desc' },
    open: { default: true },
    userIs: { default: 'author' },
    userLogin: { default: '' },
    userName: { default: '' },
    page: { default: 1 },
    owner: { default: '' },
    repository: { default: '' },
    type: { default: '' }
  },
  data: () => ({
    issues: [],
    loading: false,
    byPage: 10,
    pages: 1
  }),
  mounted: function () {
    this.update();
  },
  computed: {
    user: () => UserStore.state.user,
    filters: function () {
      var query = this.type === 'pullrequest' ? 'is:pr ' : '';
      if (this.userIs.length > 0 && this.userLogin.length > 0) {
        query += this.userIs + ':' + this.userLogin + ' ';
      }
      if (this.owner.length > 0) {
        if (this.repository.length > 0) {
          query += 'repo:' + this.owner + '/' + this.repository + ' ';
        } else {
          query += 'user:' + this.owner + ' ';
        }
      }
      if (!this.open) {
        query += 'is:' + (this.type === 'pullrequest' ? 'merged' : 'closed') + ' ';
      } else {
        query += 'is:open';
      }
      return {
        q: query,
        sort: this.sort,
        order: this.order,
        per_page: this.byPage,
        page: this.page
      };
    }
  },
  methods: {
    update: function () {
      this.loading = true;
      GithubClient.searchIssues(this.filters)
      .then((response) => {
        this.pages = Math.max(1, Math.ceil(
        response.data.total_count / this.byPage
       ));
        if (this.page > this.pages) {
          this.filter({ page: this.pages }, true);
        }
        this.issues = response.data.items;
        this.loading = false;
      });
    },
    filter: function (filters, replace = false) {
      this.$emit('filter', filters, replace);
    }
  },
  watch: {
    filters: function () {
      this.update();
    }
  },
  components: {
    'pagination': require('../pagination.vue'),
    'loading-spinner': require('../loading-spinner.vue'),
    'github-issue': require('./issue.vue'),
    'typeahead-users': require('../typeahead/users.vue'),
    'repository-selector': require('../github/repository-selector.vue')
  }
};
</script>

<template>
  <div class="github-issues">
    <div class="row form-group">
      <div class="col-md-3">
        <div class="input-group">
          <div class="input-group-addon">
            <span class="glyphicon glyphicon-user"></span>
          </div>
          <typeahead-users
            display-field="name" class="form-control" min-length="2"
            v-on:select="filter({userLogin: $event.login, userName: $event.name })"
            v-on:clear="filter({userLogin: '', userName: '' })"
            v-bind:default-value="userName"
            placeholder="user"
          >
          </typeahead-users>
        </div>
      </div>
      <div class="btn-group col-md-5">
        <label class="btn btn-default" v-bind:class="{active:userIs=='author'}">
          <input type="radio" value="author" v-on:click="filter({ userIs: 'author' })" name="userIs"> Created
        </label>
        <label class="btn btn-default" v-bind:class="{active:userIs=='assignee'}">
          <input type="radio" value="assignee" v-on:click="filter({ userIs: 'assignee' })" name="userIs"> Assigned
        </label>
        <label class="btn btn-default" v-bind:class="{active:userIs=='mentions'}">
          <input type="radio" value="mentions" v-on:click="filter({ userIs: 'mentions' })" name="userIs"> Mentioned
        </label>
      </div>
      <div class="col-md-4">
        <repository-selector
          v-on:select="filter({repository: $event.repo, owner: $event.owner})"
          v-bind:owner="owner"
          v-bind:repo="repository"
        ></repository-selector>
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading">
        <div class="btn-group btn-group-sm">
          <label class="btn btn-default" v-bind:class="{active:open}">
            <input type="radio" name="open" v-on:click="filter({open: true})" />
            <span class="glyphicon glyphicon-ok"></span> Open
          </label>
          <label class="btn btn-default"  v-bind:class="{active:!open}">
            <input type="radio" name="open" v-on:click="filter({open: false})" />
            <span class="glyphicon glyphicon-remove"></span> Closed
          </label>
        </div>
        <div class="dropdown pull-right">
          <button class="btn btn-sm btn-default dropdown-toggle" type="button" data-toggle="dropdown">
            Sort <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <li><a href="#" v-on:click.prevent="filter({ sort: 'created', order: 'desc'})">Newest</a></li>
            <li><a href="#" v-on:click.prevent="filter({ sort: 'created', order: 'asc'})">Oldest</a></li>
            <li><a href="#" v-on:click.prevent="filter({ sort: 'comments', order: 'desc'})">Most commented</a></li>
            <li><a href="#" v-on:click.prevent="filter({ sort: 'comments', order: 'asc'})">Least commented</a></li>
            <li><a href="#" v-on:click.prevent="filter({ sort: 'updated', order: 'desc'})">Recently updated</a></li>
            <li><a href="#" v-on:click.prevent="filter({ sort: 'updated', order: 'asc'})">Least recently updated</a></li>
          </ul>
        </div>
      </div>
      <div v-if="!loading" class="list-group">
        <github-issue
         v-for="issue in issues"
         v-bind:issue="issue"
         v-on:select-issue="$emit('select-issue', issue)"
         class="list-group-item"
        >
        </github-issue>
      </div>
      <loading-spinner class="medium" v-else></loading-spinner>
      <div class="panel-footer text-center">
        <pagination
          v-bind:pages="pages"
          v-bind:page="page"
          v-on:page="filter({page: $event})"
          v-if="!loading"
        >
        </pagination>
      </div>
    </div>
  </div>
</template>

<style type="text/css" scoped>
  .pagination {
    margin: 0;
  }

  label.btn > input[type='radio'] {
    display: none;
  }

  .btn-group>.btn.active {
    z-index: inherit;
  }
</style>
