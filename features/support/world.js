require('chromedriver')
const webdriver = require('selenium-webdriver');
const symfonyServer = require('./server.js');
const selectors = require('./config/selectors.json');
const seleniumWebdriver = require('selenium-webdriver');

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

    this.getSelector = function (label) {
      var lowerLabel = label.toLowerCase();

      if (lowerLabel in selectors) {
        return selectors[lowerLabel];
      }

      throw 'Unable to find corresponding selector to "' + label + '"';
    }

    this.waitElementLocated = function (selector) {
      if (typeof selector === "string") {
        selector = { css: this.getSelector(selector) };
      }

      return this.driver.wait(seleniumWebdriver.until.elementLocated(selector));
    }

    this.waitElementVisible = function (element) {
      return this.driver.wait(
        seleniumWebdriver.until.elementIsVisible(element)
      )
    }

    this.waitElementNotVisible = function (element) {
      return this.driver.wait(
        seleniumWebdriver.until.elementIsNotVisible(element)
      )
    }
}

module.exports = function () {
    this.World = CustomWorld;
};
