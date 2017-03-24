const Vue = require('vue');
const LoadingSpinner = require('./loading-spinner.vue');

describe('LoadingSpinner', () => {
    it('renders correctly', () => {
        const VueComponent = Vue.extend(LoadingSpinner);
        const $el = new VueComponent().$mount().$el;

        expect($el.children.length).toBe(0);
        expect($el.textContent).toBe('');
    })
});
