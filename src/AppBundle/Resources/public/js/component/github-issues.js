var Vue          = require('vue');
var GithubClient = require('../lib/github-client');

module.exports = Vue.component('github-issues', {
    delimiters: ['[[', ']]'],
    template: '#github-issues-template',
    props: ['query', 'queryAppend', 'userLogin'],
    data: function() {
        return {
            issues: [],
            sort: 'created',
            order: 'desc',
            open: true,
            iam: 'author'
        }
    },
    mounted: function() {
        this.update();
    },
    methods: {
        update: function() {
            var data = {
                q: this.query,
                sort: this.sort,
                order: this.order
            };
            if (this.queryAppend) {
                data.q += ' ' + this.queryAppend;
            }
            GithubClient.searchIssues(data).then(function(response) {
                this.issues = response.data.items;
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
        getTextLabelColor: function(background) {
            var hex = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(background);
            var rgb = {
                r: parseInt(hex[1], 16),
                g: parseInt(hex[2], 16),
                b: parseInt(hex[3], 16)
            }

            var o = Math.round((rgb.r * 299 + rgb.g * 587 + rgb.b * 114) /1000);

            if(o > 125) {
                return '#000';
            }

            return '#fff'
        }
    },
    filters: {
        "repo-name": function (value) {
            return value.split('repos')[1].slice(1);
        }
    },
    watch: {
        open: function() {
            this.queryUpdate(
                ['is:closed', 'is:open'],
                this.open ? 'is:open' : 'is:closed'
            );
            this.update();
        },
        iam: function() {
            this.queryUpdate(
                [
                    'author:'+this.userLogin,
                    'assignee:'+this.userLogin,
                    'mentions:'+this.userLogin
                ],
                this.iam+':'+this.userLogin
            );
            this.update();
        }
    }
});
