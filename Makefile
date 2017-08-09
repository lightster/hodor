test:
	php vendor/bin/phpunit

install:
	composer install

install-test:
	php bin/hodor.php test:generate-config

install-test-travis:
	php bin/hodor.php test:generate-config --postgres-dbname=travisci_hodor

install-test-docker:
	php bin/hodor.php test:generate-config --postgres-host=postgres --rabbitmq-host=rabbitmq
