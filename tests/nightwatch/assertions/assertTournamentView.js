exports.assertion = function(data) {

    var util;
    util = require('util');

    this.message = 'Tournament view validation failed.';
    this.expected = true;
    this.pass = function(value) {
        return value == this.expected;
    };
    this.value = function(result) {
        return result;
    };

    this.command = function(callback) {
        var selectors = {
            title: "//h3[contains(., '%s')]",
            ttype: "//h3/small[contains(., '%s')]",
            description: "//div[contains(@class, 'panel-body') and contains(., '%s')]",
            date: "//h4[contains(., '%s')]",
            time: "//p[contains(., '%s')]",
            country: "//h4[contains(., '%s')]",
            state: "//h4[contains(., '%s')]",
            city: "//h4[contains(., '%s')]",
            store: "//p[contains(., '%s')]",
            address: "//p[contains(., '%s')]",
            map: "//iframe[@id='map']",
            approvalNeed: "//div[@id='approval-needed']",
            approvalRejected: "//div[@id='approval-rejected']",
            editButton: "//div[@id='control-buttons']/form/a[contains(., 'Edit')]",
            deleteButton: "//div[@id='control-buttons']/form/button[contains(., 'Delete')]",
            approveButton: "//div[@id='control-buttons']/form/a[contains(., 'Approve')]",
            rejectButton: "//div[@id='control-buttons']/form/a[contains(., 'Reject')]",
            conflictWarning: "//div[@id='conflict-warning']",
            playerNumbers: "//div[@id='player-numbers']",
            topPlayerNumbers: "//span[@id='top-player-numbers']",
            playerClaim: "//ul[@id='player-claim']",
            createClaimFrom: "//form[@id='create-claim']",
            topEntriesTable: "//table[@id='entries-top']",
            swissEntriesTable: "//table[@id='entries-swiss']",
            dueWarning: "//div[@id='due-warning']",
            registeredPlayers: "//ul[@id='registered-players']",
            noRegisteredPlayers: "//em[@id='no-registered-players']",
            unregisterButtonDisabled: "//span[@id='unregister-disabled']",
            unregisterButton: "//a[@id='unregister']",
            RegisterButton: "//a[@id='register']"
        };

        this.api.waitForElementVisible('//body', 3000);

        for (var property in data) {
            if (data.hasOwnProperty(property)) {
                if (data[property] === true) {
                    this.api.waitForElementVisible(selectors[property], 1000);
                } else if (data[property] === false) {
                    this.api.verify.elementNotPresent(selectors[property]);
                } else {
                    this.api.waitForElementVisible(util.format(selectors[property], data[property]), 1000);
                }
            }
        }

        return true;
    };

};