<script>
var ApiClient = require('../../lib/api-client.js');

module.exports = {
    extends: require('./typeahead.vue'),
    components: {
        'item': require('./item/user.vue')
    },
    mounted: function() {
        this.sourceFunc = function(q) {
            return new Promise(function(resolve, reject) {
                return ApiClient
                    .searchUser(q)
                    .then(function(response) {
                        resolve(response.data);
                    })
                    .catch(reject);
            })
        };
    }
};
</script>
