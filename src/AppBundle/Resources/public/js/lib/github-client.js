var Vue     = require('vue');
var Cookies = require('cookies-js');

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


        // cookie auth
        var auth = Cookies.get('github-auth');
        if (typeof auth != 'undefined') {
            this.auth = JSON.parse(auth);
            if (params.success) {
                params.success(this.auth);
            }
            return;
        }

        // github code callback
        var urlParams = new URLSearchParams(window.location.search.slice(1));
        if (urlParams.has('code')) {
            Vue.http.get(
                this.url.authProxy+'?code='+urlParams.get('code')
            ).then(
                function(response) {
                    client.auth = response.data;
                    Cookies.set('github-auth', JSON.stringify(client.auth), { expires: Infinity });
                    if (params.success) {
                        params.success(response.data);
                    }
                },
                function(response) {
                    if (params.error) {
                        params.error(response.data);
                    }
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
            window.location = this.url.site + 'login/oauth/authorize?scope=' + this.app.scope + '&client_id=' + this.app.clientId;
        }

        if (params.error) {
            params.error.apply(this.$context, [{}]);
        }
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

        var queryParams = [];
        for (var x in data) {
            queryParams.push(
                encodeURIComponent(x) + '=' + encodeURIComponent(data[x])
            );
        }

        return Vue.http.get(
            this.url.api + url + '?' + queryParams.join('&')
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
