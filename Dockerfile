FROM php:5-apache
RUN docker-php-ext-install mysql
WORKDIR /var/www/html
COPY src .
RUN chown www-data:www-data files/?/?
VOLUME /var/www/html/files
