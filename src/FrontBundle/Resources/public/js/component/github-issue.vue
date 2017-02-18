var Vue = require('vue');

Vue.component('github-issue', {
    template: '#github-issue-template',
    props: ['issue'],
    methods: {
        getTextLabelColor: function(background) {
            var hex = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(background);
            var rgb = {
                r: parseInt(hex[1], 16),
                g: parseInt(hex[2], 16),
                b: parseInt(hex[3], 16)
            }

            var o = Math.round((rgb.r * 299 + rgb.g * 587 + rgb.b * 114) /1000);

            if(o > 125) {
                return '#000';
            }

            return '#fff'
        }
    },
    filters: {
        "repo-name": function (value) {
            return value.split('repos')[1].slice(1).split('/issues')[0];
        }
    },
});
