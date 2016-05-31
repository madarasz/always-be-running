exports.assertion = function(data) {

    var client = this;

    this.message = 'Tournament form validation failed.';
    this.expected = true;
    this.pass = function(value) {
        return value == this.expected;
    };
    this.value = function(result) {
        return result;
    };

    this.command = function(callback) {
        var selectors = {
            location_us_state: "//select[@id='location_us_state']",
            players_number: "//input[@id='players_number']",
            map: "//iframe[@id='map']"
        };

        this.api
            .waitForElementVisible('//body', 3000);

        if (data.hasOwnProperty('visible')) {
            data.visible.forEach(function(item) {
                client.api.waitForElementVisible(selectors[item], 1000);
            });
        }

        if (data.hasOwnProperty('not_visible')) {
            data.not_visible.forEach(function(item) {
                client.api.waitForElementNotVisible(selectors[item], 1000);
            });
        }

        if (data.hasOwnProperty('inputs')) {
            for (var property in data.inputs) {
                if (data.inputs.hasOwnProperty(property)) {
                    client.api.assert.value("//input[@id='" + property + "']", data.inputs[property]);
                }
            }
        }

        if (data.hasOwnProperty('textareas')) {
            for (var property in data.textareas) {
                if (data.textareas.hasOwnProperty(property)) {
                    client.api.assert.value("//textarea[@id='" + property + "']", data.textareas[property]);
                }
            }
        }

        if (data.hasOwnProperty('selects')) {
            for (var property in data.selects) {
                if (data.selects.hasOwnProperty(property)) {
                    client.api.assert.value("//select[@id='" + property + "']", data.selects[property]);
                }
            }
        }

        if (data.hasOwnProperty('checkboxes')) {
            client.api.useCss();
            for (var property in data.checkboxes) {
                if (data.checkboxes.hasOwnProperty(property)) {
                    if (data.checkboxes[property]) {
                        client.api.verify.elementPresent("input:checked[name='" + property + "']");
                    } else {
                        client.api.verify.elementPresent("input[name='" + property + "']");
                        client.api.verify.elementNotPresent("input:checked[name='" + property + "']");
                    }
                }
            }
            client.api.useXpath();
        }

        return true;
    };

};