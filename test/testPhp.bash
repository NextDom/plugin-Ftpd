#!/bin/bash
cd ${TRAVIS_BUILD_DIR}
cd core/class/
for file in *.class.php
do
  mv $file $file.sav
  grep -v "require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';" $file.sav >$file
done
cd ${TRAVIS_BUILD_DIR}/test
wget https://github.com/jeedom/core/blob/beta/core/class/*.class.php
mv eqLogic.class.php eqLogic.class.php.sav;grep -v "require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';" eqLogic.class.php.sav >eqLogic.class.php
php test_plugin.php
