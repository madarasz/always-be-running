var organizeCommands = {

    clickCommand: function (element, client) {

        this.log('*** Clicking command: ' + element + ' ***');

        this
            .waitForElementVisible('@'+element, 3000)
            .click('@'+element);


        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;

    },

    validate: function(isLoggedIn, client) {

        this.log('*** Validating Organize page, logged in: ' + isLoggedIn + ' ***');

        if (isLoggedIn) {
            this.waitForElementVisible('@validator', 10000);
        } else {
            this.waitForElementVisible('@loginRequired', 10000);
        }

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;

    }
};

module.exports = {
    commands: [organizeCommands],
    elements: {
        createTournament: {
            selector: "//a[contains(text(),'Create Tournament')]",
            locateStrategy: 'xpath'
        },
        createFromResults: {
            selector: "//button[contains(text(),'Create from Results')]",
            locateStrategy: 'xpath'
        },
        loginRequired: {
            selector: "//h4[contains(text(),'Login required')]",
            locateStrategy: 'xpath'
        },
        validator: {
            selector: "//h4[contains(.,'Organize')]",
            locateStrategy: 'xpath'
        }
    }
};
