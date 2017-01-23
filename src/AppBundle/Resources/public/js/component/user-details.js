Vue.component('user-details', {
    template: '#user-details-template',
    props: ['authenticated', 'user'],
    delimiters: ['[[', ']]'],
    methods: {
        login: function() {
            this.$emit('login');
        },
        logout: function() {
            this.$emit('logout');
        }
    }
});

