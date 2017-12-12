var tableCommands = {

    // asserts contents of tournament table
    assertTable: function(table_id, title, data, callback) {

        this.log('*** Verifying tournament table ('+table_id+'): '+title+' ***');

        var util = require('util');

        this.api.useXpath()
            .waitForElementPresent(util.format(this.elements.row.selector, table_id, title), 5000);

        if (data.hasOwnProperty('texts')) {
            data.texts.forEach(function(item) {
                this.api.verify.elementPresent(util.format(this.elements.text.selector, table_id, title, item));
            }, this);
        }

        if (data.hasOwnProperty('labels')) {
            data.labels.forEach(function(item) {
                this.api.verify.elementPresent(util.format(this.elements.label.selector, table_id, title, item));
            }, this);
        }

        if (data.hasOwnProperty('buttons')) {
            data.buttons.forEach(function(item) {
                this.api.verify.elementPresent(util.format(this.elements.button.selector, table_id, title, item));
            }, this);
        }
        if (data.hasOwnProperty('icons')) {
            data.icons.forEach(function(item) {
                this.api.verify.elementPresent(util.format(this.elements.icon.selector, table_id, title, item));
            }, this);
        }


        if (data.hasOwnProperty('texts_missing')) {
            data.texts_missing.forEach(function(item) {
                this.api.assert.elementNotPresent(util.format(this.elements.text.selector, table_id, title, item));
            }, this);
        }

        if (data.hasOwnProperty('multi_day') && data['multi_day']) {
            this.api.verify.elementPresent(util.format(this.elements.multiDay.selector, table_id, title));
        }

        if (typeof callback === "function") {
            callback.call(client);
        }

        return this;
    },

    // checks that a row is missing for tournament table
    assertMissingRow: function(table_id, title, callback) {
        this.log('*** Verifying missing row on table ('+table_id+'): '+title+' ***');

        var util = require('util');

        this.api.useXpath()
            .waitForElementVisible(util.format(this.elements.table.selector, table_id), 5000);

        this.api.assert.elementNotPresent(util.format(this.elements.row.selector, table_id, title), 1000);

        if (typeof callback === "function") {
            callback.call(client);
        }

        return this;
    },

    // clicks tournament for tournament detailed view
    selectTournament: function(table_id, title, callback) {

        this.log('*** Clicking tournament on table ('+table_id+'): '+title+' ***');

        var util = require('util'),
            selector = util.format(this.elements.title.selector, table_id, title);

        // check if row exists
        this.api.useXpath().waitForElementVisible('//body', 3000);
        this.api.useXpath().waitForElementPresent(selector, 5000);

        // click row
        this.api.useXpath()
            .getLocationInView(selector)
            .click(selector);

        if (typeof callback === "function") {
            callback.call(client);
        }

        return this;
    },

    // clicks button of action
    selectTournamentAction: function(table_id, title, action, callback) {

        this.log('*** Performing "'+action+'" on tournament table ('+table_id+'): '+title+' ***');
        this.api.useXpath();

        var util = require('util');

        if (action == 'delete' || action == 'remove') {
            // click delete / remove
            var selector = util.format(this.elements.actionButton.selector, table_id, title, action);
            this.api
                .getLocationInView(selector)
                .click(util.format(selector))
                .acceptAlert();
            // TODO: accept dialog workaround for PhantomJS
            //this.api.execute(function() {
            //    window.confirm = function(msg){return true;};
            //    return true;
            //});
            // check if delete was successful
            this.log('*** Checking if delete was successfull ***');
            this.api.assert.elementNotPresent(util.format(this.elements.row.selector, table_id, title));
        } else {
            var selector = util.format(this.elements.button.selector, table_id, title, action);
            this.api
                .getLocationInView(selector)
                .click(selector);
        }

        if (typeof callback === "function") {
            callback.call(client);
        }

        return this;
    }
};

module.exports = {
    commands: [tableCommands],
    elements: {
        table: "//table[@id='%s']/tbody",
        row: "//table[@id='%s']/tbody/tr/td[contains(.,'%s')]",
        text: "//table[@id='%s']/tbody/tr/td[contains(.,'%s')]/../td[contains(.,'%s')]",
        label: "//table[@id='%s']/tbody/tr/td[contains(.,'%s')]/../td/span[contains(.,'%s')]",
        icon: "//table[@id='%s']/tbody/tr/td[contains(.,'%s')]/../td//i[contains(@title,'%s')]",
        multiDay: "//table[@id='%s']/tbody/tr/td[contains(.,'%s')]/../td/span/i[contains(@class, 'fa-plus-circle')]",
        actionButton: "//table[@id='%s']/tbody/tr/td[contains(.,'%s')]/../td/form/button[contains(.,'%s')]",
        button: "//table[@id='%s']/tbody/tr/td[contains(.,'%s')]/../td/*[contains(.,'%s')]",
        title: "//table[@id='%s']/tbody/tr/td/a[contains(.,'%s')]",
        nextButton: "//a[@id='%s-controls-forward']",
        resultsTab:  {
            selector: "//li[@id='t-results']",
            locateStrategy: 'xpath'
        },
        toBeConcludedTab:  {
             selector: "//li[@id='t-to-be-concluded']",
            locateStrategy: 'xpath'
        }
    }
};
