var videosCommands = {
    validate: function(client) {

        this.log('*** Validating Videos page ***');

        this.waitForElementVisible('@validator', 10000);

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;

    }
};

module.exports = {
    commands: [videosCommands],
    elements: {
        validator: {
            selector: "//h4[contains(.,'Netrunner Tournaments Videos')]",
            locateStrategy: 'xpath'
        }
    }
};
