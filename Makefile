ENV:=dev
SHELL := /bin/bash

all: install app-build

npm-install:
	$(info Installing NPM dependencies)
	@if [ "$(ENV)" = "prod" ] ; then \
		yarn install --production ;\
	else \
		yarn install ;\
	fi

composer-install:
	$(info Installing Composer dependencies)
	@if [ "$(ENV)" = "prod" ] ; then \
		composer install --no-dev --no-interaction ;\
	else \
		composer install --no-interaction ;\
	fi

assets-install:
	$(info Installing assets)
	@if [ "$(ENV)" = "prod" ] ; then \
		bin/console assets:install -e $(ENV) ;\
	else \
		bin/console assets:install --symlink -e $(ENV) ;\
	fi

database-create:
	$(info Creating database)
	# SQLite/Doctrine issue, see: https://github.com/doctrine/dbal/pull/2402
	@if [ "$(ENV)" = "test" -o "$(ENV)" = "dev" ] ; then \
		bin/console doctrine:database:create -e $(ENV) ;\
	else \
		bin/console doctrine:database:create --if-not-exists -e $(ENV) ;\
	fi

database-drop:
	$(info Droping database)
	# SQLite/Doctrine issue, see: https://github.com/doctrine/dbal/pull/2402
	@if [ "$(ENV)" = "test" -o "$(ENV)" = "dev" ] ; then \
		bin/console doctrine:database:drop --force -e $(ENV) ;\
	else \
		bin/console doctrine:database:force --if-exists -e $(ENV) ;\
	fi

schema-update:
	$(info Updating database schema)
	@bin/console doctrine:schema:update --force -e $(ENV)

fixtures-load:
	@if [ "$(ENV)" = "dev" -o "$(ENV)" = "test" ] ; then \
		echo Loading fixtures ;\
		bin/console apimock:fixtures:load -vv -e $(ENV) ;\
	fi

app-build:
	$(info Building application)
	@touch web/build.js
	@if [ "$(ENV)" = "dev" ] ; then \
		npm run dev ;\
	else \
		npm run build ;\
	fi

eslinter:
	$(info Checking Javascript coding styles)
	@./node_modules/.bin/eslint "src/FrontBundle/Resources/scripts/component/**"

eslinter-fix:
	$(info Fixing Javascript coding styles)
	@./node_modules/.bin/eslint "src/FrontBundle/Resources/scripts/component/**" --fix

coke:
	$(info Checking PHP coding styles)
	@vendor/bin/coke

atoum:
	$(info Running PHP units tests)
	@vendor/bin/atoum

karma:
	$(info Running Javascript units tests)
	@npm run test:unit

cucumber: ENV=test
cucumber: database-drop database fixtures-load
	$(info Running cucumber functionals tests)
	@./node_modules/.bin/cucumber-js

database: database-create schema-update

dependencies: npm-install composer-install

install: dependencies database assets-install fixtures-load

tests: ENV=test
tests: dependencies database-drop all coke eslinter atoum karma cucumber

build: ENV=prod
build: all
