<script>
var ApiClient = require('../lib/api-client.js');
var UserStore = require('../store/user.js');

module.exports = {
    data: function() {
        return {
            status: '',
            refresh: false,
            userId: '',
            deploys: [],
            loading: true,
            total: 0,
            pages: 1,
            page: 1,
            limit: 10,
            sortBy: 'id',
            sortOrder: 'desc',
            fullscreen: false,
            userDisplay: ''

        }
    },
    mounted: function() {
        this.update();
    },
    computed: {
        filters: function() {
            return {
                status: this.status,
                user: this.userId,
                limit: this.limit,
                offset: (this.page - 1) * this.limit,
                sortBy: this.sortBy,
                sortOrder: this.sortOrder
            }
        },
        user: function() {
            return UserStore.state.user;
        },
    },
    methods: {
        update: function(page = 1, loading = true) {
            this.page = page;
            this.loading = loading;
            ApiClient
                .getDeploys({ params: this.filters })
                .then((response) => {
                    this.deploys = response.data.items;
                    this.total = response.data.total;
                    this.computePages();
                    this.loading = false;
                });
        },
        sort: function(field, order) {
            this.sortBy = field;
            this.sortOrder = order;
            this.update();
        },
        goToDeploy: function(deploy) {
            if (deploy.user.id == this.user.id
                && ['merge', 'deploy', 'waiting'].indexOf(deploy.status) > -1
            ) {
                this.$router.push({
                    name: 'deploy-process',
                    params: { id: deploy.id }
                });
            }
        },
        toggleFullscreen: function() {
            this.fullscreen = !this.fullscreen;
        },
        display: function(items) {
            this.limit = items;
            this.update();
        },
        registerDeploysRefresh: function() {
            this.deploysRefresh = setInterval(() => {
                this.update(this.page, false);
            }, 5000);
        },
        clearDeploysRefresh: function() {
            if (this.deploysRefresh) {
                clearInterval(this.deploysRefresh);
                this.deploysRefresh = false;
            }
        },
        selectUser: function(user) {
            this.userId = user ? user.id : '';
            this.update();
        },
        computePages: function() {
            this.pages = this.total == 0 ? 1 : Math.ceil(this.total/this.limit);
            this.page = this.page > this.pages ? this.pages : this.page;
        }
    },
    watch: {
        status: function() {
            this.update();
        },
        refresh: function() {
            this.clearDeploysRefresh();
            if (this.refresh) {
                this.registerDeploysRefresh();
            }
        }
    },
    components: {
        'loading-spinner': require('./loading-spinner.vue'),
        'pagination': require('./pagination.vue'),
        'deploy': require('./deploy.vue'),
        'typeahead-users': require('./typeahead/users.vue')
    },
    beforeDestroy: function() {
        this.clearDeploysRefresh();
    }
};
</script>

<template>
<div>
    <div class="page-header">
        <h1>Deployments <small>search history</small></h1>
    </div>
    <div class="row form-group">
        <div class="btn-group col-md-6">
            <label class="btn btn-default" v-bind:class="{active:status==''}">
                <span class="glyphicon glyphicon-asterisk"></span>
                <input type="radio" value="" v-model="status"> All
            </label>
            <label class="btn btn-default" v-bind:class="{active:status=='active'}">
                <span class="glyphicon glyphicon-refresh"></span>
                <input type="radio" value="active" v-model="status"> Active
            </label>
            <label class="btn btn-default" v-bind:class="{active:status=='canceled'}">
                <span class="glyphicon glyphicon-remove"></span>
                <input type="radio" value="canceled" v-model="status"> Canceled
            </label>
            <label class="btn btn-default" v-bind:class="{active:status=='done'}">
                <span class="glyphicon glyphicon-ok"></span>
                <input type="radio" value="done" v-model="status"> Done
            </label>
        </div>
        <div class="col-md-3">
        <!--
            <div class="input-group">
                <div class="input-group-addon">
                    <span class="glyphicon glyphicon-tasks"></span>
                </div>
                <typeahead target-element="#deploysRepository" :data="searchUser" display-field="name"></typeahead>
                <input type="text" class="form-control" id="deploysRepository" placeholder="repository" v-model="userDisplay" />
            </div>
        -->
        </div>
        <div class="col-md-3">
            <div class="input-group">
                <div class="input-group-addon">
                    <span class="glyphicon glyphicon-user"></span>
                </div>
                <typeahead-users display-field="name" class="form-control" min-length="2" v-on:select="selectUser" v-on:clear="selectUser(false)">
                </typeahead-users>
                <!-- <input id="deploysUser" type="text" class="form-control" placeholder="user" v-model="userDisplay" autocomplete="off" /> -->
            </div>
        </div>
    </div>
    <div class="panel panel-default" v-bind:class="{ fullscreen: fullscreen }">
        <div class="panel-heading">
            <div class="btn-group btn-group-sm">
                <label class="btn btn-default" v-bind:class="{active:refresh}">
                    <input type="radio" name="open" v-model="refresh" v-bind:value="!refresh">
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
                    <li><a href="#" v-on:click.prevent="sort('id', 'desc')">Newest</a></li>
                    <li><a href="#" v-on:click.prevent="sort('id', 'asc')">Oldest</a></li>
                </ul>
            </div>
            <div class="dropdown pull-right">
                <button class="btn btn-sm btn-default dropdown-toggle" type="button" data-toggle="dropdown">
                    Display <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="#" v-on:click.prevent="display(10)">10</a></li>
                    <li><a href="#" v-on:click.prevent="display(20)">20</a></li>
                    <li><a href="#" v-on:click.prevent="display(30)">30</a></li>
                    <li><a href="#" v-on:click.prevent="display(50)">50</a></li>
                </ul>
            </div>
            <div class="btn-group btn-group-sm pull-right">
                <button class="btn btn-default" v-on:click="toggleFullscreen" v-bind:class="{active:fullscreen}">
                    <span class="glyphicon glyphicon-fullscreen"></span>
                </button>
            </div>
        </div>
        <div v-if="!loading && deploys.length > 0" class="list-group">
                <deploy v-for="deploy in deploys" v-bind:deploy="deploy" v-on:click="goToDeploy(deploy)" class="list-group-item"></deploy>
        </div>
        <p v-else-if="!loading" class="text-center">No {{ status }} deployment found.</p>
        <loading-spinner class="medium" v-else></loading-spinner>
        <div class="panel-footer text-center">
            <pagination
                v-bind:pages="pages"
                v-bind:page="page"
                v-on:page="update"
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

    label.btn > input[type='radio'] {
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
