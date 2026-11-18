FROM php:8.3-apache

RUN docker-php-ext-install mysqli \
    && a2enmod rewrite

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache/server-name.conf /etc/apache2/conf-available/server-name.conf
COPY docker/entrypoint.sh /usr/local/bin/workhub-entrypoint
COPY . /var/www/html

WORKDIR /var/www/html

RUN a2enconf server-name \
    && sed -i 's/\r$//' /usr/local/bin/workhub-entrypoint \
    && chmod +x /usr/local/bin/workhub-entrypoint \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80

HEALTHCHECK --interval=15s --timeout=3s --start-period=15s --retries=5 \
    CMD php -r '$conexao = @fsockopen("127.0.0.1", 80); exit($conexao ? 0 : 1);'

CMD ["workhub-entrypoint"]
