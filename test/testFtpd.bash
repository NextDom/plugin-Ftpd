#!/bin/bash
cd ${TRAVIS_BUILD_DIR}/ressources
python ./ftpd.py test
if [ $? != 0 ]
then
  echo Unable to test daemon
  exit 1
fi
cat ftpd_config_sample.xml | sed "s?/var/www/html/plugins/ftpd/core/class/../../../../log/ftpd_daemon?${TRAVIS_BUILD_DIR}/tmp/ftpd_daemon?" | sed "s?/var/www/html/plugins/ftpd/core/class/../../capture/?${TRAVIS_BUILD_DIR}/tmp/capture/?" | sed "s?/var/www/html/plugins/ftpd/core/class/../../ressources/ftpd.pid?${TRAVIS_BUILD_DIR}/tmp/ftpd.pidn?" > ftpd.xml
cat ftpd.xml
mkdir ../tmp
mkdir ../tmp/capture
python ./ftpd.py start
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
python ./ftpd.py stop
if [ $? != 0 ]
then
  echo Unable to stop daemon
  exit 1
fi
cat ../tmp/ftpd_daemon
if [ `ls -l ../tmp/capture/ip6-localhost/*.png | wc -l` -ne 1 ]
then
  echo File not recieved
  exit 1
fi
exit 0
