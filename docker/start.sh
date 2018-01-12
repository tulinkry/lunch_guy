#!/bin/bash

cd /usr/src/app && \
php /usr/bin/composer.phar --no-interaction install && \
php bin/console server:run 0.0.0.0:80 && \
echo "Serving on 0.0.0.0:80"
