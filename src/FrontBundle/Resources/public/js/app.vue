<script>
require('bootstrap');

var GithubClient = require('./lib/github-client.js');
var ApiClient    = require('./lib/api-client.js');
var UserStore    = require('./store/user.js');
var DeploysStore = require('./store/deploys.js');
var ConfigStore  = require('./store/config.js');

module.exports = {
    components: {
        'loading-spinner': require('./component/loading-spinner.vue'),
        'user-details': require('./component/user-details.vue')
    },
    data: function() {
        return {
            authError: false,
            authenticating: false,
            deploysRefresh: null
        }
    },
    computed: {
        authenticated: function() {
            return UserStore.state.authenticated;
        },
        configured: function() {
            return ConfigStore.state.configured;
        },
        deploysCount: function() {
            return DeploysStore.state.count;
        }
    },
    mounted: function() {
        ConfigStore.dispatch('loadConfig');
    },
    methods: {
        login: function(redirect = true) {
            this.authenticating = true;
            UserStore.dispatch('login', redirect)
                .then(function() {
                    if (this.authenticated) {
                        ApiClient.setCredentials(
                            UserStore.state.user.login,
                            GithubClient.auth.access_token
                        );
                        return DeploysStore.dispatch('refresh');
                    }
                    console.log(this);
                    this.authenticating = false;
                }.bind(this))
                .then(function() {
                    this.registerDeploysRefresh();
                    this.authenticating = false;
                }.bind(this))
                .catch(function(response) {
                    this.authenticating = false;
                    this.authError = true;
                    GithubClient.clearAuthCookie();
                }.bind(this));
        },
        registerDeploysRefresh: function() {
            this.deploysRefresh = setInterval(function(){
                DeploysStore.dispatch('refresh');
            }, 5000);
        }
    },
    watch: {
        configured: function() {
            if (this.configured) {
                GithubClient.setupUrls(ConfigStore.state.config.github.urls);
                GithubClient.setupApp({
                    client_id: ConfigStore.state.config.github.client_id,
                    scope: 'repo'
                });
                this.login(false);
            }
        }
    },
    beforeDestroy: function() {
        clearInterval(this.deploysRefresh);
    }
};
</script>

<template>
<transition name="fade">
    <div v-if="configured">
        <nav class="navbar navbar-inverse navbar-fixed-top">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#">
                        <span class="glyphicon glyphicon-play-circle"></span>
                    </a>
                </div>
                <div id="navbar" class="collapse navbar-collapse">
                    <ul class="nav navbar-nav">
                        <li class="dropdown active" v-if="authenticated">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                <span class="glyphicon glyphicon-plus"></span>
                                Deploy <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <router-link :to="{ name: 'deploy-by-pullrequest' }">
                                        <span class="glyphicon glyphicon-plus"></span>
                                        Pull request
                                    </router-link>
                                </li>
                                <li>
                                    <router-link :to="{ name: 'deploy-by-project' }">
                                        <span class="glyphicon glyphicon-plus"></span>
                                        Project
                                    </router-link>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <router-link :to="{ name: 'user-deploys' }">
                                <span class="glyphicon glyphicon-time"></span>
                                In progress
                                <span class="label label-warning" v-if="deploysCount > 0">
                                    {{ deploysCount }}
                                </span>

                            </router-link>
                        </li>
                    </ul>
                    <user-details></user-details>
                </div>

            </div>

        </nav>
        <div class="container" id="container">
                <div class="page-header" v-if="authError">
                    <h1>Oops :-(</h1>
                </div>
                <div class="alert alert-danger" v-if="authError">
                    <h4>Authentication error</h4>
                    <p>An error occured during the authentication process. Please try again.</p>
                </div>
            <transition name="fade">
                <loading-spinner v-if="authenticating" class="big center">
                </loading-spinner>
                <template v-else>
                    <router-view v-if="authenticated"></router-view>
                </template>
            </transition>
        </div>
    </div>
</transition>
</template>

<style type="text/css" scoped>
#container {
    margin-top: 51px;
    position: relative;
}

.tab-content {
    padding-top: 20px;
}

.navbar-inverse .navbar-brand {
    color:#19C9FF;
}


.fade-enter-active, .fade-leave-active {
    transition: opacity .2s;
    position: absolute;
    width: 100%;
    padding-right: 30px;
}

.fade-enter, .fade-leave-to {
    opacity: 0;
    position: absolute;
    width: 100%;
    padding-right: 30px;
}
</style>
<style type="text/css" src="../../../../../node_modules/bootstrap/dist/css/bootstrap.min.css"></style>
