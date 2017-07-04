#!/bin/bash

export DEBIAN_FRONTEND=noninteractive
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
python ./ftpd.py test 2>/dev/null 
if [ $? -ne 0 ]
then
	echo "Installation of python-daemon"
	apt_install python-daemon
	echo 30 > /tmp/ftpd_in_progress

	echo "Installation of python-lxml"
	apt_install python-lxml
	echo 60 > /tmp/ftpd_in_progress

	echo "Installation of python-requests"
	apt_install python-requests
	echo 90 > /tmp/ftpd_in_progress
	

	python ./ftpd.py test
	if [ $? -ne 0 ]
	then
		echo "Some dependencies missing"
	fi
else
	echo 99 > /tmp/ftpd_in_progress
	echo "No necessity of dependencies"
fi

echo 100 > /tmp/ftpd_in_progress
echo "Everything is successfully installed!"
rm /tmp/ftpd_in_progress
