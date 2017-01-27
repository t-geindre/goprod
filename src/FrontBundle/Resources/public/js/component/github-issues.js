require('./loading-spinner');
require('./pagination');
require('./github-issue');

var Vue          = require('vue');
var GithubClient = require('../lib/github-client');
var UserStore    = require('../store/user');

module.exports = Vue.component('github-issues', {
    delimiters: ['[[', ']]'],
    template: '#github-issues-template',
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
                per_page: 20,
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
            .then(function(response) {
                this.pagination.pages = Math.ceil(
                    response.data.total_count / this.pagination.per_page
                );
                this.issues = response.data.items;
                this.loading = false;
            }.bind(this));
        },
        setSort: function(type, order) {
            this.sort = type;
            this.order = order;
            this.update();
        },
        queryUpdate: function(remove, add) {
            var items = this.query.toLowerCase().split(' ');
            remove.forEach(function(remove) {
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
    }
});
