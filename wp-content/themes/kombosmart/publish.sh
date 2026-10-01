#!/bin/bash
theme="kombosmart"
date=$(date '+%Y-%m-%d-%H-%M-%S')

echo Generating...
#gulp deploy
cd ..
rsync -av --exclude=""$theme"/node_modules" --exclude=""$theme"/package.json" --exclude=""$theme"/package-lock.json" --exclude=""$theme"/publish.sh" --exclude=""$theme"/publish.js" --exclude=""$theme"/publish.sh" --exclude=""$theme"/assets/frontend/src" $theme deploy
clear
cd deploy
zip -r $theme-$date.zip $theme
rm -r $theme

cd ..
path=$(readlink -f deploy)

echo $theme-$date.zip
echo file:///$path
echo Done! Have fun...