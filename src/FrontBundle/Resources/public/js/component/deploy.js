var Vue = require('vue');

module.exports = Vue.component('deploy', {
    template: '#deploy-template',
    props: ['deploy']
});
