<script>
var ApiClient = require('../lib/api-client.js');
var UserStore = require('../store/user.js');

module.exports = {
    props: {
        status:     { default: '' },
        sortBy:     { default: 'id' },
        sortOrder:  { default: 'desc' },
        owner:      { default: null },
        repository: { default: null },
        userName:   { default: '' },
        userId:     { default: null },
        limit:      { default: 10 },
        page:       { default: 1 }
    },
    data: () => ({
        refresh: false,
        deploys: [],
        loading: true,
        pages: 1,
        fullscreen: false
    }),
    mounted: function() {
        this.update();
    },
    computed: {
        filters: function() {
            return {
                status: this.status,
                user: this.userId,
                limit: this.limit,
                offset: (this.page-1)*this.limit,
                sortBy: this.sortBy,
                sortOrder: this.sortOrder,
                owner: this.owner,
                repository: this.repository
            }
        },
        user: function() {
            return UserStore.state.user;
        },
    },
    methods: {
        update: function(loading = true) {
            this.loading = loading;
            ApiClient
                .getDeploys({ params: this.filters })
                .then((response) => {
                    this.deploys = response.data.items;
                    this.pages = response.data.total == 0 ? 1 : Math.ceil(response.data.total/this.limit);
                    if (this.page > this.pages) {
                        this.filter({page: this.pages}, true);
                    };
                    this.loading = false;
                });
        },
        filter: function(filters, replace = false) {
            this.$router[replace ? 'replace' : 'push']({
                path: this.$route.path,
                query: Object.assign({}, this.$route.query, filters)
            });
        },
        show: function(deploy) {
            this.$router.push({name: 'deploy-process', params: { id: deploy.id }});
        },
        clearAutoRefresh: function() {
            if (this.deploysRefresh) {
                clearInterval(this.deploysRefresh);
                this.deploysRefresh = false;
            }
        }
    },
    watch: {
        refresh: function() {
            this.clearAutoRefresh();
            if (this.refresh) {
                this.deploysRefresh = setInterval(() => {
                    this.update(false);
                }, 5000);
            }
        },
        filters: function() {
            this.update();
        }
    },
    components: {
        'loading-spinner': require('./loading-spinner.vue'),
        'pagination': require('./pagination.vue'),
        'deploy': require('./deploy.vue'),
        'typeahead-users': require('./typeahead/users.vue'),
        'repository-selector': require('./github/repository-selector.vue')
    },
    beforeDestroy: function() {
        this.clearAutoRefresh();
    }
};
</script>

<template>
<div>
    <div class="page-header">
        <h1>Deployments <small>search history</small></h1>
    </div>
    <div class="row form-group">
        <div class="btn-group col-md-5">
            <label class="btn btn-default" v-bind:class="{active:status==''}">
                <span class="glyphicon glyphicon-asterisk"></span>
                <input type="radio" value="" v-on:click="filter({status:''})"> All
            </label>
            <label class="btn btn-default" v-bind:class="{active:status=='active'}">
                <span class="glyphicon glyphicon-refresh"></span>
                <input type="radio" value="active" v-on:click="filter({status:'active'})"> Active
            </label>
            <label class="btn btn-default" v-bind:class="{active:status=='canceled'}">
                <span class="glyphicon glyphicon-remove"></span>
                <input type="radio" value="canceled" v-on:click="filter({status:'canceled'})"> Canceled
            </label>
            <label class="btn btn-default" v-bind:class="{active:status=='done'}">
                <span class="glyphicon glyphicon-ok"></span>
                <input type="radio" value="done" v-on:click="filter({status:'done'})"> Done
            </label>
        </div>
        <div class="col-md-4">
            <repository-selector
                v-on:select="filter({repository: $event.repo, owner: $event.owner})"
                v-bind:owner="owner"
                v-bind:repo="repository"
            ></repository-selector>
        </div>
        <div class="col-md-3">
            <div class="input-group">
                <div class="input-group-addon">
                    <span class="glyphicon glyphicon-user"></span>
                </div>
                <typeahead-users
                    display-field="name" class="form-control" min-length="2"
                    v-on:select="filter({userId: $event.id, userName: $event.name })"
                    v-on:clear="filter({userId: '', userName: '' })"
                    v-bind:default-value="userName"
                    placeholder="user"
                >
                </typeahead-users>
            </div>
        </div>
    </div>
    <div class="panel panel-default" v-bind:class="{ fullscreen: fullscreen }">
        <div class="panel-heading">
            <div class="btn-group btn-group-sm">
                <label class="btn btn-default" v-bind:class="{active:refresh}">
                    <input type="checkbox" v-model="refresh" />
                    <span class="glyphicon glyphicon-ok" v-if="refresh"></span>
                    <span class="glyphicon glyphicon-remove" v-if="!refresh"></span>
                    Auto refresh
                </label>
            </div>
            <div class="dropdown pull-right">
                <button class="btn btn-sm btn-default dropdown-toggle" type="button" data-toggle="dropdown">
                    Sort <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="#" v-on:click.prevent="filter({ sortBy:'id', sortOrder:'desc'})">Newest</a></li>
                    <li><a href="#" v-on:click.prevent="filter({ sortBy:'id', sortOrder:'asc'})">Oldest</a></li>
                </ul>
            </div>
            <div class="dropdown pull-right">
                <button class="btn btn-sm btn-default dropdown-toggle" type="button" data-toggle="dropdown">
                    Display <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="#" v-on:click.prevent="filter({limit: 10})">10</a></li>
                    <li><a href="#" v-on:click.prevent="filter({limit: 20})">20</a></li>
                    <li><a href="#" v-on:click.prevent="filter({limit: 30})">30</a></li>
                    <li><a href="#" v-on:click.prevent="filter({limit: 50})">50</a></li>
                </ul>
            </div>
            <div class="btn-group btn-group-sm pull-right">
                <label class="btn btn-default" v-bind:class="{active:fullscreen}">
                    <input type="checkbox" name="haha" v-model="fullscreen" />
                    <span class="glyphicon glyphicon-fullscreen"></span>
                </label>
            </div>
        </div>
        <div v-if="!loading && deploys.length > 0" class="list-group">
                <deploy v-for="deploy in deploys" v-bind:deploy="deploy" v-on:click="show(deploy)" class="list-group-item"></deploy>
        </div>
        <p v-else-if="!loading" class="text-center">No {{ status }} deployment found.</p>
        <loading-spinner class="medium" v-else></loading-spinner>
        <div class="panel-footer text-center">
            <pagination
                v-bind:pages="pages"
                v-bind:page="page"
                v-on:page="filter({page:$event})"
                v-if="!loading"
            >
            </pagination>
        </div>
    </div>
</div>
</template>

<style scoped>
    .pagination {
        margin: 0;
    }

    label.btn > input {
      display: none;
    }
    p {
        margin: 10px 5px;
    }
    div.fullscreen {
        position: fixed;
        top:0;
        left:0;
        right:0;
        bottom: 0;
        z-index:9000;
        overflow: auto;
    }

    .btn-group.pull-right,
    .dropdown.pull-right {
        padding-left: 5px;
    }
</style>
