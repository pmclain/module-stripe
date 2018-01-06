#!/usr/bin/env bash

set -e
trap '>&2 echo Error: Command \`$BASH_COMMAND\` on line $LINENO failed with exit code $?' ERR

# download stripe mock
if [ ! -d "stripe-mock/stripe-mock_${STRIPE_MOCK_VERSION}" ]; then
  mkdir -p stripe-mock/stripe-mock_${STRIPE_MOCK_VERSION}/
  curl -L "https://github.com/stripe/stripe-mock/releases/download/v${STRIPE_MOCK_VERSION}/stripe-mock_${STRIPE_MOCK_VERSION}_linux_amd64.tar.gz" -o "stripe-mock/stripe-mock_${STRIPE_MOCK_VERSION}_linux_amd64.tar.gz"
  tar -zxf "stripe-mock/stripe-mock_${STRIPE_MOCK_VERSION}_linux_amd64.tar.gz" -C "stripe-mock/stripe-mock_${STRIPE_MOCK_VERSION}/"
fi

# start stripe mock
stripe-mock/stripe-mock_${STRIPE_MOCK_VERSION}/stripe-mock > /dev/null &
STRIPE_MOCK_PID=$!

# mock mail
sudo service postfix stop
echo # print a newline
smtp-sink -d "%d.%H.%M.%S" localhost:2500 1000 &
echo 'sendmail_path = "/usr/sbin/sendmail -t -i "' > ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/sendmail.ini

# disable xdebug and adjust memory limit
echo > ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/xdebug.ini
echo 'memory_limit = -1' >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini
phpenv rehash;

composer selfupdate

# clone main magento github repository
git clone --branch $MAGENTO_VERSION --depth=1 https://github.com/magento/magento2

# install Magento
cd magento2

# add composer package under test, composer require will trigger update/install
composer config minimum-stability dev
composer config repositories.travis_to_test git https://github.com/pmclain/module-stripe.git
composer require pmclain/module-stripe:dev-master#$TRAVIS_COMMIT

# prepare for test suite
case $TEST_SUITE in
    integration)
        cp vendor/pmclain/module-stripe/Test/Integration/phpunit.xml.dist dev/tests/integration/phpunit.xml

        cd dev/tests/integration

        # create database and move db config into place
        mysql -uroot -e '
            SET @@global.sql_mode = NO_ENGINE_SUBSTITUTION;
            CREATE DATABASE magento_integration_tests;
        '
        mv etc/install-config-mysql.travis.php.dist etc/install-config-mysql.php

        cd ../../..
        ;;
    static)
        cd dev/tests/static

        echo "==> preparing changed files list"
        changed_files_ce="$TRAVIS_BUILD_DIR/dev/tests/static/testsuite/Magento/Test/_files/changed_files_ce.txt"
        php get_github_changes.php \
            --output-file="$changed_files_ce" \
            --base-path="$TRAVIS_BUILD_DIR" \
            --repo='https://github.com/magento/magento2.git' \
            --branch="$TRAVIS_BRANCH"
        cat "$changed_files_ce" | sed 's/^/  + including /'

        cd ../../..
        ;;
    unit)
        cp vendor/pmclain/module-stripe/Test/Unit/phpunit.xml.dist dev/tests/unit/phpunit.xml
    ;;
esac
