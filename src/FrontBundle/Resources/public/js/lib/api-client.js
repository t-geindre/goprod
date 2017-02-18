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
        return this.query('config', options);
    }

    // User
    this.getUser = function(options = {}) {
        return this.query('user', options);
    }

    this.updateUser = function(user, options = {}) {
        return this.formQuery(
            'user',
            Object.assign(options, { body: user })
        );
    }

    // Deploys
    this.createDeploy = function(deploy, options = {}) {
        return this.formQuery(
            'deploys',
            Object.assign(options, { body: deploy })
        );
    }

    this.getDeploysByCurrentUser = function(options = {}) {
        return this.query('user/deploys', options);
    }

    this.getDeploy = function(id, options = {}) {
        return this.query('deploys/' + id, options);
    }

    this.cancelDeploy = function(id, options = {}) {
        return this.formQuery('deploys/' + id + '/cancel');
    }

    // Internals
    this.setCredentials = function(login, accessToken) {
        this.credentials = { login: login, access_token: accessToken };
    }

    this.formQuery = function(route, options = {}) {
        return new Promise(function(resolve, reject) {
            client
                .query(
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

    this.query = function(route, options = {})
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
