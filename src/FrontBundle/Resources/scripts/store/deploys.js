const Vuex = require('vuex')
const Vue = require('vue')
const ApiClient = require('../lib/api-client')
const GithubClient = require('../lib/github-client')

Vue.use(Vuex);

module.exports = new Vuex.Store({
  state: {
    deploys: [],
    count: 0
  },
  mutations: {
    add: function (state, deploy) {
      state.deploys.push(deploy);
      state.count++;
    },
    replace: function (state, deploys) {
      state.deploys = deploys;
      state.count = deploys.length;
    },
    update: function (state, deploy) {
      Vue.set(
       state.deploys,
       state.deploys.findIndex((item) => item.id === deploy.id),
       deploy
      );
    }
  },
  actions: {
    refresh: function (context, deploy = {}) {
      return new Promise(function (resolve, reject) {
        if (!deploy.id) {
          ApiClient.getDeploysByCurrentUser()
            .then(function (response) {
              context.commit('replace', response.data);
              resolve(response);
            }, reject);
          return;
        }
        ApiClient.getDeploy(deploy.id)
        .then((response) => {
          context.commit('update', response.data);
          resolve(response);
        }, reject);
      });
    },
    create: function (context, deploy) {
      return new Promise(function (resolve, reject) {
        ApiClient.createDeploy(deploy)
        .then(function (response) {
          context.commit('add', response.data.entity);
          resolve(response);
        }, reject);
      });
    },
    cancel: function (context, deploy) {
      return new Promise(function (resolve, reject) {
        ApiClient.cancelDeploy(deploy.id)
        .then(function (response) {
          context.commit('update', response.data);
          resolve(response);
        }, reject);
      });
    },
    confirm: function (context, deploy) {
      return new Promise(function (resolve, reject) {
        ApiClient.confirmDeploy(deploy.id)
        .then(function (response) {
          context.commit('update', response.data);
          resolve(response);
        }, reject);
      });
    },
    merge: function (context, deploy, sha) {
      return new Promise(function (resolve, reject) {
        GithubClient.mergePullRequest(
          {
            owner: deploy.owner,
            repo: deploy.repository,
            number: deploy.pull_request_id
          },
        sha
       )
       .then(() => {
         return context.dispatch('refresh', deploy);
       })
       .then(resolve, reject)
      });
    },
    deploy: function (context, deploy) {
      return new Promise(function (resolve, reject) {
        ApiClient
        .deploy(deploy.id)
        .then((response) => {
          context.commit('update', response.data);
          resolve(response);
        })
        .catch(reject);
      });
    }
  }
})
