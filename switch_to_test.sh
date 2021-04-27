#!/usr/bin/env bash
echo Switching to test env!
mv package-lock.json prod-package-lock.json
mv package.json prod-package.json
mv test-package-lock.json package-lock.json
mv test-package.json package.json
rm -rf node_modules
echo Switching complete!
echo Please use Nodejs v14