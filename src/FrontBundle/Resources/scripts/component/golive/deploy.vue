<script>
    var ConfigStore  = require('../../store/config.js');
    var GoliveClient = require('../../lib/golive-client.js');

    module.exports = {
        props: ['id'],
        data: () => ({
            status: 'pending',
            events: []
        }),
        mounted: function() {
            this.update();
        },
        computed: {
            goliveUrl: () => ConfigStore.state.config.golive.urls.site,
            url: function() {
                return this.goliveUrl + '#/deployments/' + this.id
            }
        },
        methods: {
            update: function() {
                GoliveClient
                    .getLiveDeployment(this.id)
                    .onmessage = (message) => {
                        var data = JSON.parse(message.data);
                        this.status = data.status;
                        this.events.push(data);
                    };
            }
        },
        watch: {
            id: function() {
                this.update();
            },
            status: function() {
                this.$emit('status', this.status);
            }
        }
    }
</script>

<template>
    <div>
        <div class="panel panel-default" v-bind:class="{ 'panel-success': status == 'success' }">
            <div class="panel-heading">
                <div class="btn-group-xs pull-right">
                    <a v-bind:href="url" target="_blank" class="btn btn-link">
                        <span class="glyphicon glyphicon-share"></span> View on Golive
                    </a>
                </div>
                <h3 class="panel-title">
                    Golive deployment
                </h3>
            </div>
            <div class="panel-body">
                <ul>
                    <li v-for="event in events" v-if="event.message">{{ event.message }}</li>
                </ul>
            </div>
        </div>
    </div>
</template>
