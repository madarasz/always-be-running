exports.command = function (username, password, callback) {
    var client = this;

    client
        .useXpath()
        .waitForElementVisible('//body', 3000)
        .windowMaximize('current')
        .click("//a[contains(text(),'Login')]")
        .waitForElementVisible('//body', 3000)
        .waitForElementVisible("//h3[contains(text(), 'NetrunnerDB Authentication')]", 1000)
        .clearValue("//input[@id='username']")
        .setValue("//input[@id='username']", username)
        .clearValue("//input[@id='password']")
        .setValue("//input[@id='password']", password)
        .click("//input[@type='submit']")
        .waitForElementVisible('//body', 3000)
        .waitForElementVisible("//h3[contains(text(), 'NetrunnerDB Authorization')]", 1000)
        .click("//input[@name='accepted']");

    if (typeof callback === "function"){
        callback.call(client);
    }

    return this;
};