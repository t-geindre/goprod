var Vue       = require('vue');
var VueRouter = require('vue-router');
var Routes    = require('./routes');

Vue.use(VueRouter);

module.exports = new VueRouter({
    routes: Routes
});
