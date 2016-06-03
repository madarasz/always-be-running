var tableCommands = {
    assertTable: function(table_id, title, data, callback) {

        this.log('*** Verifying tournament table ('+table_id+'): '+title+' ***');

        var util = require('util');

        this.api.useXpath()
            .waitForElementVisible(util.format(this.elements.row.selector, table_id, title), 1000);

        if (data.hasOwnProperty('texts')) {
            data.texts.forEach(function(item) {
                this.api.waitForElementVisible(util.format(this.elements.text.selector, table_id, title, item), 1000);
            }, this);
        }

        if (data.hasOwnProperty('labels')) {
            data.labels.forEach(function(item) {
                this.api.waitForElementVisible(util.format(this.elements.label.selector, table_id, title, item), 1000);
            }, this);
        }

        if (data.hasOwnProperty('texts_missing')) {
            data.texts_missing.forEach(function(item) {
                this.api.assert.elementNotPresent(util.format(this.elements.text.selector, table_id, title, item));
            }, this);
        }

        if (typeof callback === "function") {
            callback.call(client);
        }

        return this.api;
    },

    selectTournament: function(table_id, title, action, callback) {

        this.log('*** Performing "'+action+'" on tournament table ('+table_id+'): '+title+' ***');

        var util = require('util');

        if (action === 'delete') {
            this.api.click(util.format(this.elements.deleteButton.selector, table_id, title)).
                api.assert.elementNotPresent(util.format(this.elements.row.selector, table_id, title));
        } else {
            this.api.click(util.format(this.elements.button.selector, table_id, title, action));
        }

        if (typeof callback === "function") {
            callback.call(client);
        }

        return this.api;
    }
};

module.exports = {
    commands: [tableCommands],
    elements: {
        row: "//table[@id='%s']/tbody/tr/td[contains(.,'%s')]",
        text: "//table[@id='%s']/tbody/tr/td[contains(.,'%s')]/../td[contains(.,'%s')]",
        label: "//table[@id='%s']/tbody/tr/td[contains(.,'%s')]/../td/span[contains(.,'%s')]",
        deleteButton: "//table[@id='%s']/tbody/tr/td[contains(.,'%s')]/../td/form/button[contains(.,'delete')]",
        button: "//table[@id='%s']/tbody/tr/td[contains(.,'%s')]/../td/a[contains(.,'%s')]"
    }
};
