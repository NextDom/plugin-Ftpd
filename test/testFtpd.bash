#!/bin/bash
if [ -z "${TRAVIS_BUILD_DIR}" ]
then
  cd ressources
else
  cd ${TRAVIS_BUILD_DIR}/ressources
fi
ls -l
python ./Ftpd.py test
if [ $? != 0 ]
then
  echo Unable to test daemon
  exit 1
fi
cat Ftpd_config_sample.xml | sed "s?/var/www/html/plugins/Ftpd/core/class/../../../../log/Ftpd_daemon?${TRAVIS_BUILD_DIR}/tmp/Ftpd_daemon?" | sed "s?/var/www/html/plugins/Ftpd/core/class/../../capture/?${TRAVIS_BUILD_DIR}/tmp/capture/?" | sed "s?/var/www/html/plugins/Ftpd/core/class/../../ressources/Ftpd.pid?${TRAVIS_BUILD_DIR}/tmp/Ftpd.pidn?" > Ftpd.xml
cat Ftpd.xml
mkdir ../tmp
mkdir ../tmp/capture
python ./Ftpd.py start
if [ $? != 0 ]
then
  echo Unable to start daemon
  exit 1
fi
sleep 60
python ./ftp.py
if [ $? != 0 ]
then
  exit 1
  echo Unable to start client
fi
python ./Ftpd.py stop
if [ $? != 0 ]
then
  echo Unable to stop daemon
  exit 1
fi
cat ../tmp/Ftpd_daemon
if [ `ls -l ../tmp/capture/ip6-localhost/*.png | wc -l` -ne 1 ]
then
  echo File not recieved
  exit 1
fi
exit 0
