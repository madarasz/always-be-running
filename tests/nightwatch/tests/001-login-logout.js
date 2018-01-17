module.exports = {

    beforeEach: function(browser) {
        browser.deleteCookies().windowMaximize('current');
    },

    after: function(browser) {
        browser.end();
    },

    /***
     * - navigate to Results page, results table is visible, to-be-concluded tab is not visible
     * - try Personal page, check login required
     * - check Profile page, validate page
     * - try Admin page, check 403
     * - check Videos page, validate page
     * - navigate to Organize page, requires login
     * - login with NRDB (regular user)
     * - check Organize page, create tournament option available, Profile, Personal menus available
     * - navigate to Results page, results table is visible, to-be-concluded exists
     * - navigate to Personal page, validate page
     * - check Profile page, validate page
     * - try Admin page, check 403
     * - check Videos page, validate page
     * - navigate to Organize page, logout
     * - check Organize page, requires login, login menu available
     */
    'Login - logout': function (browser) {

        var regularLogin = browser.globals.accounts.regularLogin;

        // open browser
        browser.url(browser.launchUrl);
        browser.page.upcomingPage().validate();

        // navigate to Results page, results table is visible, to-be-concluded tab is not visible
        browser.log('* Navigate to Results page, results table is visible, to-be-concluded tab is not visible *');
        browser.page.mainMenu()
            .selectMenu('results');
        browser.page.resultsPage().validate(false);

        // try Personal page, check login required
        browser.log('* Try Personal page, check login required *');
        browser.url(browser.launchUrl + '/personal');
        browser.page.organizePage().validate(false);

        // check Profile page, validate page
        browser.log('* Check Profile page, validate page *');
        browser.url(browser.launchUrl + '/profile/' + regularLogin.userid);
        browser.page.profilePage().validate(regularLogin.username);

        // check Videos page, validate page
        browser.log('* Check Videos page, validate page *');
        browser.page.mainMenu()
            .selectMenu('videos');
        browser.page.videosPage().validate();

        // try Admin page, check 403
        browser.log('* Try Admin page, check 403 *');
        browser.url(browser.launchUrl + '/admin');
        browser.page.adminPage().validate(false);

        // navigate to Organize page, requires login
        browser.log('* Navigate to Organize page, requires login *');
        browser.page.mainMenu()
            .selectMenu('organize')
            .validateMenu('login');
        browser.page.organizePage().validate(false);

        // login with NRDB (regular user)
        browser.log('* Login with NRDB (regular user) *');
        browser.login(regularLogin.username, regularLogin.password);

        // check Organize page, create tournament option available, Profile, Personal menus available
        browser.log('* Check Organize page, create tournament option available, Profile, Personal menus available *');
        browser.page.organizePage().validate(true);
        browser.page.mainMenu()
            .validateMenu('profile')
            .validateMenu('personal');

        // navigate to Results page, results table is visible, to-be-concluded list exists
        browser.log('* Navigate to Results page, results table is visible, to-be-concluded list exists *');
        browser.page.mainMenu()
            .selectMenu('results');
        browser.page.resultsPage().validate(true);

        // navigate to Personal page, validate page
        browser.log(' * Navigate to Personal page, validate page *');
        browser.page.mainMenu()
            .selectMenu('personal');
        browser.page.personalPage().validate();

        // check Profile page, validate page
        browser.log('* Check Profile page, validate page *');
        browser.page.mainMenu()
            .selectMenu('profile');
        browser.page.profilePage().validate(regularLogin.username);

        // try Admin page, check 403
        browser.log('* Try Admin page, check 403');
        browser.url(browser.launchUrl + '/admin');
        browser.page.adminPage().validate(false);

        // check Videos page, validate page
        browser.log('* Check Videos page, validate page *');
        browser.page.mainMenu()
            .selectMenu('videos');
        browser.page.videosPage().validate();

        // navigate to Organize page, logout
        browser.log('* Navigate to Organize page, logout *');
        browser.page.mainMenu()
            .selectMenu('organize')
            .selectMenu('logout');

        // navigate to Organize page, requires login, login menu available
        browser.log('* Check Organize page, requires login, login menu available *');
        browser.page.mainMenu()
            .validateMenu('login');
        browser.page.organizePage().validate(false);
    }
};
