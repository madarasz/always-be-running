var upcomingCommands = {
    validate: function(client) {

        this.log('*** Validating Upcoming page ***');

        this.waitForElementVisible('@validator', 10000);

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;

    }
};

module.exports = {
    commands: [upcomingCommands],
    elements: {
        validator: {
            selector: "//h4[contains(text(),'Upcoming Netrunner Tournaments')]",
            locateStrategy: 'xpath'
        }
    }
};
