var UserStore = require('../store/user.js');

module.exports = [
    {
        name: 'home',
        path: '/',
        redirect: { name: 'user-deploys' }
    },
    {
        name: 'deploy-by-pullrequest',
        path: '/deploys/new/by-pullrequest',
        component: require('../component/deploy/by-pullrequest.vue'),
        props: (route) => Object.assign(
            {},
            {
                userLogin: UserStore.state.user.login,
                userName: UserStore.state.user.name,
                userIs: 'author'
            },
            route.query
        )
    },
    {
        name: 'deploy-create-by-pullrequest',
        path: '/deploys/new/by-pullrequest/:owner/:repo/:number',
        component: require('../component/deploy/create.vue')
    },
    {
        name: 'deploy-by-project',
        path: '/new/by-repository',
        component: require('../component/deploy/create.vue')
    },
    {
        name: 'user-deploys',
        path: '/deploys/mine',
        component: require('../component/deploy/user-list.vue')
    },
    {
        name: 'deploy-process',
        path: '/deploys/:id',
        component: require('../component/deploy/process.vue')
    },
    {
        name: 'deploys',
        path: '/deploys',
        component: require('../component/deploy/search.vue'),
        props: (route) => route.query
    }
]
