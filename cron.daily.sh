#!/bin/sh

# Se placer dans le dossier du script
ROOTDIR=$(cd `dirname $0` && pwd)
cd $ROOTDIR

# Mise à jour du solde des comptes bancaires
symfony console balance:update

# Génération des opérations mensuelles dont le jour d'occurrence a été atteint
symfony console transaction:monthly:generate

# Nettoyage des fichiers temporaires
rm -r ~/.symfony5/tmp/*