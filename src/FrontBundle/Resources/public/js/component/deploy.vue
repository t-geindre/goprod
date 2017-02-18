<script>
module.exports = {
    props: ['deploy']
};
</script>

<template>
    <a href="#" v-on:click.prevent="$emit('click')" class="deploy">
        <span
            class="glyphicon glyphicon-remove canceled text-danger"
            v-if="deploy.status == 'canceled'">
        </span>
        <span
            class="glyphicon glyphicon-ok done text-success"
            v-else-if="deploy.status == 'done'">
        </span>
        <span
            class="glyphicon glyphicon-refresh active text-primary"
            v-else>
        </span>
        <p>
            <span class="repo">{{ deploy.owner }}/{{ deploy.repository}}</span>
            <span class="description">{{ deploy.description }}</span>
        </p>
        <p class="infos">
            #{{ deploy.id }} opened on {{ deploy.create_date }} by {{ deploy.user.login }}
        </p>
    </a>
</template>

<style type="text/css" scoped>
    a {
        font-size: 16px;
        font-weight: bold;
        color: #767676;
    }

    .description {
        color:#333;
    }

    .infos {
        font-size: 12px;
    }

    .glyphicon {
        font-size: 20px;
        float: left;
        padding: 10px 15px 10px 0;
    }

    p {
        margin-bottom: 5px;
    }

    .infos {
        font-size: 12px;
    }
</style>
