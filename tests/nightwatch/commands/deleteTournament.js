exports.command = function (title, callback) {
    var client = this;

    client
        .url(client.launchUrl)
        .useXpath()
        .waitForElementVisible('//body', 3000)
        .windowMaximize('current')
        .click("//a[contains(text(),'My Tournaments')]")
        .waitForElementVisible('//body', 3000)
        .waitForElementVisible("//td[contains(text(), '" + title + "')]", 1000)
        .click("//button[contains(text(),'delete')]")
        .assert.elementNotPresent("//td[contains(text(), '" + title + "')]");

    if (typeof callback === "function"){
        callback.call(client);
    }

    return this;
};