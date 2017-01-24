Vue.component('github-issues', {
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
            this.$github.searchIssues(data).then(function(response) {
                this.issues = response.data.items;
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
                while ((index = items.indexOf(remove)) > -1) {
                    items.splice(index, 1);
                }
            })
            items.unshift(add);
            this.query = items.join(' ');
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
