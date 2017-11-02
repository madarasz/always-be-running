var resultsCommands = {
    validate: function(isLoggedIn, client) {

        this.log('*** Validating Results page, logged in: ' + isLoggedIn + ' ***');

        this.waitForElementVisible('@validator', 10000);

        if (isLoggedIn) {
            this.waitForElementVisible('@resultsTab', 10000);
            this.waitForElementVisible('@toBeConcludedTab', 10000);
            this.waitForElementVisible('@resultsTable', 10000);
            this.waitForElementPresent('@toBeConcludedTable', 10000);
        } else {
            this.waitForElementVisible('@resultsTable', 10000);
            this.waitForElementNotPresent('@resultsTab', 100);
            this.waitForElementNotPresent('@toBeConcludedTab', 100);
            this.waitForElementNotPresent('@toBeConcludedTable', 100);
        }

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;

    }
};

module.exports = {
    commands: [resultsCommands],
    elements: {
        validator: {
            selector: "//h4[contains(text(),'Netrunner Tournament Results')]",
            locateStrategy: 'xpath'
        },
        resultsTab: {
            selector: "//li[@id='tab-results']",
            locateStrategy: 'xpath'
        },
        toBeConcludedTab: {
            selector: "//li[@id='tab-to-be-concluded']",
            locateStrategy: 'xpath'
        },
        resultsTable: {
            selector: "//table[@id='results']",
            locateStrategy: 'xpath'
        },
        toBeConcludedTable: {
            selector: "//table[@id='to-be-concluded']",
            locateStrategy: 'xpath'
        }
    }
};
