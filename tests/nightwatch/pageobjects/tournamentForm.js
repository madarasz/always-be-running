var tournamentFromCommands = {

    // asserts tournament form contents
    assertForm: function(data, client) {

        this.log('*** Verifying tournament form ***');

        var util = require('util');

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
                    this.api.useXpath().assert.value(util.format(this.elements.inputs.selector, property), data.inputs[property]);
                }
            }
        }

        if (data.hasOwnProperty('textareas')) {
            for (var property in data.textareas) {
                if (data.textareas.hasOwnProperty(property)) {
                    this.api.useXpath().assert.value(util.format(this.elements.textareas.selector, property), data.textareas[property]);
                }
            }
        }

        if (data.hasOwnProperty('selects')) {
            for (var property in data.selects) {
                if (data.selects.hasOwnProperty(property)) {
                    this.api.useXpath().assert.value(util.format(this.elements.selects.selector, property), data.selects[property]);
                }
            }
        }

        if (data.hasOwnProperty('errors')) {
            for (var property in data.errors) {
                if (data.errors.hasOwnProperty(property)) {
                    this.api.useXpath().verify.elementPresent(util.format(this.elements.errorlist.selector, data.errors[property]));
                }
            }
        }

        if (data.hasOwnProperty('checkboxes')) {
            this.api.useCss();
            for (var property in data.checkboxes) {
                if (data.checkboxes.hasOwnProperty(property)) {
                    if (data.checkboxes[property]) {
                        this.api.verify.elementPresent(util.format(this.elements.checkboxes_checked.selector, property));
                    } else {
                        this.api.verify.elementPresent(util.format(this.elements.checkboxes.selector, property));
                        this.api.verify.elementNotPresent(util.format(this.elements.checkboxes_checked.selector, property));
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

    // inserts data on tournament form
    fillForm: function(data, client) {

        this.log('*** Filling out tournament form ***');

        var util = require('util');

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
            this.api.pause(100); // pause after each action
        }

        function mapSearch(client, data) {
            client
                .log('* Adding location *')
                .api.useXpath().clearValue(client.elements.location_search.selector)
                .keys(data)
                .waitForElementVisible(client.elements.map_suggestion.selector, 5000)
                .sendKeys(client.elements.location_search.selector, client.api.Keys.DOWN_ARROW)
                .pause(1000)
                .sendKeys(client.elements.location_search.selector, client.api.Keys.TAB)
        }

        function fillFormInputs(client, data) {
            for (var property in data) {
                if (data.hasOwnProperty(property)) {
                    client.log('* Typing into field "' + property + '": ' + data[property] + ' *');
                    client.api.useXpath()
                        .clearValue(util.format(client.elements.inputs.selector, property))
                        .setValue(util.format(client.elements.inputs.selector, property), data[property]);
                }
            }
        }

        function fillFormSelects(client, data) {
            for (var property in data) {
                if (data.hasOwnProperty(property)) {
                    client.log('* Selecting in "' + property + '": ' + data[property] + ' *');
                    client.api.useXpath().click(util.format(client.elements.selects.selector,property))
                        .setValue(util.format(client.elements.selects.selector,property), data[property])
                        .keys(['\uE006']);
                }
            }
        }

        function fillFormTextareas(client, data) {
            for (var property in data) {
                if (data.hasOwnProperty(property)) {
                    client.log('* Typing into textarea "' + property + '": ' + data[property] + '* ');
                    client.api.useXpath().clearValue(util.format(client.elements.textareas.selector,property))
                        .setValue(util.format(client.elements.textareas.selector,property), data[property]);
                }
            }
        }

        function fillFormCheckboxes(client, data) {
            for (var property in data) {
                if (data.hasOwnProperty(property)) {
                    client.log('* Clicking checkbox "' + property + '" *');
                    client.api.useCss().verify.elementPresent(util.format(client.elements.checkboxes.selector, property));

                    var required = data[property];

                    // check state
                    client.api.element('css selector', util.format(client.elements.checkboxes_checked.selector, property),
                        (function(required, property, client) {     // closure FTW
                            return function(found) {
                                client.log('- required state: ' + required);
                                var state = found.status != -1;
                                client.log('- current state: ' + state);
                                //client.log(JSON.stringify(found));

                                //click if needed
                                if (state != required) {
                                    client.log('- clicking!');
                                    client.api.useCss().click(util.format(client.elements.checkboxes.selector, property));
                                }
                            }
                        }(required, property, client))
                    );
                }
            }
        }

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;
    },

    validate: function(client) {

        this.log('* Validating tournament form page *');

        this.waitForElementVisible('@validator', 10000);

        if (typeof callback === "function"){
            callback.call(client);
        }

        return this;

    }
};


module.exports = {
    commands: [tournamentFromCommands],
    elements: {
        inputs: "//input[@id='%s']",
        textareas: "//textarea[@id='%s']",
        selects: "//select[@id='%s']",
        errorlist: "//div[@id='error-list']/ul/li[contains(., '%s')]",
        checkboxes: "input[id='%s']",                   // css selector!
        checkboxes_checked: "input:checked[id='%s']",    // css selector!
        validator: {
            selector: "//h4[contains(.,'Create new tournament') or contains(.,'Edit tournament')]",
            locateStrategy: 'xpath'
        },
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
