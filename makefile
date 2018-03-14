.PHONY: start
start: erase build up ## clean current environment, recreate dependencies and spin up again

.PHONY: stop
stop: ## stop environment
		docker-compose stop

.PHONY: rebuild
rebuild: start ## same as start

.PHONY: erase
erase: ## stop and delete containers, clean volumes.
		docker-compose stop
		docker-compose rm -v -f

.PHONY: build
build: ## build environment and initialize composer and project dependencies
		docker-compose build
		docker-compose up -d
		docker-compose exec php sh -lc 'composer install'
		docker-compose exec php sh -lc 'dev d:m:m -n'

.PHONY: up
up: ## spin up environment
		docker-compose up -d

.PHONY: tests
tests: ## execute project unit tests
		docker-compose exec php sh -lc './bin/phpunit'

.PHONY: style
style: ## executes analizers
		docker-compose exec php sh -lc './vendor/bin/phpstan analyse -l 5 -c phpstan.neon src tests'

.PHONY: xon
xon: ## activate xdebug simlink
		docker-compose exec php sh -lc 'xon'

.PHONY: xoff
xoff: ## deactivate xdebug
		docker-compose exec php sh -lc 'xoff'

.PHONY: sh
sh: ## gets inside a container, use 's' variable to select a service. make s=php sh
		docker-compose exec $(s) sh -l

.PHONY: logs
logs: ## look for 's' service logs
		docker-compose logs -f $(s)

.PHONY: help
help: ## Display this help message
	@cat $(MAKEFILE_LIST) | grep -e "^[a-zA-Z_\-]*: *.*## *" | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
