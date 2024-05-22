# KorExpenses

KorExpenses est une application web auto-hébergée qui permet de gérer ses dépenses personnelles sur plusieurs comptes
bancaires

## Prérequis
- Serveur Web (Apache, nginx...)
- PHP 8.3 ou ultérieur
- Extensions PHP suivantes : ctype, iconv, PCRE, Session, SimpleXML, Tokenizer
- [Composer](https://getcomposer.org/download/)
- [Symfony CLI](https://symfony.com/download)
- Système de gestion de base de données (MariaDB, MySQL, PostgreSQL, SQLite...)

## Première installation
- Préparer le serveur web pour accueillir une application PHP
- Cloner le dépôt Git dans le dossier web racine
  - Via SSH (préféré)
    ```shell
    git clone git@github.com:Koretech10/korexpenses.git .
    ```
  - Via HTTPS
    ```shell
    git clone https://github.com/Koretech10/korexpenses.git .
    ```
- Dupliquer le fichier .env pour créer le fichier de variables d'environnement et renseigner les variables
```
cp .env .env.prod.local
```
- Installer les dépendances
```shell
symfony composer install --no-dev --optimize-autoloader
```
- Tester la migration de la base de données
```shell
symfony console doctrine:migrations:migrate --dry-run
```
- Si le dry-run réussi, migrer la base de données
```shell
symfony console doctrine:migrations:migrate
```
- Vider le cache de l'application
```shell
symfony console cache:clear
```
- Initialiser l'utilisateur root pour vous connecter à l'application
```shell
symfony console root:manage
```
- Planifier le lancement régulier de la mise à jour des soldes (via cron)
```shell
crontab -e
```
```
# Lancement de la commande balance:update tous les jours à minuit
0 0 * * * symfony console balance:update 
```

## Mise à jour
- Depuis le dossier racine web, récupérer la dernière version du dépôt Git
```shell
git reset --hard
git pull
```
- Dupliquer le fichier .env pour créer le fichier de variables d'environnement et renseigner les variables
```
mv .env.prod.local .env.prod.local.bkp
cp .env .env.prod.local
```
- Installer les dépendances
```shell
symfony composer install --no-dev --optimize-autoloader
```
- Tester la migration de la base de données
```shell
symfony console doctrine:migrations:migrate --dry-run
```
- Si le dry-run réussi, migrer la base de données
```shell
symfony console doctrine:migrations:migrate
```
- Vider le cache de l'application
```shell
symfony console cache:clear
```