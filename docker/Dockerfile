FROM php:7.1-cli
MAINTAINER Kryštof Tulinger <k.tulinger@seznam.cz>

ENV WWW_DIR /usr/src/app

RUN apt-get -y update && \
  apt-get -y install git zip poppler-utils && \
  apt-get -y clean

RUN echo "memory_limit = 2G" > /usr/local/etc/php/conf.d/memory-limit.ini

WORKDIR /usr/bin

RUN curl -sS https://getcomposer.org/installer | php

WORKDIR $WWW_DIR
COPY . $WWW_DIR

EXPOSE 80

ADD ./docker /docker
RUN chmod -R +x /docker
CMD ["/docker/start.sh"]
