import Vue from 'vue'
import App from './app.vue'
import Router from './routing/router'

new Vue({
    router: Router,
    el: '#app',
    render: h => h(App)
})
