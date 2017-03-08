#!/bin/bash
phpunit.phar --colors --coverage-html ../../coverage/ --testsuite test "$1"
