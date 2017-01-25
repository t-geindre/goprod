var vue = new Vue({
    el: '#app',
    template: '#app-template',
    replace: false,
    data: {
        authenticating: false
    },
    router: new VueRouter({
        routes: [
            {
                name: 'home',
                path: '/',
                redirect: { name: 'deploy-by-pullrequest' }
            },
            {
                name: 'deploy-by-pullrequest',
                path: '/deploy/pullrequest',
                component: Vue.component('deploy-pullrequest')
            },
            {
                name: 'deploy-create-by-pullrequest',
                path: '/deploy/pullrequest/:id',
                component: Vue.component('user-details')
            }
        ]
    }),
    store: new Vuex.Store({
        state: {
            authenticated: false,
            user: {}
        },
        mutations: {
            authenticated: function(state, authenticated) {
                state.authenticated = authenticated;
            },
            loadUser: function(state, user) {
                state.user = user;
            },
            clearUser: function(state) {
                state.user = {};
            }
        }
    }),
    computed: {
        authenticated: function() {
            return this.$store.state.authenticated;
        }
    },
    mounted: function() {
        this.authenticate(false);
    },
    methods: {
        authenticate: function(redirect = true) {
            this.authenticating = true;
            this.$github.authenticate({
                redirect: redirect,
                success: function() {
                    this.$github.getCurrentUser().then(function(response) {
                        this.$store.commit('loadUser', response.data);
                        this.$store.commit('authenticated', true);
                        this.authenticating = false;
                    }, function(response) {
                        this.$store.commit('authenticated', false);
                        this.$github.clearAuthCookie();
                        this.authenticating = false;
                    })
                },
                error: function() {
                    this.authenticating = false;
                }
            });
        },
        clearAuth: function() {
            this.$github.clearAuthCookie();
            this.$store.commit('loadUser', {});
            this.$store.commit('authenticated', false);
        }
    }
});

