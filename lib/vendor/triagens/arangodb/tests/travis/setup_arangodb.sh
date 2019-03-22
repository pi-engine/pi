#!/bin/bash

echo "PHP version: $TRAVIS_PHP_VERSION"

if [[ "$TRAVIS_PHP_VERSION" == "5.6" ]] ; then 
wget "https://phar.phpunit.de/phpunit-5.7.phar"
mv phpunit-5.7.phar ./phpunit
fi

if [[ "$TRAVIS_PHP_VERSION" == "7.0" ]] ; then 
wget "https://phar.phpunit.de/phpunit-6.0.phar"
mv phpunit-6.0.phar ./phpunit
fi

if [[ "$TRAVIS_PHP_VERSION" == "7.1" ]] ; then 
wget "https://phar.phpunit.de/phpunit-6.0.phar"
mv phpunit-6.0.phar ./phpunit
fi

if [[ "$TRAVIS_PHP_VERSION" == "hhvm" ]] ; then 
wget "https://phar.phpunit.de/phpunit-6.0.phar"
mv phpunit-6.0.phar ./phpunit
fi

if [[ "$TRAVIS_PHP_VERSION" == "hhvm-3.18" ]] ; then 
wget "https://phar.phpunit.de/phpunit-5.7.phar"
mv phpunit-5.7.phar ./phpunit
fi

chmod +x ./phpunit

echo "./phpunit --version"
./phpunit --version

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $DIR

docker pull arangodb/arangodb-preview:devel
docker run -d -e ARANGO_ROOT_PASSWORD="test" -p 8529:8529 arangodb/arangodb:3.4.1

sleep 2

n=0
# timeout value for startup
timeout=60 
while [[ (-z `curl -H 'Authorization: Basic cm9vdDp0ZXN0' -s 'http://127.0.0.1:8529/_api/version' `) && (n -lt timeout) ]] ; do
  echo -n "."
  sleep 1s
  n=$[$n+1]
done

if [[ n -eq timeout ]];
then
    echo "Could not start ArangoDB. Timeout reached."
    exit 1
fi

echo "ArangoDB is up"
