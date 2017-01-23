Vue.component('user-details', {
    template: '#user-details-template',
    props: ['authenticated', 'user'],
    delimiters: ['[[', ']]'],
    mounted: function() {
        console.log(this.user);
    },
    methods: {
        login: function() {
            this.$emit('login');
        },
        logout: function() {
            this.$emit('logout');
        }
    }
});

