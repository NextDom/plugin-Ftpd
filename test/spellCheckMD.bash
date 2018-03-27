#!/bin/bash
cd ${TRAVIS_BUILD_DIR}
for file in *.md docs/fr_FR/*.md
do
  cat $file | aspell --personal=${TRAVIS_BUILD_DIR}/.aspell.fr.pws --lang=fr --encoding=utf-8 list
done | sort -u
cat mdl *.md docs/fr_FR/*.md | aspell --personal=${TRAVIS_BUILD_DIR}/.aspell.fr.pws --lang=fr --encoding=utf-8 list >/dev/null
