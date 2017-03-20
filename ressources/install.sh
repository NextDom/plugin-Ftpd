#!/bin/bash

# This file is part of Plugin ftpd for jeedom.
#
#set -x  # make sure each command is printed in the terminal
touch /tmp/ftpd_in_progress
echo 0 > /tmp/ftpd_in_progress
echo "Lancement de l'installation/mise à jour des dépendances ftpd"

BASEDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

function apt_install {
  sudo apt-get -y install "$@"
  if [ $? -ne 0 ]; then
    echo "could not install $1 - abort"
    rm /tmp/ftpd_in_progress
    exit 1
  fi
}

echo "Installation des dependances"
apt_install python-pip
echo 33 > /tmp/ftpd_in_progress

echo "Installation des dependances"
apt_install python-lxml
echo 66 > /tmp/ftpd_in_progress

echo "Installation des dependances"
apt_install python-requests
echo 99 > /tmp/ftpd_in_progress

echo 100 > /tmp/ftpd_in_progress
echo "Everything is successfully installed!"
rm /tmp/ftpd_in_progress
