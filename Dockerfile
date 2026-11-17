FROM php:8.3-apache

RUN docker-php-ext-install mysqli \
    && a2enmod rewrite

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/workhub-entrypoint
COPY . /var/www/html

WORKDIR /var/www/html

RUN chmod +x /usr/local/bin/workhub-entrypoint \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["workhub-entrypoint"]
