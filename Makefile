ENV:=dev

all: install app-build

check-env:
ifneq ($(ENV),prod)
ifneq ($(ENV),dev)
	$(warning ENV must either be set on "prod" or "dev")
	@exit 1
endif
endif

npm-install: check-env
	$(info Installing NPM dependencies)
ifeq ($(ENV),prod)
	@yarn install --production
else
	@yarn install
endif

composer-install: check-env
	$(info Installing Composer dependencies)
ifeq ($(ENV),prod)
	@composer install --no-dev
else
	@composer install
endif

assets-install: check-env
	$(info Installing assets)
ifeq ($(ENV),prod)
	bin/console assets:install -e $(ENV)
else
	bin/console assets:install --symlink -e $(ENV)
endif

database-create: check-env
	$(info Creating database)
	@bin/console doctrine:database:create --if-not-exists -e $(ENV)

schema-update: check-env
	$(info Updating database schema)
	@bin/console doctrine:schema:update --force -e $(ENV)

fixtures-load: check-env
ifeq ($(ENV),dev)
	$(info Loading fixtures)
	@bin/console apimock:fixtures:load -vv -e $(ENV)
endif

app-build: check-env
	$(info Building application)
	@touch web/build.js
	@npm run $(ENV)

coke:
	$(info Checking PHP coding styles)
	@vendor/bin/coke

atoum:
	$(info Running PHP units tests)
	@vendor/bin/atoum

karma:
	$(info Running Javascript units tests)
	@npm run test:unit

database: database-create schema-update
dependencies: npm-install composer-install
install: dependencies database assets-install fixtures-load
tests: install coke atoum karma
