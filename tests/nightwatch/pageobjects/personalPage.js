var personalCommands = {
    validate: function(client) {

        this.log('*** Validating Personal page ***');

        this.waitForElementVisible('@validator', 10000);

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;

    }
};

module.exports = {
    commands: [personalCommands],
    elements: {
        validator: {
            selector: "//h4[contains(text(),'Personal')]",
            locateStrategy: 'xpath'
        }
    }
};
