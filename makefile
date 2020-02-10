env=dev
compose=docker-compose -f docker-compose.yml -f docker-compose.$(env).yml

export compose env 

.PHONY: start
start: erase build up db ## clean current environment, recreate dependencies and spin up again

.PHONY: stop
stop: ## stop environment
		$(compose) stop

.PHONY: rebuild
rebuild: start ## same as start

.PHONY: erase
erase: ## stop and delete containers, clean volumes.
		$(compose) stop
		docker-compose rm -v -f

.PHONY: build
build: ## build environment and initialize composer and project dependencies
		$(compose) build
		$(compose) run --rm php sh -lc 'xoff;COMPOSER_MEMORY_LIMIT=-1 composer install'
.PHONY: build-ci

.PHONY: artifact
artifact: ## build production artifact
		docker-compose -f docker-compose.prod.yml build

.PHONY: composer-update
composer-update: ## Update project dependencies
		$(compose) run --rm php sh -lc 'xoff;COMPOSER_MEMORY_LIMIT=-1 composer update'

.PHONY: up
up: ## spin up environment
		$(compose) up -d

.PHONY: phpunit
phpunit: db ## execute project unit tests
		$(compose) exec -T php sh -lc "./vendor/bin/phpunit $(conf)"

.PHONY: style
style: ## executes php analizers
		$(compose) run --rm php sh -lc './vendor/bin/phpstan analyse -l 6 -c phpstan.neon src tests'

.PHONY: cs
cs: ## executes coding standards
		$(compose) run --rm php sh -lc './vendor/bin/ecs check src tests --fix'

.PHONY: cs-check
cs-check: ## executes coding standards in dry run mode
		$(compose) run --rm php sh -lc './vendor/bin/ecs check src tests'

.PHONY: layer
layer: ## Check issues with layers
		$(compose) run --rm php sh -lc 'php bin/deptrac.phar analyze --formatter-graphviz=0'

.PHONY: db
db: ## recreate database
		$(compose) exec -T php sh -lc './bin/console d:d:d --force'
		$(compose) exec -T php sh -lc './bin/console d:d:c'
		$(compose) exec -T php sh -lc './bin/console d:m:m -n'
.PHONY: schema-validate
schema-validate: ## validate database schema
		$(compose) exec -T php sh -lc './bin/console d:s:v'

.PHONY: xon
xon: ## activate xdebug simlink
		$(compose) exec -T php sh -lc 'xon'

.PHONY: xoff
xoff: ## deactivate xdebug
		$(compose) exec -T php sh -lc 'xoff'

.PHONY: sh
sh: ## gets inside a container, use 's' variable to select a service. make s=php sh
		$(compose) exec $(s) sh -l

.PHONY: logs
logs: ## look for 's' service logs, make s=php logs
		$(compose) logs -f $(s)

.PHONY: wait-for-elastic
wait-for-elastic: ## Health check for elastic
		$(compose) run --rm php sh -lc 'sh ./etc/ci/wait-for-elastic.sh elasticsearch:9200'

.PHONY: help
help: ## Display this help message
	@cat $(MAKEFILE_LIST) | grep -e "^[a-zA-Z_\-]*: *.*## *" | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
