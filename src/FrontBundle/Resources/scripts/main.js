const Vue = require('vue')
const App = require('./app.vue')
const Router = require('./routing/router')

/* eslint-disable no-new */
new Vue({
  router: Router,
  el: '#app',
  render: h => h(App)
})
