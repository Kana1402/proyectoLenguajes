FROM php:8.2-apache

WORKDIR /var/www/html

RUN a2enmod rewrite
RUN docker-php-ext-install pdo pdo_mysql mysqli

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'.PHP_EOL; } else { echo 'Installer corrupt'.PHP_EOL; unlink('composer-setup.php'); exit(1); }" && \
php composer-setup.php && \
php -r "unlink('composer-setup.php');"  && \
mv composer.phar /usr/local/bin/composer

COPY ./negocio/composer.json .
COPY ./negocio/.htaccess .

RUN apt-get update && apt-get install -y zip
RUN apt-get update && apt-get install -y git

RUN composer install

RUN composer dump-autoload

RUN sed -i 's/DocumentRoot.*$/DocumentRoot \/var\/www\/html\/public/' /etc/apache2/sites-enabled/000-default.conf