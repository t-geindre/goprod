const seleniumWebdriver = require('selenium-webdriver');
const selectors = require('./config/selectors.json');
const assert = require('assert');

function getSelector(label) {
  var lowerLabel = label.toLowerCase();

  if (lowerLabel in selectors) {
    return selectors[lowerLabel];
  }

  throw 'Unable to find corresponding selector to "' + label + '"';
}

module.exports = function () {
  this.Given(/^I am on the homepage$/, function () {
    this.driver.get(this.symfonyServer.baseUrl);
      return this.driver.wait(
        seleniumWebdriver.until.elementLocated({css: getSelector('main container')})
      );
  });

  this.When(/^I click on (.*)$/, function (text) {
    return this.driver.findElement({css: getSelector(text)}).then(function(element) {
      return element.click();
    });
  });

  this.When(/^I fill (.*) with "([^"]*)"$/, function (label, text) {
    return this.driver.findElement({css: getSelector(label)}).then(function(element) {
      element.clear();
      element.sendKeys(text);
    });
  });

  this.When('I refresh the current page', function() {
    return this.driver.navigate().refresh();
  })

  this.Then(/^I should see "([^"]*)"$/, function (text) {
    var xpath = "//*[contains(text(),'" + text + "')]";
    var condition = seleniumWebdriver.until.elementLocated({xpath: xpath});
    return this.driver.wait(condition);
  });


  this.Then(/^(.*) should be hidden$/, function (label) {
    var selector = getSelector(label)+'[style*="display: none"]';
    var condition = seleniumWebdriver.until.elementLocated({css: selector});
    return this.driver.wait(condition);
  });
};


