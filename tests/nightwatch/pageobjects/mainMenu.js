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
