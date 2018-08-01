FROM php:7.2.7-apache-stretch
ARG version
ARG env
RUN echo "Building version: $version for $env"
WORKDIR /var/www
COPY . .
ENV APP_ENV $env
ENV APACHE_DOCUMENT_ROOT /var/www/public
VOLUME /var/www/var
VOLUME /etc/apache2/sites-available
RUN chown -R www-data:www-data var \
    && chown -R www-data:www-data public \
    && a2enmod rewrite \
    && a2dissite 000-default.conf \
    && a2dissite default-ssl.conf \
    && docker-php-ext-install pdo_mysql
WORKDIR /
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN echo "ServerName GeorgiaDanceSport" >> /etc/apache2/apache2.conf
