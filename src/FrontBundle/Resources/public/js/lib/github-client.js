var Vue         = require('vue');
var VueResource = require('vue-resource')
var Cookies     = require('cookies-js');

Vue.use(VueResource);

var client;
var GithubClient = function()
{
    this.urls = {
        site: 'https://www.github.com/',
        api: 'https://api.github.com/',
        auth_proxy: ''
    };

    this.auth = {};

    this.app = {
        client_id: '',
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

    this.getPullRequest = function(pullrequest) {
        return this.apiQuery(
            'repos/'+pullrequest.owner+'/'+pullrequest.repo+'/pulls/'+pullrequest.number,
            { avoidCache: (new Date()).getTime() }
        );
    }

    this.getIssue = function(issues) {
        return this.apiQuery(
            'repos/'+issues.owner+'/'+issues.repo+'/issues/'+issues.number
        );
    }

    this.setupUrls = function(urls) {
        this.urls = urls;
    }

    this.setupApp = function(app) {
        this.app = app;
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
                    client.urls.auth_proxy+'?code='+urlParams.get('code')
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
                window.location = client.urls.site + 'login/oauth/authorize?scope=' + client.app.scope + '&client_id=' + client.app.client_id;
            }

            resolve({ authenticated: false, auth: {}});
        })
    };

    this.clearAuthCookie = function()
    {
        Cookies.set('github-auth', undefined, { expires: Infinity });
        this.auth = {};
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
            this.urls.api + url, { params: data }
        );
    }
}

module.exports = client = new GithubClient();
