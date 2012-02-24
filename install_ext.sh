#!/bin/sh

git clone git://github.com/nicolasff/phpredis.git
cd phpredis
phpize
./configure
make
sudo make install
sudo su -c "echo \"extension=redis.so\" >>  `php --ini | grep \"Loaded Configuration\" | sed -e \"s|.*:\\s*||\"`"