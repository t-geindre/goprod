<script>
module.exports = {
    props: ['sort', 'order', 'open', 'userIs', 'userLogin', 'userName', 'page', 'owner', 'repository'],
    methods: {
        deploy: function(pr) {
            var repo = pr.repository_url.split('/');
            this.$router.push({
                name: 'deploy-create-by-pullrequest',
                params: {
                    owner: repo[repo.length - 2],
                    repo: repo[repo.length - 1],
                    number: pr.number
                }
            });
        },
        filter: function(filters, replace = false) {
            this.$router[replace ? 'replace' : 'push']({
                path: this.$route.path, query: Object.assign({}, this.$route.query, filters)
            })
        }
    },
    components: {
        'github-issues': require('../github/issues.vue')
    }
};
</script>

<template>
    <div>
        <div class="page-header">
            <h1>Let's go prod! <small>Select pull request</small></h1>
        </div>
        <github-issues
            v-on:select-issue="deploy"
            v-on:filter="filter"
            v-bind:sort="sort"
            v-bind:order="order"
            v-bind:open="open"
            v-bind:userIs="userIs"
            v-bind:userLogin="userLogin"
            v-bind:userName="userName"
            v-bind:page="page"
            v-bind:owner="owner"
            v-bind:repository="repository"
            type="pullrequest"
        >
        </github-issues>
    </div>
</template>
