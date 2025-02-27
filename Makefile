.PHONY: test test_debug

test:
	./vendor/bin/phpunit

test_debug:
	./vendor/bin/phpunit --debug
