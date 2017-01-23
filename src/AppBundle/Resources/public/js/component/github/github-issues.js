Vue.component('github-issues', {
    delimiters: ['[[', ']]'],
    template: '#github-issues-template',
    props: ['query', 'queryAppend', 'userLogin'],
    data: function() {
        return {
            pulls: [],
            sort: 'created',
            order: 'desc'
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
                data.q += ' '+this.queryAppend;
            }
            this.$github.searchIssues(data).then(function(response) {
                this.pulls = response.data.items;
            });
        },
        setSort: function(type, order) {
            this.sort = type;
            this.order = order;
            this.update();
        }
    },
    filters: {
        "repo-name": function (value) {
            return value.split('repos')[1].slice(1);
        }
    }
});
