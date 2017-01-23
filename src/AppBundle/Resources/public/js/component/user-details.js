Vue.component('user-details', {
    template: '#user-details-template',
    props: ['authenticated', 'user'],
    delimiters: ['[[', ']]'],
    mounted: function() {
        console.log(this.user);
    },
    methods: {
        authenticate: function() {
            this.$emit('authenticate');
        }
    }
});

