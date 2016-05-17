exports.command = function (data, callback) {
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
                client.clearValue("//input[@id='" + property + "']")
                    .setValue("//input[@id='" + property + "']", data[property]);
            }
        }
    }

    function fillFormSelects(client, data) {
        for (var property in data) {
            if (data.hasOwnProperty(property)) {
                client.click("//select[@id='" + property + "']")
                    .setValue("//select[@id='" + property + "']", data[property])
                    .keys(['\uE006']);
            }
        }
    }

    function fillFormTextareas(client, data) {
        for (var property in data) {
            if (data.hasOwnProperty(property)) {
                client.clearValue("//textarea[@id='" + property + "']")
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
                       client.click("//input[@id='" + property + "']");
                   //}
                //}(property)));
            }
        }
    }

    if (typeof callback === "function"){
        callback.call(client);
    }

    return this;
};