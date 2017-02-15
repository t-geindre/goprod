module.exports = [
    {
        name: 'home',
        path: '/',
        redirect: { name: 'deploy-by-pullrequest' }
    },
    {
        name: 'deploy-by-pullrequest',
        path: '/new/by-pullrequest',
        component: require('../component/deploy-pullrequest')
    },
    {
        name: 'deploy-create-by-pullrequest',
        path: '/new/by-pullrequest/:owner/:repo/:number',
        component: require('../component/deploy-create')
    },
    {
        name: 'deploy-by-project',
        path: '/new/by-project',
        component: require('../component/deploy-pullrequest')
    },
    {
        name: 'deploy-list',
        path: '/deploys',
        component: require('../component/user-deploys')
    }
]
