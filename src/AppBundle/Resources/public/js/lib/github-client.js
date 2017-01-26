var Vue         = require('vue');
var VueResource = require('vue-resource')
var Cookies     = require('cookies-js');

Vue.use(VueResource);

var client;
var GithubClient = function()
{
    this.url = {
        site: 'https://www.github.com/',
        api: 'https://api.github.com/',
        authProxy: ''
    };

    this.auth = {};

    this.app = {
        clientId: '',
        scope: ''
    };

    this.getCurrentUser = function()
    {
        return this.apiQuery('user');
    }

    this.searchIssues = function(terms)
    {
        return this.apiQuery('search/issues', terms);
    }

    this.authenticate = function(params)
    {
        params = Object.assign({
            redirect: false,
            remember: true,
            clearAuthCode: true
        }, params);

        return new Promise(function(resolve, reject) {
            // cookie auth
            var auth = Cookies.get('github-auth');
            if (typeof auth != 'undefined') {
                client.auth = JSON.parse(auth);
                return resolve({ authenticated: true, auth: client.auth});
            }

            // github code callback
            var urlParams = new URLSearchParams(window.location.search.slice(1));
            if (urlParams.has('code')) {

                Vue.http.get(
                    client.url.authProxy+'?code='+urlParams.get('code')
                ).then(
                    function(response) {
                        client.auth = response.data;
                        if (params.remember) {
                            Cookies.set('github-auth', JSON.stringify(client.auth), { expires: Infinity });
                        }
                        resolve({ authenticated: true, auth: response.data })
                    },
                    function(response) {
                        reject(response.data);
                    }
                );

                // clear used code
                if (window.history && params.clearAuthCode) {
                    urlParams.delete('code')
                    var search = urlParams.toString();
                    window.history.pushState(
                        {},
                        'Authenticated',
                        window.location.pathname + (search.length ? '?' + search : '')
                    );
                }

                return;
            }
            // redirect to github to get token
            if (params.redirect) {
                window.location = client.url.site + 'login/oauth/authorize?scope=' + client.app.scope + '&client_id=' + client.app.clientId;
            }

            resolve({ authenticated: false, auth: {}});
        })
    };

    this.clearAuthCookie = function()
    {
        Cookies.set('github-auth', undefined, { expires: Infinity });
    }

    this.apiQuery = function(url, data = {})
    {
        if (this.auth.access_token) {
            data = Object.assign(
                { access_token: this.auth.access_token },
                data
            );
        }

        return Vue.http.get(
            this.url.api + url, { params: data }
        );
    }
}

// Global setup
if (typeof window !== 'undefined') {
    client = new GithubClient();
    if (window.GithubClient) {
        client = Object.assign(client, window.GithubClient);
    }
    window.GithubClient = client;
}

module.exports = client;
