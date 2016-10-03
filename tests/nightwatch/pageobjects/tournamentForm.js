var tournamentFromCommands = {
    assertForm: function(data, client) {

        this.log('*** Verifying tournament form ***');

        var util;
        util = require('util');

        this.api.useXpath().waitForElementVisible('//body', 3000);

        if (data.hasOwnProperty('visible')) {
            data.visible.forEach(function(item) {
                this.waitForElementVisible('@'+item, 3000);
            }, this);
        }

        if (data.hasOwnProperty('not_visible')) {
            data.not_visible.forEach(function(item) {
                this.waitForElementNotVisible('@'+item, 1000);
            }, this);
        }

        if (data.hasOwnProperty('not_present')) {
            data.not_present.forEach(function(item) {
                this.waitForElementNotPresent('@'+item, 100);
            }, this);
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

        if (data.hasOwnProperty('errors')) {
            data.errors.forEach(function(item) {
                this.waitForElementVisible(util.format("//div[@id='error-list']/ul/li[contains(., '%s')]", item), 3000);
            }, this);
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

        return this;
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
                    case 'location':
                        mapSearch(this, data['location']);
                        break;
                }
            }
        }

        function mapSearch(client, data) {
            client
                .log('* Adding location *')
                .api.useXpath().clearValue("//input[@id='location_search']")
                .keys(data)
                .waitForElementVisible("//span[@class='pac-matched']", 5000)
                .sendKeys("//input[@id='location_search']", client.api.Keys.DOWN_ARROW)
                .pause(1000)
                .sendKeys("//input[@id='location_search']", client.api.Keys.TAB)
        }

        function fillFormInputs(client, data) {
            for (var property in data) {
                if (data.hasOwnProperty(property)) {
                    client.log('* Typing into field "' + property + '": ' + data[property] + ' *');
                    client.api.useXpath().clearValue("//input[@id='" + property + "']")
                        .setValue("//input[@id='" + property + "']", data[property]);
                }
            }
        }

        function fillFormSelects(client, data) {
            for (var property in data) {
                if (data.hasOwnProperty(property)) {
                    client.log('* Selecting in "' + property + '": ' + data[property] + ' *');
                    client.api.useXpath().click("//select[@id='" + property + "']")
                        .setValue("//select[@id='" + property + "']", data[property])
                        .keys(['\uE006']);
                }
            }
        }

        function fillFormTextareas(client, data) {
            for (var property in data) {
                if (data.hasOwnProperty(property)) {
                    client.log('* Typing into textarea "' + property + '": ' + data[property] + '* ');
                    client.api.useXpath().clearValue("//textarea[@id='" + property + "']")
                        .setValue("//textarea[@id='" + property + "']", data[property]);
                }
            }
        }

        function fillFormCheckboxes(client, data) {
            for (var property in data) {
                if (data.hasOwnProperty(property)) {
                    client.log('* Clicking checkbox "' + property + '" *');
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

        return this;
    }
};


module.exports = {
    commands: [tournamentFromCommands],
    elements: {
        location: {
            selector: "//div[@id='select_location']",
            locateStrategy: 'xpath'
        },
        players_number_disabled: {
            selector: "//input[@id='players_number' and @disabled]",
            locateStrategy: 'xpath'
        },
        map_loaded: {
            selector: "//div[@id='map']/div",
            locateStrategy: 'xpath'
        },
        location_search: {
            selector: "//input[@id='location_search']",
            locateStrategy: 'xpath'
        },
        map_suggestion: {
            selector: "//span[@class='pac-matched']",
            locateStrategy: 'xpath'
        },
        submit: {
            selector: "//input[@type='submit']",
            locateStrategy: 'xpath'
        },
        location_country: {
            selector: "//span[@id='country' and text()]",
            locateStrategy: 'xpath'
        },
        location_state: {
            selector: "//span[@id='state' and text()]",
            locateStrategy: 'xpath'
        },
        location_city: {
            selector: "//span[@id='city' and text()]",
            locateStrategy: 'xpath'
        },
        location_store: {
            selector: "//span[@id='store' and text()]",
            locateStrategy: 'xpath'
        },
        location_address: {
            selector: "//span[@id='address' and text()]",
            locateStrategy: 'xpath'
        },
        submit_button: {
            selector: "//input[@type='submit']",
            locateStrategy: 'xpath'
        }
    }
};
