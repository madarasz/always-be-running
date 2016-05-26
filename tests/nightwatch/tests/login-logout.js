module.exports = {
    'Login, logout' : function (browser) {

        var loginAdmin = browser.globals.adminLogin;

        browser
            .url(browser.launchUrl)
            .login(loginAdmin.username, loginAdmin.password)
            .waitForElementVisible("//a[contains(text(),'Logout')]", 1000)
            .waitForElementVisible("//a[contains(text(),'Admin')]", 1000)
            .click("//a[contains(text(),'Logout')]")
            .waitForElementVisible('//body', 3000)
            .waitForElementVisible("//a[contains(text(),'Login')]", 1000)
            .end();
    }
};