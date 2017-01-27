var Vue = require('vue');

Vue.component('pagination', {
    template: '#pagination-template',
    delimiters: ['[[', ']]'],
    props: ['pages', 'page'],
    methods: {
        goToPage: function(page) {
            this.$emit('page', page);
        }
    }
});
