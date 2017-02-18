<script>
var DeploysStore = require('../store/deploys.js');

module.exports = {
    computed: {
        deploys: function() {
            return DeploysStore.state.deploys;
        },
        count: function() {
            return DeploysStore.state.count;
        }
    },
    methods: {
        goToDeploy: function(deploy) {
            this.$router.push({
                name: 'deploy-process',
                params: { id: deploy.id }
            });
        }
    },
    components: {
        deploy: require('./deploy.vue')
    }
};
</script>

<template>
    <div>
        <div class="page-header">
            <h1>My deployments <small>in progress</small></h1>
        </div>
        <p v-if="count == 0">
            You have no active deployment. You can create a new one
            <router-link :to="{ name: 'deploy-by-pullrequest' }">by pullrequest</router-link> or
            <router-link :to="{ name: 'deploy-by-project' }">by project</router-link>.
        </p>
        <div class="panel panel-default" v-else>
            <div class="list-group">
                <deploy
                    v-for="deploy in deploys"
                    v-bind:deploy="deploy"
                    class="list-group-item"
                    v-on:click="goToDeploy(deploy)"
                >
                </deploy>
            </div>
        </div>
    </div>
</template>
