var Vue         = require('vue');
var VueResource = require('vue-resource');

Vue.use(VueResource);

// Fos Router
// @todo better solution to import and load routing
require('../../../../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js')

var client;
var ApiClient = function()
{
    this.credentials = {};
    this.routerConfigured = false;

    // App
    this.getAppConfig = function(options = {}) {
        return this.query({ route: 'app_config' }, options);
    }

    // User
    this.getUser = function(options = {}) {
        return this.query({ route: 'user_profile' }, options);
    }

    this.updateUser = function(user, options = {}) {
        return this.formQuery(
            { route: 'user_update' },
            Object.assign(options, { body: user })
        );
    }

    // Deploys
    this.createDeploy = function(deploy, options = {}) {
        return this.formQuery(
            { route: 'deploy_create' },
            Object.assign(options, { body: deploy })
        );
    }

    this.getDeploysByCurrentUser = function(options = {}) {
        return this.query({ route: 'deploy_by_current_user' }, options);
    }

    this.getDeploy = function(id, options = {}) {
        return this.query(
            { route: 'deploy_by_id', params: { id: id } },
            options
        );
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
        if (!this.routerConfigured) {
            return new Promise(function(resolve, reject) {
                Vue.http.get('js/routing.json').then(function(response) {
                    fos.Router.setData(response.data);
                    client.routerConfigured = true;
                    client.query(route, options).then(resolve, reject);
                });
            });
        }

        options.params = Object.assign(this.credentials, options.params);

        return Vue.http(
            Object.assign(
                { method: 'get', url: Routing.generate(route.route, route.params) },
                options
            )
        );
    }

}
module.exports = client = new ApiClient;
