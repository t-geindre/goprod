var Vue         = require('vue');
var VueResource = require('vue-resource');

Vue.use(VueResource);

module.exports = new function()
{
    var accessToken = false;
    var baseUrl = '';

    // Deployments
    this.getLiveDeployment = function(id) {
        return this.get('deployments/' + id + '/live');
    }

    // Config
    this.setAccessToken = function(token) {
        accessToken = token;
    }

    this.setBaseUrl = function(url) {
        baseUrl = url;
    }

    // Internals
    this.get = function(route, options = {})
    {
        options.params = Object.assign(this.credentials, options.params);

        return Vue.http(
            Object.assign(
                { method: 'get', url: baseUrl + route },
                options
            )
        );
    }

}();
