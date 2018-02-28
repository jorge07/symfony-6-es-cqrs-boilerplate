start: erase build up

stop:
		docker-compose stop

rebuild: start

erase:
		docker-compose stop
		docker-compose rm -v -f

build:
		docker-compose build
		docker-compose exec php sh -lc 'composer install'
		docker-compose exec php sh -lc 'dev d:m:m -n'

up:
		docker-compose up -d

tests:
		docker-compose exec php sh -lc './bin/phpunit'

style:
		docker-compose exec php sh -lc './vendor/bin/phpstan analyse -l 5 -c phpstan.neon src tests'

xon:
		docker-compose exec php sh -lc 'xon'

xoff:
		docker-compose exec php sh -lc 'xoff'

sh:
		docker-compose exec $(s) sh -l

logs:
		docker-compose logs -f $(s)