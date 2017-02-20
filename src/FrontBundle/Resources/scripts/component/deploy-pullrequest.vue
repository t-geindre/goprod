<script>
module.exports = {
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
        }
    },
    components: {
        'github-issues': require('./github-issues.vue')
    }
};
</script>

<template>
    <div>
        <div class="page-header">
            <h1>Let's go prod! <small>Select pull request</small></h1>
        </div>
        <github-issues query-append="is:pr" v-on:select-issue="deploy">
        </github-issues>
    </div>
</template>
