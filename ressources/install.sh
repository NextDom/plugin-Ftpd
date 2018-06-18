#!/bin/bash

export DEBIAN_FRONTEND=noninteractive
touch /tmp/Ftpd_in_progress
echo 0 > /tmp/Ftpd_in_progress
echo "Install dependances Ftpd"

function apt_install {
  apt-get -y install "$@" >/dev/null
  if [ $? -ne 0 ]; then
    echo 100 > /tmp/Ftpd_in_progress
    echo "Could not install $1"
    rm /tmp/Ftpd_in_progress
    exit 1
  fi
}

echo 10 > /tmp/Ftpd_in_progress
echo "Test the necessity of dependencies"
BASEDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
cd $BASEDIR
ffmpeg -h 1>/dev/null 2>&1
if [ $? -ne 0 ]
then
    echo "Installation of ffmpeg"
    apt_install ffmpeg
    echo 20 > /tmp/Ftpd_in_progress
else
    echo 20 > /tmp/Ftpd_in_progress
    echo "No necessity of dependencies"
fi

python ./Ftpd.py test 2>/dev/null 
if [ $? -ne 0 ]
then
    echo "Installation of python-daemon"
    apt_install python-daemon
    echo 40 > /tmp/Ftpd_in_progress

    echo "Installation of python-lxml"
    apt_install python-lxml
    echo 60 > /tmp/Ftpd_in_progress

    echo "Installation of python-requests"
    apt_install python-requests
    echo 80 > /tmp/Ftpd_in_progress
    
    python ./Ftpd.py test
    if [ $? -ne 0 ]
    then
        echo "Some dependencies missing"
    fi
else
    echo 99 > /tmp/Ftpd_in_progress
    echo "No necessity of dependencies"
fi

echo 100 > /tmp/Ftpd_in_progress
echo "Everything is successfully installed!"
rm /tmp/Ftpd_in_progress
