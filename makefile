start: erase build db up

stop:
		docker-compose stop

rebuild: start

erase:
		docker-compose stop
		docker-compose rm -v -f

build:
		docker-compose build
		docker-compose run php sh -lc 'composer install'

up:
		docker-compose up -d

db:
		docker-compose exec php sh -lc './bin/console d:d:d --force'
		docker-compose exec php sh -lc './bin/console d:d:c'
		docker-compose exec php sh -lc './bin/console d:m:m -n'

phpunit: db
		docker-compose exec php sh -lc './bin/phpunit'

style:
		docker-compose run php sh -lc './vendor/bin/phpstan analyse -l 5 -c phpstan.neon src tests'

xon:
		docker-compose exec php sh -lc 'xon'

xoff:
		docker-compose exec php sh -lc 'xoff'

sh:
		docker-compose exec $(s) sh -l

logs:
		docker-compose logs -f $(s)
