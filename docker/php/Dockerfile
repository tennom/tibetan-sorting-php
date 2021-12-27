FROM php:7.4-fpm-alpine
RUN apk --update --no-cache add autoconf g++ make && \
    pecl install -f xdebug && \
    docker-php-ext-enable xdebug && \
    apk del --purge autoconf g++ make

CMD ["php-fpm", "-F"]

WORKDIR /var/www/app

EXPOSE 9000