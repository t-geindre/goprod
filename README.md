# Goprod

Yet another tool-deploy !

Requirements:
 * Yarn
 * Npm
 * NodeJS 6+
 * PHP 7+

## Features

 * Keeps deployments history:
   * Related Pullrequest
   * Or a description provided by user
 * Manages deployment queues
 * The whole deployment process in one place:
   * Merge Github Pullrequest
   * Delete associated branch
   * Golive deployment

## Building the application

### Development

Requirements:

 * PHP Sqlite extension

Build application and dependencies with the following command:

```shell
$ make
```

This will start a local webserver, just navigate to http://localhost:8000.

#### Tests

Requirements:

 * [Google Chrome](https://www.google.fr/chrome/) 57+

To run the full tests suite, use the following command:

```shell
$ make tests
```

### Production build

Run the following command to build application and install dependencies:

```shell
$ make build
```

This make the application ready to be serve on a production environment.

