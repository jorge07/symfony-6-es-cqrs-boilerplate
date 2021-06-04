env=dev
docker-os=
compose=docker-compose -f docker-compose.yml -f etc/$(env)/docker-compose.yml

ifeq ($(docker-os), windows)
	ifeq ($(env), dev)
		compose += -f etc/dev/docker-compose.windows.yml
	endif
endif

export compose env docker-os

.PHONY: start
start: erase build start-deps up db ## clean current environment, recreate dependencies and spin up again

.PHONY: start-deps
start-deps:  ## Start all dependencies and wait for it
		$(compose) run --rm start_dependencies

.PHONY: stop
stop: ## stop environment
		$(compose) stop $(s)

.PHONY: rebuild
rebuild: start ## same as start

.PHONY: erase
erase: ## stop and delete containers, clean volumes.
		touch .env.blackfire
		$(compose) stop
		docker-compose rm -v -f

.PHONY: build
build: ## build environment and initialize composer and project dependencies
		$(compose) build --parallel

		if [ env = "prod" ]; then \
			echo Building in $(env) mode; \
			$(compose) run --rm php sh -lc 'xoff;COMPOSER_MEMORY_LIMIT=-1 composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --no-suggest --optimize-autoloader'; \
		else \
			$(compose) run --rm php sh -lc 'xoff;COMPOSER_MEMORY_LIMIT=-1 composer install'; \
		fi

.PHONY: build-ci

.PHONY: artifact
artifact: ## build production artifact
		docker-compose -f etc/artifact/docker-compose.yml build

.PHONY: composer-update
composer-update: ## Update project dependencies
		$(compose) run --rm code sh -lc 'xoff;COMPOSER_MEMORY_LIMIT=-1 composer update'

.PHONY: up
up: ## spin up environment
		$(compose) up -d

.PHONY: phpunit
phpunit: db ## execute project unit tests
		$(compose) exec -T php sh -lc "XDEBUG_MODE=coverage ./vendor/bin/phpunit $(conf)"

.PHONY: coverage
coverage:
		$(compose) run --rm php sh -lc "wget -q https://github.com/php-coveralls/php-coveralls/releases/download/v2.2.0/php-coveralls.phar; \
			chmod +x php-coveralls.phar; \
			export COVERALLS_RUN_LOCALLY=1; \
			export COVERALLS_EVENT_TYPE='manual'; \
			export CI_NAME='github-actions'; \
			php ./php-coveralls.phar -v; \
		"
.PHONY: phpstan
phpstan: ## executes php analizers
		$(compose) run --rm code sh -lc './vendor/bin/phpstan analyse -l 6 -c phpstan.neon src tests'

.PHONY: psalm
psalm: ## execute psalm analyzer
		$(compose) run --rm code sh -lc './vendor/bin/psalm --show-info=false'

.PHONY: cs
cs: ## executes coding standards
		$(compose) run --rm code sh -lc './vendor/bin/ecs check src tests --fix'

.PHONY: cs-check
cs-check: ## executes coding standards in dry run mode
# Disabled until Sylius upgrade
# 		$(compose) run --rm code sh -lc './vendor/bin/ecs check src tests'

.PHONY: layer
layer: ## Check issues with layers
		$(compose) run --rm code sh -lc 'bin/deptrac.phar analyze --formatter-graphviz=0'

.PHONY: db
db: ## recreate database
		$(compose) exec -T php sh -lc './bin/console d:d:d --force --if-exists'
		$(compose) exec -T php sh -lc './bin/console d:d:c --if-not-exists'
		$(compose) exec -T php sh -lc './bin/console d:m:m -n'
.PHONY: dmd
dmd: ## Generate migrations diff file
		$(compose) exec -T php sh -lc './bin/console d:m:diff'
.PHONY: schema-validate
schema-validate: ## validate database schema
		$(compose) exec -T php sh -lc './bin/console d:s:v'

.PHONY: xon
xon: ## activate xdebug simlink
		$(compose) exec -T php sh -lc 'xon | true'

.PHONY: xoff
xoff: ## deactivate xdebug
		$(compose) exec -T php sh -lc 'xoff | true'
		make s='php workers_events workers_users' stop
		make up

.PHONY: sh
sh: ## gets inside a container, use 's' variable to select a service. make s=php sh
		$(compose) exec $(s) sh -l

.PHONY: logs
logs: ## look for 's' service logs, make s=php logs
		$(compose) logs -f $(s)

.PHONY: minikube
minikube:
		@eval $$(minikube docker-env); \
		docker-compose -f etc/artifact/docker-compose.yml build --parallel; \
		helm dep up etc/artifact/chart; \
		helm upgrade -i cqrs etc/artifact/chart

.PHONY: htemplate
htemplate:
		helm template cqrs etc/artifact/chart

.PHONY: help
help: ## Display this help message
	@cat $(MAKEFILE_LIST) | grep -e "^[a-zA-Z_\-]*: *.*## *" | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
