module.exports = [
    {
        name: 'home',
        path: '/',
        redirect: { name: 'user-deploys' }
    },
    {
        name: 'deploy-by-pullrequest',
        path: '/new/by-pullrequest',
        component: require('../component/deploy-pullrequest.vue')
    },
    {
        name: 'deploy-create-by-pullrequest',
        path: '/new/by-pullrequest/:owner/:repo/:number',
        component: require('../component/deploy-create.vue')
    },
    {
        name: 'deploy-by-project',
        path: '/new/by-project',
        component: require('../component/deploy-pullrequest.vue')
    },
    {
        name: 'user-deploys',
        path: '/deploys/mine',
        component: require('../component/user-deploys.vue')
    },
    {
        name: 'deploy-process',
        path: '/deploys/:id',
        component: require('../component/deploy-process.vue')
    }
]
