(function () {
    var client;
    var GithubClient = function()
    {
        this.url = {
            site: 'https://www.github.com/',
            api: 'https://api.github.com/',
            authProxy: ''
        };

        this.auth = {
        };

        this.app = {
            clientId: '',
            scope: ''
        };

        this.$context = null;

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
                remember: true
            }, params);


            // cookie auth
            if (window.Cookies) {
                var auth = window.Cookies.get('github-auth');
                if (typeof auth != 'undefined') {
                    this.auth = JSON.parse(auth);
                    if (params.success) {
                        params.success.apply(this.$context, [this.auth]);
                    }
                    return;
                }
            }

            // github code callback
            var urlParams = new URLSearchParams(window.location.search.slice(1));
            if (urlParams.has('code')) {
                this.$context.$http.get(
                    this.url.authProxy+'?code='+urlParams.get('code')
                ).then(
                    (function (context) { return function(response) {
                        client.auth = response.data;
                        if (params.remember && window.Cookies) {
                            window.Cookies.set('github-auth', JSON.stringify(client.auth), { expires: Infinity });
                        }
                        if (params.success) {
                            params.success.apply(context, [response.data]);
                        }
                    }})(this.$context),
                    (function (context) { return function(response) {
                        if (params.error) {
                            params.error.apply(context, [response.data]);
                        }
                    }})(this.$context)
                );

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
            if (window.Cookies) {
                window.Cookies.set('github-auth', undefined, { expires: Infinity });
            }
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

            return this.$context.$http.get(
                this.url.api + url + '?' + queryParams.join('&')
            );
        }
    }

    // Global setup
    if (typeof window !== 'undefined' && window.Vue) {

        client = new GithubClient();
        if (window.GithubClient) {
            client = Object.assign(client, window.GithubClient);
        }
        window.GithubClient = client;

        Object.defineProperties(Vue.prototype, {
            $github: {
                get: function () {
                    client.$context = this;
                    return client;
                }
            }
        });
    }
})();
