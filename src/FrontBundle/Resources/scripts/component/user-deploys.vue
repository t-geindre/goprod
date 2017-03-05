<script>
var DeploysStore = require('../store/deploys.js');

module.exports = {
    data: function() {
        return {
            sort: ['id', 'desc']
        };
    },
    computed: {
        deploys: function() {
            return DeploysStore.state.deploys
                .sort((a, b) => {
                    if (a[this.sort[0]] < b[this.sort[0]]) {
                        return this.sort[1] == 'asc' ? -1 : 1;
                    }
                    if (a[this.sort[0]] > b[this.sort[0]]) {
                        return this.sort[1] == 'asc' ? 1 : -1;
                    }
                    return 0;
                })
                .filter(
                    (deploy) => ['canceled', 'done'].indexOf(deploy.status) == -1
                );
        },
        count: function() {
            return this.deploys.length;
        }
    },
    methods: {
        goToDeploy: function(deploy) {
            this.$router.push({
                name: 'deploy-process',
                params: { id: deploy.id }
            });
        },
        setSort: function(field, order) {
            this.sort = [field, order];
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
            <div class="panel-heading clearfix">
                <div class="dropdown pull-right">
                    <button class="btn btn-xs btn-default dropdown-toggle" type="button" data-toggle="dropdown">
                        Sort <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="#" v-on:click="setSort('id', 'desc')">Newest</a></li>
                        <li><a href="#" v-on:click="setSort('id', 'asc')">Oldest</a></li>
                    </ul>
                </div>
                <h3 class="panel-title">Active deployments</h3>
            </div>
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

<style scoped>
    h3 {
        padding-top: 3px;
    }
</style>
