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

    }
};

module.exports = {
    commands: [organizeCommands],
    elements: {
        create: {
            selector: "//a[contains(text(),'Create Tournament')]",
            locateStrategy: 'xpath'
        }
    }
};
