const seleniumWebdriver = require('selenium-webdriver');
const assert = require('assert');

module.exports = function () {

  this.Given(/^I am logged in as (.+)$/, function (userName) {
    return  this.driver.get(this.symfonyServer.baseUrl)
      .then(() => this.waitElementLocated('login button'))
      .then((el) => {
        el.click();
        return this.waitElementLocated(userName);
      })
      .then((el) => {
        el.click();
        return this.waitElementLocated('main container');
      })
  })

  this.Given(/^I am on the homepage$/, function () {
    this.driver.get(this.symfonyServer.baseUrl);
    return this.waitElementLocated('main container');
  });

  this.When(/^I click on (.+)$/, function (label) {
    return this.waitElementLocated(label)
      .then((el) => el.click());
  });

  this.When(/^I fill (.+) with "([^"]*)"$/, function (label, text) {
    return this.waitElementLocated(label)
      .then((el) => {
        el.clear();
        el.sendKeys(text);
      });
  });

  this.When('I refresh the current page', function() {
    return this.driver.navigate().refresh();
  })

  this.Then(/^I should see "([^"]*)"$/, function (text) {
    return this.waitElementLocated({ xpath: "//*[contains(text(),\"" + text + "\")]" });
  });

  this.Then(/^I should see ([^"]*)$/, function (label) {
    return this.waitElementLocated(label)
      .then((el) => this.waitElementVisible(el));
  });

  this.Then(/^(.+) should be hidden$/, function (label) {
    return this.waitElementLocated(label)
      .then((el) => this.waitElementNotVisible(el));
  });

  this.Then(/^(.+) should be (enabled|disabled)$/, function (label, state) {
    return this.waitElementLocated({ css: this.getSelector(label)+':'+state });
  });

  this.When(/^I press return on (.+)$/, function (label) {
    return this.waitElementLocated(label)
      .then((el) => el.sendKeys(seleniumWebdriver.Key.RETURN))
  })
};


