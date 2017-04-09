<script>
module.exports = {
  props: ['deploy'],
  computed: {
    status: function () {
      return {
        'new': 'new',
        'queued': 'queued',
        'merge': 'waiting for pullrequest merge',
        'deploy': 'waiting for deploy',
        'waiting': 'waiting for user confirmation',
        'done': 'done',
        'canceled': 'canceled'
      }[this.deploy.status];
    }
  }
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
      class="glyphicon glyphicon-time queued text-primary"
      v-else-if="deploy.status == 'queued'">
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
      #{{ deploy.id }} opened on {{ deploy.create_date }} by {{ deploy.user.login }}, {{ status }}
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

  span.text-danger {
    color: #d10202;
  }

  span.text-success {
    color: #26ba01;
  }
</style>
