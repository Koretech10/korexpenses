#!/bin/sh

# Horodatage début
echo "$(date) - Début des tâches quotidiennes"

# Se placer dans le dossier du script
ROOTDIR=$(cd `dirname $0` && pwd)
cd $ROOTDIR

# Génération des opérations mensuelles dont le jour d'occurrence a été atteint
symfony console transaction:monthly:generate

# Mise à jour du solde des comptes bancaires
symfony console balance:update

# Nettoyage des fichiers temporaires
rm -r ~/.symfony5/tmp/*

# Horodatage fin
echo "$(date) - Fin des taches quotidiennes"