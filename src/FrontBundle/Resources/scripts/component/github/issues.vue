<script>
var GithubClient = require('../../lib/github-client.js');
var UserStore    = require('../../store/user.js');

module.exports = {
    props: ['queryAppend'],
    computed: {
        user: function() {
            return UserStore.state.user;
        }
    },
    data: function() {
        return {
            issues: [],
            sort: 'created',
            order: 'desc',
            open: true,
            iam: 'author',
            query: '',
            loading: false,
            pagination: {
                per_page: 10,
                page: 1,
                pages: 1
            }
        }
    },
    mounted: function() {
        this.query = 'is:open author:'+this.user.login;
        this.update();
    },
    methods: {
        update: function() {
            this.loading = true;
            GithubClient.searchIssues({
                q: this.query + (this.queryAppend ? ' '+this.queryAppend : ''),
                sort: this.sort,
                order: this.order,
                per_page: this.pagination.per_page,
                page: this.pagination.page
            })
            .then((response) => {
                this.pagination.pages = Math.ceil(
                    response.data.total_count / this.pagination.per_page
                );
                this.issues = response.data.items;
                this.loading = false;
            });
        },
        setSort: function(type, order) {
            this.sort = type;
            this.order = order;
            this.update();
        },
        queryUpdate: function(remove, add) {
            var items = this.query.toLowerCase().split(' ');
            remove.forEach(function(remove) {
                var index;
                while ((index = items.indexOf(remove)) > -1) {
                    items.splice(index, 1);
                }
            })
            items.unshift(add);
            this.query = items.join(' ');
        },
        goToPage: function(page) {
            this.pagination.page = page;
            this.update();
        }
    },
    watch: {
        open: function() {
            this.queryUpdate(
                ['is:closed', 'is:open'],
                this.open ? 'is:open' : 'is:closed'
            );
            this.goToPage(1);
        },
        iam: function() {
            this.queryUpdate(
                [
                    'author:'+this.user.login,
                    'assignee:'+this.user.login,
                    'mentions:'+this.user.login
                ],
                this.iam+':'+this.user.login
            );
            this.goToPage(1);
        }
    },
    components: {
        'pagination': require('../pagination.vue'),
        'loading-spinner': require('../loading-spinner.vue'),
        'github-issue': require('./issue.vue')
    }
};
</script>

<template>
<div class="github-issues">
    <div class="row form-group">
        <div class="btn-group col-md-5">
            <label class="btn btn-default" v-bind:class="{active:iam=='author'}">
                <input type="radio" value="author" v-model="iam" name="iam"> Created
            </label>
            <label class="btn btn-default" v-bind:class="{active:iam=='assignee'}">
                <input type="radio" value="assignee" v-model="iam" name="iam"> Assigned
            </label>
            <label class="btn btn-default" v-bind:class="{active:iam=='mentions'}">
                <input type="radio" value="mentions" v-model="iam" name="iam"> Mentioned
            </label>
        </div>
        <div class="col-md-7">
            <div class="input-group">
                <div class="input-group-addon">
                    <span class="glyphicon glyphicon-search"></span>
                </div>
                <input type="text" class="form-control" id="query" v-model="query" v-on:keyup.enter="update" />
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="btn-group btn-group-sm">
                <label class="btn btn-default" v-bind:class="{active:open}">
                    <input type="radio" name="open" v-model="open" v-bind:value="true">
                    <span class="glyphicon glyphicon-ok"></span> Open
                </label>
                <label class="btn btn-default"  v-bind:class="{active:!open}">
                    <input type="radio" name="open" v-model="open" v-bind:value="false">
                    <span class="glyphicon glyphicon-remove"></span> Closed
                </label>
            </div>
            <div class="dropdown pull-right">
                <button class="btn btn-sm btn-default dropdown-toggle" type="button" data-toggle="dropdown">
                    Sort <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="#" v-on:click.prevent="setSort('created', 'desc')">Newest</a></li>
                    <li><a href="#" v-on:click.prevent="setSort('created', 'asc')">Oldest</a></li>
                    <li><a href="#" v-on:click.prevent="setSort('comments', 'desc')">Most commented</a></li>
                    <li><a href="#" v-on:click.prevent="setSort('comments', 'asc')">Least commented</a></li>
                    <li><a href="#" v-on:click.prevent="setSort('updated', 'desc')">Recently updated</a></li>
                    <li><a href="#" v-on:click.prevent="setSort('updated', 'asc')">Least recently updated</a></li>
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
                v-bind:pages="pagination.pages"
                v-bind:page="pagination.page"
                v-on:page="goToPage"
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
</style>
