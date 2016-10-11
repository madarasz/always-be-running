var messageCommands = {

    assertMessage: function (message, client) {

        this.log('* Looking for message: ' + message + ' *');

        var util = require('util');
        this.api.useXpath().waitForElementVisible(util.format(this.elements.message.selector, message), 5000);

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;

    },

    assertError: function (error, client) {

        this.log('* Looking for error: ' + error + ' *');

        var util = require('util');
        this.api.useXpath().waitForElementVisible(util.format(this.elements.error.selector, error), 5000);

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;

    }
};

module.exports = {
    commands: [messageCommands],
    elements: {
        message: "//div[@id='message' and contains(.,'%s')]",
        error: "//div[@id='error-list']/ul/li[contains(., '%s')]"
    }
};
