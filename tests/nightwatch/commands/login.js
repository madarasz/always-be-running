exports.command = function (username, password, callback) {
    var client = this;

    client
        .log("*** Logging in with: " + username + " ***")
        .useXpath()
        .waitForElementVisible('//body', 3000)
        .windowMaximize('current')

        // logout if needed
        .element('Xpath', "//a[contains(text(),'Logout')]", function(result) {
            if (result.value && result.value.ELEMENT) {
                client.click("//a[contains(text(),'Logout')]")
            }
        })

        .click("//a[contains(text(),'Login')]")
        .waitForElementVisible('//body', 3000)

        // if session is remembered: clear cookies and refresh
        .element('Xpath', "//h3[contains(text(), 'NetrunnerDB Authorization')]", function(result) {
            if (result.value && result.value.ELEMENT) {
                client.deleteCookies().refresh();
            }
        })

        .clearValue("//input[@id='username']")
        .setValue("//input[@id='username']", username)
        .clearValue("//input[@id='password']")
        .setValue("//input[@id='password']", password)
        .click("//input[@type='submit']")

        .waitForElementVisible("//h3[contains(text(), 'NetrunnerDB Authorization')]", 1000)
        .click("//input[@name='accepted']")
        .waitForElementVisible('//body', 3000);

    if (typeof callback === "function"){
        callback.call(client);
    }

    return this;
};