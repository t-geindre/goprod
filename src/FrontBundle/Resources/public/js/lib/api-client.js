var Vue         = require('vue');
var VueResource = require('vue-resource');

Vue.use(VueResource);

var client;
var ApiClient = function()
{
    this.credentials = {};
    this.routerConfigured = false;
    this.baseUrl = 'api/';

    // App
    this.getAppConfig = function(options = {}) {
        return this.get('config', options);
    }

    // User
    this.getUser = function(options = {}) {
        return this.get('user', options);
    }

    this.updateUser = function(user, options = {}) {
        return this.post(
            'user',
            Object.assign(options, { body: user })
        );
    }

    // Deploys
    this.createDeploy = function(deploy, options = {}) {
        return this.post(
            'deploys',
            Object.assign(options, { body: deploy })
        );
    }

    this.getDeploysByCurrentUser = function(options = {}) {
        return this.get('user/deploys', options);
    }

    this.getDeploy = function(id, options = {}) {
        return this.get('deploys/' + id, options);
    }

    this.cancelDeploy = function(id, options = {}) {
        return this.post('deploys/' + id + '/cancel');
    }

    this.confirmDeploy = function(id, options = {}) {
        return this.post('deploys/' + id + '/confirm');
    }

    this.getDeploysByRepository = function(repository, options = {}) {
        return this.get(
            repository.owner + '/' + repository.repository + '/deploys',
            options
        );
    }

    this.getDeploys = function(options = {}) {
        return this.get('deploys', options);
    }

    // Internals
    this.setCredentials = function(login, accessToken) {
        this.credentials = { login: login, access_token: accessToken };
    }

    this.post = function(route, options = {}) {
        return new Promise(function(resolve, reject) {
            client
                .get(
                    route,
                    Object.assign({ method: 'post' }, options)
                )
                .then(
                    function(response) {
                        if (response.data.errors) {
                            return reject(response);
                        }
                        resolve(response);
                    },
                    reject
                );
        });
    }

    this.get = function(route, options = {})
    {
        options.params = Object.assign(this.credentials, options.params);

        return Vue.http(
            Object.assign(
                { method: 'get', url: this.baseUrl + route },
                options
            )
        );
    }

}
module.exports = client = new ApiClient;
