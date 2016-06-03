var tournamentFromCommands = {
    assertForm: function(data, client) {

        this.log('*** Verifying tournament form ***');

        var util;
        util = require('util');

        this.api.useXpath().waitForElementVisible('//body', 3000);

        if (data.hasOwnProperty('visible')) {
            data.visible.forEach(function(item) {
                this.waitForElementVisible('@'+item, 3000);
            });
        }

        if (data.hasOwnProperty('not_visible')) {
            data.not_visible.forEach(function(item) {
                this.waitForElementNotVisible('@'+item, 1000);
            });
        }

        if (data.hasOwnProperty('inputs')) {
            for (var property in data.inputs) {
                if (data.inputs.hasOwnProperty(property)) {
                    this.api.useXpath().assert.value("//input[@id='" + property + "']", data.inputs[property]);
                }
            }
        }

        if (data.hasOwnProperty('textareas')) {
            for (var property in data.textareas) {
                if (data.textareas.hasOwnProperty(property)) {
                    this.api.useXpath().assert.value("//textarea[@id='" + property + "']", data.textareas[property]);
                }
            }
        }

        if (data.hasOwnProperty('selects')) {
            for (var property in data.selects) {
                if (data.selects.hasOwnProperty(property)) {
                    this.api.useXpath().assert.value("//select[@id='" + property + "']", data.selects[property]);
                }
            }
        }

        if (data.hasOwnProperty('checkboxes')) {
            this.api.useCss();
            for (var property in data.checkboxes) {
                if (data.checkboxes.hasOwnProperty(property)) {
                    if (data.checkboxes[property]) {
                        this.api.verify.elementPresent("input:checked[name='" + property + "']");
                    } else {
                        this.api.verify.elementPresent("input[name='" + property + "']");
                        this.api.verify.elementNotPresent("input:checked[name='" + property + "']");
                    }
                }
            }
            this.api.useXpath();
        }

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this.api;
    },

    fillForm: function(data, client) {

        this.log('*** Filling out tournament form ***');

        for (var property in data) {
            if (data.hasOwnProperty(property)) {
                switch (property) {
                    case 'inputs':
                        fillFormInputs(this, data['inputs']);
                        break;
                    case 'textareas':
                        fillFormTextareas(this, data['textareas']);
                        break;
                    case 'selects':
                        fillFormSelects(this, data['selects']);
                        break;
                    case 'checkboxes':
                        fillFormCheckboxes(this, data['checkboxes']);
                        break;
                }
            }
        }

        function fillFormInputs(client, data) {
            for (var property in data) {
                if (data.hasOwnProperty(property)) {
                    client.api.useXpath().clearValue("//input[@id='" + property + "']")
                        .setValue("//input[@id='" + property + "']", data[property]);
                }
            }
        }

        function fillFormSelects(client, data) {
            for (var property in data) {
                if (data.hasOwnProperty(property)) {
                    client.api.useXpath().click("//select[@id='" + property + "']")
                        .setValue("//select[@id='" + property + "']", data[property])
                        .keys(['\uE006']);
                }
            }
        }

        function fillFormTextareas(client, data) {
            for (var property in data) {
                if (data.hasOwnProperty(property)) {
                    client.api.useXpath().clearValue("//textarea[@id='" + property + "']")
                        .setValue("//textarea[@id='" + property + "']", data[property]);
                }
            }
        }

        function fillFormCheckboxes(client, data) {
            for (var property in data) {
                if (data.hasOwnProperty(property)) {
                    // TODO: check state
                    //client.getValue("//input[@id='" + property + "']", (function(result) {
                    //   if (data[property] != (result.checked == 'checked')) {
                    client.api.useXpath().click("//input[@id='" + property + "']");
                    //}
                    //}(property)));
                }
            }
        }

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this.api;
    }
};


module.exports = {
    commands: [tournamentFromCommands],
    elements: {
        location_us_state: {
            selector: "//select[@id='location_us_state']",
            locateStrategy: 'xpath'
        },
        players_number: {
            selector: "//input[@id='players_number']",
            locateStrategy: 'xpath'
        },
        map: {
            selector: "//iframe[@id='map']",
            locateStrategy: 'xpath'
        },
        location: {
            selector: "//div[@id='select_location']",
            locateStrategy: 'xpath'
        },
        submit: {
            selector: "//input[@type='submit']",
            locateStrategy: 'xpath'
        }
    }
};
