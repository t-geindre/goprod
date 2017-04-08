<script>
module.exports = {
  props: ['issue'],
  methods: {
    getTextLabelColor: function (background) {
      var hex = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(background);
      var rgb = {
        r: parseInt(hex[1], 16),
        g: parseInt(hex[2], 16),
        b: parseInt(hex[3], 16)
      }

      var o = Math.round((rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000);

      if (o > 125) {
        return '#000';
      }

      return '#fff'
    }
  },
  filters: {
    'repo-name': function (value) {
      return value.split('repos')[1].slice(1).split('/issues')[0];
    }
  }
};
</script>

<template>
  <a href="#" class="issue github-issue" v-on:click.prevent="$emit('select-issue', issue)">
    <svg
      aria-hidden="true"
      height="16"
      version="1.1"
      viewBox="0 0 12 16"
      width="12"
      class="icon-issue icon-pull pull-left"
      v-if="issue.pull_request"
      v-bind:class="{'issue-open': issue.state == 'open', 'issue-closed': issue.state == 'closed' }"
    >
      <path d="M11 11.28V5c-.03-.78-.34-1.47-.94-2.06C9.46 2.35 8.78 2.03 8 2H7V0L4 3l3 3V4h1c.27.02.48.11.69.31.21.2.3.42.31.69v6.28A1.993 1.993 0 0 0 10 15a1.993 1.993 0 0 0 1-3.72zm-1 2.92c-.66 0-1.2-.55-1.2-1.2 0-.65.55-1.2 1.2-1.2.65 0 1.2.55 1.2 1.2 0 .65-.55 1.2-1.2 1.2zM4 3c0-1.11-.89-2-2-2a1.993 1.993 0 0 0-1 3.72v6.56A1.993 1.993 0 0 0 2 15a1.993 1.993 0 0 0 1-3.72V4.72c.59-.34 1-.98 1-1.72zm-.8 10c0 .66-.55 1.2-1.2 1.2-.65 0-1.2-.55-1.2-1.2 0-.65.55-1.2 1.2-1.2.65 0 1.2.55 1.2 1.2zM2 4.2C1.34 4.2.8 3.65.8 3c0-.65.55-1.2 1.2-1.2.65 0 1.2.55 1.2 1.2 0 .65-.55 1.2-1.2 1.2z"></path>
    </svg>
    <svg
      aria-hidden="true"
      height="16"
      version="1.1"
      viewBox="0 0 14 16"
      width="14"
      class="icon-issue pull-left"
      v-else
      v-bind:class="{'issue-open': issue.state == 'open', 'issue-closed': issue.state == 'closed' }"
    >
      <path d="M7 2.3c3.14 0 5.7 2.56 5.7 5.7s-2.56 5.7-5.7 5.7A5.71 5.71 0 0 1 1.3 8c0-3.14 2.56-5.7 5.7-5.7zM7 1C3.14 1 0 4.14 0 8s3.14 7 7 7 7-3.14 7-7-3.14-7-7-7zm1 3H6v5h2V4zm0 6H6v2h2v-2z"></path>
    </svg>
    <p>
      <span class="repo">{{ issue.url | repo-name }}</span>
      <span class="title">{{ issue.title }}</span>
      <span v-for="label in issue.labels" class="label" v-bind:style="{ 'background-color': '#'+label.color, 'color' : getTextLabelColor(label.color) }">
        {{ label.name }}
      </span>
    </p>
    <p class="infos">
      #{{ issue.number }} opened on {{ issue.created_at }} by {{ issue.user.login }}
    </p>
  </a>
</template>

<style type="text/css" scoped>
  a {
    font-size: 16px;
    font-weight: bold;
    color: #767676;
  }

  .label {
    border-radius: 3px;
    padding: 2px 4px;
    color:#000;
    font-size:12px;
    margin-right: 10px;
  }

  .title {
    color:#333;
  }

  .infos {
    font-size: 12px;
  }

  .icon-issue {
    margin: 5px 10px 30px 0;
    fill: currentColor;
  }

  p {
    margin-bottom: 5px;
  }

  .issue-open {
    color:#6cc644;
  }

  .issue-closed {
    color: #bd2c00;
  }

  .icon-pull.issue-closed {
    color: #6e5494;
  }
</style>
