/* Routes
 ******************************************************************************/
var router = new VueRouter({
    history: true
});

/* When clicking on the navigation items in the main menu,
 * the below routes will activate the respective subcomponent (they have the same structure as the main app object)
 * They can be found in the js/components directory
 */
router.map({
    '/matches': {
        component: 'matches'
    },
    '/matches/:id': {
        component: 'match-details',
        // This is just to be able to get the ID from the url
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
        component: 'profile',
        alwaysRefresh: true
    },
    '/statistics': {
        component: 'statistics'
    },
    '/help': {
        component: 'help'
    },
    '*': { // Catch all
        component: 'profile'
    }
});

router.start(app);