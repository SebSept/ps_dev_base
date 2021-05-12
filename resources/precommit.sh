#!/usr/bin/env sh
# this file must be symlinked in .git/hooks with the name "pre-commit"
# it fire composer script "pre-commit" which launch phpstan + phpcsfix
#
# install with `ln -s $(pwd)/precommit.sh .git/hooks/pre-commit`

(cd "./" && echo "start pre-commit hook ..." & exec 'composer' 'run-script' 'pre-commit')