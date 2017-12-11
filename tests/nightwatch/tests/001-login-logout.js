module.exports = {

    beforeEach: function(browser) {
        browser.deleteCookies().windowMaximize('current');
    },

    after: function(browser) {
        browser.end();
    },

    /***
     * - navigate to Results page, results table is visible, to-be-concluded tab is not visible
     * - navigate to Organize page, requires login
     * - login with NRDB (regular user)
     * - check Organize page, create tournament option available, Profile, Personal menus available
     * - navigate to Results page, results table is visible, to-be-concluded exists
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
