<script>
require('bootstrap');
const GithubClient = require('./lib/github-client.js')
const GoliveClient = require('./lib/golive-client.js')
const ApiClient = require('./lib/api-client.js')
const Notify = require('./lib/notify.js')
const UserStore = require('./store/user.js')
const DeploysStore = require('./store/deploys.js')
const ConfigStore = require('./store/config.js')

module.exports = {
    name: 'app',
    data: () => ({
     authError: false,
     authenticating: false,
     deploysRefresh: null,
     queuedDeploys: []
    }),
    computed: {
     user: () => UserStore.state.user,
     authenticated: () => UserStore.state.authenticated,
     configured: () => ConfigStore.state.configured,
     config: () => ConfigStore.state.config,
     deploys: () => DeploysStore.state.deploys
    },
    mounted: () => ConfigStore.dispatch('loadConfig'),
    methods: {
     login: function(redirect = true) {
      this.authenticating = true;
      UserStore.dispatch('login', redirect)
       .then((response) => {
        if (response.authenticated) {
            ApiClient.setCredentials(
             this.user.login,
             GithubClient.auth.access_token
            );
            this.registerDeploysRefresh();
            return DeploysStore.dispatch('refresh');
        }
        this.authenticating = false;
       })
       .then(() => this.authenticating = false)
       .catch((response) => {
        this.authenticating = false;
        this.authError = true;
        GithubClient.clearAuthCookie();
       });
     },
     registerDeploysRefresh: function () {
      this.deploysRefresh = setInterval(function (){
       DeploysStore.dispatch('refresh');
      }, 5000);
     },
     clearDeploysRefresh: function () {
      if (this.deploysRefresh) {
       clearInterval(this.deploysRefresh);
       this.deploysRefresh = false;
      }
     }
    },
    watch: {
     configured: function () {
      if (this.configured) {
       GithubClient.setupUrls(this.config.github.urls);
       GithubClient.setupApp({client_id: this.config.github.client_id, scope: 'repo'});
       GoliveClient.setBaseUrl(this.config.golive.urls.api);
       this.login(false);
      }
     },
     deploys: function () {
      if (this.queuedDeploys.length > 0) {
       this.queuedDeploys.forEach((id) => {
        var deploy = this.deploys.find((deploy) => deploy.id == id && ['queued', 'canceled'].indexOf(deploy.status) == -1);
        if (deploy) {
            Notify(
             deploy.owner + '/' + deploy.repository + '\n'
             + 'Deployment started!',
             {
              icon: '/bundles/front/img/deploy-icon.png',
              click: () => {
               this.$router.push({
                name: 'deploy-process',
                params: { id: id }
               });
              }
             }
            );
        }
       });
      }
      this.queuedDeploys = this.deploys
       .filter((deploy) => deploy.status == 'queued')
       .map((deploy) => deploy.id);
     },
     authenticated: function () {
      if (!this.authenticated) {
       this.clearDeploysRefresh();
      }
     },
     user: function () {
      GoliveClient.setAccessToken(this.user.golive_key);
     }
    },
    components: {
     'loading-spinner': require('./component/loading-spinner.vue'),
     'user-details': require('./component/user-details.vue')
    },
    beforeDestroy: function () {
     this.clearDeploysRefresh();
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
              <span class="glyphicon glyphicon-refresh"></span>
            </a>
          </div>
          <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav" v-if="authenticated">
              <li>
                <router-link :to="{ name: 'deploys' }">Deployments</router-link>
              </li>
            </ul>
            <user-details></user-details>
            <ul class="nav navbar-nav pull-right">
              <li class="dropdown pull-right" v-if="authenticated">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                  <span class="glyphicon glyphicon-plus"></span>
                  <span class="caret"></span>
                </a>
                <ul class="dropdown-menu deploy-menu">
                  <li class="dropdown-header">New deployment by</li>
                  <li>
                    <router-link :to="{ name: 'deploy-by-pullrequest' }">Pullrequest</router-link>
                  </li>
                  <li>
                    <router-link :to="{ name: 'deploy-by-project' }">Repository</router-link>
                  </li>
                </ul>
              </li>
            </ul>
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
    transition: opacity .05s;
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

<style type="text/css" src="../../../../node_modules/bootstrap/dist/css/bootstrap.min.css"></style>
