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

    this.getAppConfig = function(options) {
        return this.query({ route: 'app_config' }, options);
    }

    this.getUserProfile = function(options) {
        return this.query({ route: 'user_profile' }, options);
    }

    this.checkProfile = function(options) {
        return this.query({ route: 'user_chekprofile' }, options);
    };

    this.updateUser = function(user) {
        return this.updateQuery(
            'user_update',
            {},
            { method: 'post', body: user }
        );
    }

    this.setCredentials = function(login, accessToken) {
        this.credentials = { login: login, access_token: accessToken };
    }

    this.updateQuery = function(route, options = {}) {
        return new Promise(function(resolve, reject) {
            client.query(route, options).then(function(response) {
                if (response.data.errors) {
                    return reject(response);
                }
                resolve(response);
            });
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

        options.params = Object.assign(
            this.credentials,
            options.params
        );

        return Vue.http(
            Object.assign(
                { method: 'get', url: Routing.generate(route.route, route.params) },
                options
            )
        );
    }

}
module.exports = client = new ApiClient;
