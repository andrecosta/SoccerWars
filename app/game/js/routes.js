/* Routes
 ******************************************************************************/
var router = new VueRouter({
    history: true
});

router.map({
    '/matches': {
        component: 'matches'
    },
    '/matches/:id': {
        component: 'match-details',
        data: function (route, resolve, reject) {
            return new Promise(function (resolve, reject) {
                resolve({
                    id: route.params.id
                })
            })
        }
    },
    '/teams': {
        component: 'teams'
    },
    '/leaderboards': {
        component: 'users'
    },
    '/users/:id': {
        component: 'profile',
        data: function (route, resolve, reject) {
            return new Promise(function (resolve, reject) {
                resolve({
                    id: route.params.id
                })
            })
        }
    },
    '/profile': {
        component: 'profile'
    },
    '/statistics': {
        component: 'statistics'
    },
    '/help': {
        component: 'help'
    },
    '*': {
        component: 'profile'
    }
});

router.start(app);