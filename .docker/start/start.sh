#!/bin/sh

# Remplacement des variables dans la conf du vhost
sed -i "s/%DOMAIN_NAME%/$DOMAIN_NAME/g" /etc/apache2/sites-available/000-default.conf

# Remplacement des variables dans la conf de l'application
sed -i "s/%APP_SECRET%/$APP_SECRET/g" /app/.env.local
sed -i "s/%REMEMBER_ME%/$REMEMBER_ME/g" /app/.env.local
sed -i "s/%DB_USER%/$DB_USER/g" /app/.env.local
sed -i "s/%DB_PASS%/$DB_PASS/g" /app/.env.local
sed -i "s/%DB_HOSTNAME%/$DB_HOSTNAME/g" /app/.env.local
sed -i "s/%DB_PORT%/$DB_PORT/g" /app/.env.local
sed -i "s/%DB_NAME%/$DB_NAME/g" /app/.env.local
sed -i "s/%DB_SERVER_VERSION%/$DB_SERVER_VERSION/g" /app/.env.local

# Initialisation de l'application
symfony composer install --no-dev --optimize-autoloader
symfony console doctrine:migrations:migrate --no-interaction
symfony console cache:clear
symfony console root:manage -c "$ROOT_PWD"

# Changement du propriétaire de /app
chown -R www-data: /app

# Exécuter le script cron.daily
chmod +x /app/cron.daily.sh
/app/cron.daily.sh