var Vue         = require('vue');
var VueResource = require('vue-resource');
var UserStore   = require('../store/user');

Vue.use(VueResource);

var ApiClient = function() {
    this.checkProfile = function() {
        return this.query('check_profile');
    };

    this.query = function(url, data = {})
    {
        return Vue.http.get(
            url,
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

module.exports = new ApiClient;
