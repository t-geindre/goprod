<script>
    module.exports = {
        props: ['id'],
        data: () => {
            return {
                loading: true,
                events: []
            };
        },
        components: {
            'loading-spinner': require('../loading-spinner.vue')
        }
    }
</script>

<template>
    <div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="btn-group-xs pull-right">
                    <a href="#" class="btn btn-link">
                        <span class="glyphicon glyphicon-refresh"></span> Refresh
                    </a>
                    <a href="#" target="_blank" class="btn btn-link">
                        <span class="glyphicon glyphicon-share"></span> View on Golive
                    </a>
                </div>
                <h3 class="panel-title">
                    Golive deployment
                </h3>
            </div>
            <div class="panel-body">
                <ul v-if="!loading">
                    <li v-for="event in events">{{ event.message }}</li>
                </ul>
                <loading-spinner class="medium" v-else></loading-spinner>
            </div>
        </div>
    </div>
</template>
