/* Routes
 ******************************************************************************/
var router = new VueRouter();

router.map({
    '/dashboard': {
        component: 'dashboard'
    },
    '/matches': {
        component: 'matches'
    },
    '/teams': {
        component: 'teams'
    },
    '/users': {
        component: 'users'
    }
});

router.start(app);