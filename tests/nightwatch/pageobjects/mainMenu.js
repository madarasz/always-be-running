var menuCommands = {
    selectMenu: function(item, client) {
        this.click("@" + item);

        if (typeof callback === "function") {
            callback.call(client);
        }

        return this;
    }
};

module.exports = {
    commands: [menuCommands],
    elements: {
        organize: {
            selector: "//a[contains(text(),'Organize')]",
            locateStrategy: 'xpath'
        },
        results: {
            selector: "//a[contains(text(),'Results')]",
            locateStrategy: 'xpath'
        },
        upcoming: {
            selector: "//a[contains(text(),'Upcoming')]",
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
