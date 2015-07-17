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
        component: 'matches'
    },
    '/match/:id': {
        component: 'match-details'
    },
    '/teams': {
        component: 'teams'
    },
    '/users': {
        component: 'users'
    },
    '/user/:id': {
        component: 'user-details'
    },
    '/profile': {
        component: 'profile'
    },
    '*': {
        component: 'dashboard'
    }
});

router.start(app);