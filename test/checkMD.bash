#!/bin/bash
cd ${TRAVIS_BUILD_DIR}
gem install mdl
CODE=0;

ls *.md docs/fr_FR/*.md | sed 's/.md//' | while read file
do
  if [ -e $file.rb ]
  then
    mdl --config $file.rb $file.md
    CODE=`expr $? + $CODE`
  else
    mdl $file.md
    CODE=`expr $? + $CODE`
  fi
done
exit $CODE
