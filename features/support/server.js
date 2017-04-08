var {exec, spawn} = require('child_process');
var yaml = require('js-yaml');
var fs = require('fs');

/**
 * Manage symfony server in test env
 */
const SymfonyServer = function() {
    // Read host from symfony config
    this.baseUrl = yaml
        .safeLoad(fs.readFileSync('app/config/config_test.yml', 'utf8'))
        .parameters['hosts.local'];

    this.host = this.baseUrl.replace(/http[s]*:\/\//gi, '');

    /**
     * @return this
     */
    this.start = function() {
        spawn('bin/console', [ 'server:start', this.host, '--env=test', '--no-ansi', '--force']);

        return this;
    }

    /**
     * @return this
     */
    this.stop = function() {
        spawn('bin/console', [ 'server:stop', this.host, '--env=test']);

        return this;
    }
};

module.exports = new SymfonyServer;
