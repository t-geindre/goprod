require('chromedriver')
var webdriver = require('selenium-webdriver');
var symfonyServer = require('./server.js');

function CustomWorld() {
    var chromeCapabilities = webdriver.Capabilities.chrome();
    chromeCapabilities.set(
        'chromeOptions',
        {
                'args': [
                    '--no-sandbox',
                    // See https://chromium.googlesource.com/chromium/src/+/lkgr/headless/README.md
                    '--headless',
                    '--disable-gpu',
                    // Without a remote debugging port, Google Chrome exits immediately.
                    ' --remote-debugging-port=9222',
                ]
            }
    );

    this.symfonyServer = symfonyServer.start();

    this.driver = new webdriver.Builder()
        .forBrowser('chrome')
        .withCapabilities(chromeCapabilities)
        .build();

    // Pause to let symfony server start
    this.driver.sleep(200);
}

module.exports = function() {
    this.World = CustomWorld;
};
