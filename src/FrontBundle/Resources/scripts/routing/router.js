const Vue = require('vue')
const VueRouter = require('vue-router')
const Routes = require('./routes')

Vue.use(VueRouter);

module.exports = new VueRouter({
  routes: Routes
});
