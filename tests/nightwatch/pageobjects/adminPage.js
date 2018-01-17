var adminCommands = {
    validate: function(auth, client) {

        this.log('*** Validating Admin page, authorization: ' + auth + ' ***');

        if (auth) {
            this.waitForElementVisible('@validator', 10000);
        } else {
            this.waitForElementVisible('@error403', 10000);
        }

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;

    }
};

module.exports = {
    commands: [adminCommands],
    elements: {
        validator: {
            selector: "//h4[contains(.,'Admin')]",
            locateStrategy: 'xpath'
        },
        error403 : {
            selector: "//h3[contains(.,'403 - Access denied')]",
            locateStrategy: 'xpath'
        }
    }
};
