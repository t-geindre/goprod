module.exports = [
    {
        name: 'home',
        path: '/',
        redirect: { name: 'deploy-by-pullrequest' }
    },
    {
        name: 'deploy-by-pullrequest',
        path: '/new/pullrequest',
        component: require('../component/deploy-pullrequest')
    },
    {
        name: 'deploy-create-by-pullrequest',
        path: '/new/pullrequest/:owner/:repo/:number',
        component: require('../component/deploy-create')
    }
]
