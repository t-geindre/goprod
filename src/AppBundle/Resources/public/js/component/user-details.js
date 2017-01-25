var Vue  = require('vue');
var Vuex = require('vuex');

module.exports = Vue.component('user-details', {
    template: '#user-details-template',
    delimiters: ['[[', ']]'],
    computed: Vuex.mapState(['user', 'authenticated']),
    methods: {
        login: function() {
            this.$emit('login');
        },
        logout: function() {
            this.$emit('logout');
        }
    }
});

