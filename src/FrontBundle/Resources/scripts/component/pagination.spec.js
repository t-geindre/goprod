const Vue = require('vue')
const Pagination = require('./pagination.vue')

describe('Pagination', () => {
  it('renders correctly', () => {
    const VueComponent = Vue.extend(Pagination);

    let vm = new VueComponent({ propsData: { pages: 0, page: 1 }}).$mount();
    expect(vm.$el.children).toBe(undefined);
    expect(vm.$el.textContent).toBe('');

    vm = new VueComponent({ propsData: { pages: 10, page: 1 }}).$mount();
    expect(vm.$el.children).toBeDefined();
    expect(vm.$el.textContent).toBe(' 123456 »');

    vm = new VueComponent({ propsData: { pages: 10, page: 5 }}).$mount();
    expect(vm.$el.children).toBeDefined();
    expect(vm.$el.textContent).toBe('« 12345678910 »');

    vm = new VueComponent({ propsData: { pages: 10, page: 10 }}).$mount();
    expect(vm.$el.children).toBeDefined();
    expect(vm.$el.textContent).toBe('« 5678910 ');
  })
});
