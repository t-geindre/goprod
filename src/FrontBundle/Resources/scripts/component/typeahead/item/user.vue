<script>
module.exports = {
  extends: require('./item.vue')
}
</script>

<template>
  <a href="#">
    <img :src="item.avatar_url">
    {{ item.name }}
  </a>
</template>

<style scoped>
  .dropdown-menu>li>a {
    padding-left: 10px;
  }

  img {
    border-radius: 5px;
    width: 30px;
    margin-right: 5px;
  }
</style>
