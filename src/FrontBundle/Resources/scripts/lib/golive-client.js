var Vue         = require('vue');
var VueResource = require('vue-resource');

Vue.use(VueResource);

module.exports = function()
{
    var accessToken = false;

    this.setAccessToken = function(token) {
        accessToken = token;
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
