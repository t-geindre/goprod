var Vue          = require('vue');
var Vuex         = require('vuex');
var UserStore    = require('../store/user');
var ApiClient    = require('../lib/api-client');

module.exports = Vue.component('user-details', {
    template: '#user-details-template',
    delimiters: ['[[', ']]'],
    computed: {
        user: function() {
            return UserStore.state.user;
        },
        authenticated: function()
        {
            return UserStore.state.authenticated;
        }
    },
    methods: {
        login: function() {
            UserStore.dispatch('login');
        },
        logout: function() {
            UserStore.dispatch('logout');
        }
    },
    watch: {
        authenticated: function() {
            if (this.authenticated) {
                ApiClient.checkProfile().then(function(response) {
                    console.log(response.data);
                });
            }
        }
    }
});

