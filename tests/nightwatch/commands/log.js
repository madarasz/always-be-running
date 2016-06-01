exports.command = function (message) {
    return this.perform(function () {
        console.log(message);
    });
};