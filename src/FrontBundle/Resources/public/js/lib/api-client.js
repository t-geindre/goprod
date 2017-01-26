var Vue         = require('vue');
var VueResource = require('vue-resource');
var UserStore   = require('../store/user');

Vue.use(VueResource);

// Fos Router
// @todo better solution to import and load routing
require('../../../../../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js')

var client;
var ApiClient = function()
{
    this.routerConfigured = false;

    this.getAppConfig = function() {
        return this.query('app_config');
    }

    this.checkProfile = function() {
        return this.query('user_chekprofile');
    };

    this.query = function(route, params = {}, data = {})
    {
        if (!this.routerConfigured) {
            return new Promise(function(resolve, reject) {
                Vue.http.get('js/routing.json').then(function(response) {
                    fos.Router.setData(response.data);
                    client.routerConfigured = true;
                    client.query(route, params, data).then(resolve, reject);
                });
            });
        }

        return Vue.http.get(
            Routing.generate(route, params),
            {
                params: Object.assign(
                    {
                        access_token: UserStore.state.auth.access_token,
                        login: UserStore.state.user.login
                    },
                    data
                )
            }
        );
    }

}

module.exports = client = new ApiClient;
