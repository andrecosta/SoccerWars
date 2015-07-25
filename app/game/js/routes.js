/* Routes
 ******************************************************************************/
var router = new VueRouter({
    history: true
});

router.map({
    '/dashboard': {
        component: 'dashboard'
    },
    '/matches': {
        component: 'matches',
        alwaysRefresh: true
    },
    '/matches/:id': {
        component: 'match-details',
        data: function (route, resolve, reject) {
            return new Promise(function (resolve, reject) {
                resolve({
                    id: route.params.id
                })
            })
        },
        alwaysRefresh: true
    },
    '/teams': {
        component: 'teams'
    },
    '/users': {
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
    '*': {
        component: 'dashboard'
    }
});

router.start(app);