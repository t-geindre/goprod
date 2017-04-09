var exec = require('child_process').exec;

module.exports = function () {
    this.After(function() {
        this.symfonyServer.stop();
        return this.driver.quit();
    });
};
