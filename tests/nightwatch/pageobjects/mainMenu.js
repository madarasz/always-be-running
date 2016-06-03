var menuCommands = {
    selectMenu: function(item, client) {
        this.click("@" + item);

        if (typeof callback === "function") {
            callback.call(client);
        }

        return this.api;
    }
};

module.exports = {
    commands: [menuCommands],
    elements: {
        create: {
            selector: "//a[contains(text(),'Create')]",
            locateStrategy: 'xpath'
        },
        admin: {
            selector: "//a[contains(text(),'Admin')]",
            locateStrategy: 'xpath'
        },
        my: {
            selector: "//a[contains(text(),'My Tournaments')]",
            locateStrategy: 'xpath'
        }
    }
};
