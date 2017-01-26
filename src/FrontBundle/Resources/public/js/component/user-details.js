var Vue       = require('vue');
var Vuex      = require('vuex');
var UserStore = require('../store/user');
var ApiClient = require('../lib/api-client');
var jQuery    = require('jquery');

module.exports = Vue.component('user-details', {
    template: '#user-details-template',
    delimiters: ['[[', ']]'],
    data: function() {
        return {
            profileModal: null,
            profileComplete: true
        }
    },
    mounted: function() {
        this.profileModal = jQuery('#profile-modal');
    },
    computed: {
        user: function() {
            return UserStore.state.user;
        },
        authenticated: function()
        {
            return UserStore.state.authenticated;
        },
        authenticating: function()
        {
            return UserStore.state.authenticating;
        }
    },
    methods: {
        login: function() {
            UserStore.dispatch('login');
        },
        logout: function() {
            UserStore.dispatch('logout');
        },
        displayProfile: function() {
            this.profileModal.modal({
                backdrop: (this.profileComplete ? true : 'static'),
                keyboard: this.profileComplete,
                show: true
            });
        }
    },
    watch: {
        authenticated: function() {
            if (this.authenticated) {
                ApiClient.checkProfile().then(function(response) {
                    this.profileComplete = response.data.complete;
                }.bind(this));
            }
        },
        profileComplete: function() {
            if (!this.profileComplete) {
                // this.displayProfile();
            }
        }
    }
});

