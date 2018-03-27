#!/bin/bash
cd ${TRAVIS_BUILD_DIR}
CODE=0;
for file in *.md docs/fr_FR/*.md
do
  cat $file | aspell --personal=${TRAVIS_BUILD_DIR}/.aspell.fr.pws --lang=fr --encoding=utf-8 list
  CODE=`expr $? + $CODE`
done | sort -u
exit $CODE
