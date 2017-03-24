<script>
var ApiClient = require('../../lib/api-client.js');
var UserStore = require('../../store/user.js');

module.exports = {
    props: {
        deploy: {},
        title: { default: 'This deployment has been queued' }
    },
    data: function() {
        return {
            loading: true,
            deploys: [],
            deploysRefresh: false
        }
    },
    mounted: function() {
        this.deploysRefresh = setInterval(() => {
            this.update();
        }, 5000);
        this.update();
    },
    methods: {
        update: function() {
            ApiClient.getDeploysByRepository(this.deploy)
                .then((response) => {
                    this.deploys = response.data;
                    this.loading = false;
                });
        },
        goToDeploy: function(deploy) {
            this.$router.push({name: 'deploy-process', params: { id: deploy.id }});
        }
    },
    computed: {
        isEmpty: function() {
            return this.deploys.length == 0;
        },
        user: () => UserStore.state.user
    },
    components: {
        'loading-spinner': require('../loading-spinner.vue'),
        'deploy': require('./deploy.vue')
    },
    watch: {
        deploy: {
            handler: function() {
                this.update();
            },
            deep :true
        }
    },
    beforeDestroy: function() {
        if (this.deploysRefresh) {
            clearInterval(this.deploysRefresh);
        }
    }
};
</script>

<template>
    <div class="panel panel-info" v-if="!isEmpty">
        <div class="panel-heading">{{ title }}</div>
        <loading-spinner v-if="loading" class="medium"></loading-spinner>
        <div class="list-group" v-else>
            <deploy
                v-for="deploy in deploys"
                v-bind:deploy="deploy"
                class="list-group-item"
                v-on:click="goToDeploy(deploy)"
                v-bind:class="{ 'list-group-item-info': user.login == deploy.user.login }"
            >
            </deploy>
        </div>
    </div>
</template>
