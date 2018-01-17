var profileCommands = {
    validate: function(username, client) {

        var util = require('util');

        this.log('*** Validating Profile page for: ' + username + ' ***');

        this.api.useXpath().waitForElementVisible('//body', 3000);
        this.api.useXpath().verify.elementPresent(util.format(this.elements.validator.selector, username));

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;

    }
};

module.exports = {
    commands: [profileCommands],
    elements: {
        validator: {
            selector: "//h4[contains(.,'Profile - %s')]",
            locateStrategy: 'xpath'
        }
    }
};
