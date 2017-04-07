<script>
    var ConfigStore  = require('../../store/config.js');
    var GoliveClient = require('../../lib/golive-client.js');

    module.exports = {
        props: ['id'],
        data: () => ({
            status: 'pending',
            events: [],
            eventSource: false
        }),
        mounted: function() {
            this.update();
        },
        computed: {
            goliveUrl: () => ConfigStore.state.config.golive.urls.site,
            url: function() {
                return this.goliveUrl + '#/deployments/' + this.id
            },
            empty: function() {
                return this.events.length == 0;
            }
        },
        methods: {
            update: function() {
                GoliveClient.getDeployment(this.id).then((response) => {
                    this.status = response.data.status;
                    this.eventSource = GoliveClient.getLiveDeployment(this.id);
                    this.eventSource.onmessage = (message) => {
                        var data = JSON.parse(message.data);
                        this.status = data.status;
                        if (data.message) {
                            this.events.push(data);
                        }
                        if (data.status != 'running') {
                            this.closeEventSource();
                        }
                    };
                });

            },
            closeEventSource: function() {
                if (this.eventSource !== false) {
                    this.eventSource.close();
                    this.eventSource = false;
                }
            }
        },
        watch: {
            id: function() {
                this.update();
            },
            status: function() {
                setTimeout(
                    () => this.$emit('status', this.status),
                    // delay event because golive might return a non
                    // up to date response (not the same status)
                    500
                );
            }
        },
        components: {
            'loading-spinner': require('../loading-spinner.vue')
        },
        beforeDestroy: function() {
            this.closeEventSource();
        }
    }
</script>

<template>
    <div>
        <div
            class="panel"
            v-bind:class="{
                'panel-success': status == 'success',
                'panel-info': status == 'pending' || status == 'running',
                'panel-danger': status == 'failure'
            }">
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
                <p v-if="status == 'pending'">
                    <loading-spinner class="inline"></loading-spinner>
                    Pending...
                </p>
                <p v-else-if="empty">No log for this deployment</p>
                <ul v-else>
                    <li v-for="event in events" v-if="event.message">{{ event.message }}</li>
                </ul>
            </div>
        </div>
    </div>
</template>


<style scoped>
p {
    color:#888;
    margin-bottom: 0;
}
</style>
