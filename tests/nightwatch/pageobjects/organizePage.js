var organizeCommands = {
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
