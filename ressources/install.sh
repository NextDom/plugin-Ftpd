#!/bin/bash

touch /tmp/ftpd_in_progress
echo 0 > /tmp/ftpd_in_progress
echo "Install dependances ftpd"

function apt_install {
  apt-get -y install "$@" >/dev/null
  if [ $? -ne 0 ]; then
	echo 100 > /tmp/ftpd_in_progress
    echo "Could not install $1"
    rm /tmp/ftpd_in_progress
    exit 1
  fi
}

echo 10 > /tmp/ftpd_in_progress
echo "Test the necessity of dependencies"
BASEDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
cd $BASEDIR
python ./ftpd.py test 1>/dev/null 
if [ $? -ne 0 ]
then
	echo "Installation of python-pip"
	apt_install python-pip
	echo 33 > /tmp/ftpd_in_progress

	echo "Installation of python-lxml"
	apt_install python-lxml
	echo 66 > /tmp/ftpd_in_progress

	echo "Installation of python-requests"
	apt_install python-requests
	echo 99 > /tmp/ftpd_in_progress
else
	echo 99 > /tmp/ftpd_in_progress
	echo "No necessity of dependencies"
fi

echo 100 > /tmp/ftpd_in_progress
echo "Everything is successfully installed!"
rm /tmp/ftpd_in_progress
