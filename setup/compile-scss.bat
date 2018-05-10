@echo off
pushd .
cd %~dp0
cmd /c "scss --unix-newlines --style compressed --no-cache --update ../public/scss/:../public/css/"
popd
