<script>
var UserStore    = require('../store/user.js');
var DeploysStore = require('../store/deploys.js');
var jQuery       = require('jquery');

module.exports = {
    data: function() {
        return {
            profileModal: null,
            formData: {},
            formErrors: { fields: {} },
            loading: false
        }
    },
    mounted: function() {
        this.profileModal = jQuery('.profile-modal', this.$el);
    },
    computed: {
        user: function()
        {
            return UserStore.state.user;
        },
        authenticated: function()
        {
            return UserStore.state.authenticated;
        },
        authenticating: function()
        {
            return UserStore.state.authenticating;
        },
        profileComplete: function() {
            return UserStore.state.complete;
        },
        deploysCount: function() {
            return DeploysStore.state.count;
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
            this.formData = {
                goliveKey: this.user.golive_key
            };
            this.formErrors = { fields: {} };
            this.profileModal.modal({
                backdrop: (this.profileComplete ? true : 'static'),
                keyboard: this.profileComplete,
                show: true
            });
        },
        hideProfile: function() {
            this.profileModal.modal('hide');
        },
        updateProfile: function() {
            this.loading = true;
            UserStore.dispatch('update', this.formData).then(
                (response) => {
                    this.hideProfile();
                    this.loading = false;
                },
                (response) => {
                    this.formErrors = response.data.errors;
                    this.loading = false;
                }
            );
        }
    },
    watch: {
        profileComplete: function() {
            if (!this.profileComplete) {
                this.displayProfile();
            }
        }
    },
    components: {
        'loading-spinner': require('./loading-spinner.vue')
    }
};
</script>

<template>
    <div class="user-details">
        <ul class="nav navbar-nav navbar-right" v-if="authenticated">
            <li class="dropdown">
                <a href="#" class="dropdown-toggle user-infos" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img :src="user.avatar_url" alt="avatar" />
                        {{ user.name }}
                        <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <router-link :to="{ name: 'user-deploys' }">
                            <span class="label label-info">{{ deploysCount }}</span>
                            My deployments

                        </router-link>
                    </li>
                    <li role="separator" class="divider"></li>
                    <li>
                        <a href="#" v-on:click.prevent="displayProfile">
                            <span class="glyphicon glyphicon-user"></span>
                            Profile
                        </a>
                    </li>
                    <li role="separator" class="divider"></li>
                    <li>
                        <a href="#" v-on:click.prevent="logout">
                            <span class="glyphicon glyphicon glyphicon-off"></span>
                            Logout
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
        <button v-else-if="!authenticating" type="button" class="navbar-right btn btn-default navbar-btn" v-on:click="login">
            <span class="glyphicon glyphicon glyphicon-off"></span>
            Login
        </button>
        <div class="modal profile-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" v-if="profileComplete">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">{{ user.name }} - <small>Profile</small></h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning" role="alert" v-if="!profileComplete">
                            Please, <strong>complete your profile</strong>
                        </div>
                        <form>
                            <div class="form-group" v-bind:class="{'has-error':this.formErrors.fields.goliveKey}">
                                <label for="goliveApiKey">Golive API Key (<a href="#">manage</a>):</label>
                                <input type="password" name="goliveKey" class="form-control" id="goliveApiKey" placeholder="fds54fds98sd8ds897vd8s7" v-model="formData.goliveKey" />
                                <span id="helpBlock" class="help-block" v-if="this.formErrors.fields.goliveKey">
                                    A valid API key is required
                                </span>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal" v-if="profileComplete">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-primary" v-on:click="updateProfile" v-bind:disabled="loading">
                            <loading-spinner class="inline" v-if="loading"></loading-spinner>
                            Save changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style type="text/css" scoped>
    img {
        width: 35px;
        height: 35px;
        border-radius: 5px;
    }

    .user-infos {
        padding-top: 7px;
        padding-bottom: 8px;
    }
</style>

<style type="text/css">
    /* modal fix, @todo */
    .modal-backdrop {
        z-index: 500;
    }
</style>
