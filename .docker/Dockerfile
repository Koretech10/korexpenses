FROM php:8.3-apache

ENV COMPOSER_ALLOW_SUPERUSER=1

ARG DOMAIN_NAME=localhost
ARG APP_SECRET=insertSecretHere
ARG REMEMBER_ME_FOR=2592000
ARG ROOT_PWD=!R00tR00tR00tR00t!
ARG DB_USER=korexpenses_user
ARG DB_PASS=korexpenses_pass
ARG DB_HOSTNAME=mariadb
ARG DB_PORT=3306
ARG DB_NAME=korexpenses
ARG DB_SERVER_VERSION=10.6.17-MariaDB

EXPOSE 80

# Remplacement du script d'entrypoint
COPY start/docker-php-entrypoint /usr/local/bin/docker-php-entrypoint
RUN chmod +x /usr/local/bin/docker-php-entrypoint

# Ajout du script d'initialisation du conteneur
COPY start/start.sh /start.sh
RUN chmod +x /start.sh

# Installation de packages
RUN apt-get update \
 && apt-get upgrade -y \
 && apt-get install -y git unzip zip  zlib1g-dev libicu-dev g++ apt-transport-https gnupg cron \
 && curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash \
 && apt-get install -y symfony-cli \
 && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
 && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Ajout d'extensions PHP
RUN docker-php-ext-configure intl \
 && docker-php-ext-install -j$(nproc) opcache pdo_mysql intl

# Copie des fichiers de configuration
COPY conf/app.ini /usr/local/etc/php/conf.d/app.ini
COPY conf/vhost.conf /etc/apache2/sites-available/000-default.conf

# Activation du module URL Rewrite
RUN a2enmod rewrite

# Clonage des sources
RUN git clone https://github.com/Koretech10/korexpenses.git /app
WORKDIR /app

# Copie des fichiers d'environnement
COPY app/.env /app/.env
COPY app/.env.local /app/.env.local

# Copie du cron
COPY app/korexpenses /etc/cron.d/01_korexpenses