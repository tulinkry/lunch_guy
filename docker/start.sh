#!/bin/bash

cd /usr/src/app && \
php /usr/bin/composer.phar --no-interaction install && \
echo "Serving on 0.0.0.0:80" && \
php bin/console server:run 0.0.0.0:80
