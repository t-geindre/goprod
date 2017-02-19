<script>
var ApiClient = require('../lib/api-client.js');

module.exports = {
    props: ['deploy'],
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
        }
    },
    components: {
        'loading-spinner': require('./loading-spinner.vue'),
        'deploy': require('./deploy.vue')
    },
    beforeDestroy: function() {
        if (this.deploysRefresh) {
            clearInterval(this.deploysRefresh);
        }
    }
};
</script>

<template>
    <div class="panel panel-default">
        <div class="panel-heading">Your deployment has been queued</div>
        <loading-spinner v-if="loading" class="medium"></loading-spinner>
        <div class="list-group" v-else>
            <deploy
                v-for="deploy in deploys"
                v-bind:deploy="deploy"
                class="list-group-item"
            >
            </deploy>
        </div>
    </div>
</template>
