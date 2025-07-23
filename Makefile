.PHONY: test test_debug lint

test:
	./vendor/bin/phpunit

test_debug:
	./vendor/bin/phpunit --debug

lint:
	composer lint
