PORT ?= 8000
start:
	PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:$(PORT) -t public && psql -a -d $DATABASE_URL -f database.sql

lint:
	composer exec --verbose phpcs -- --standard=PSR12 public

install:
	composer install

db-start:
	sudo service postgresql start

db-status:
	sudo service postgresql status

db-stop:
	sudo service postgresql stop