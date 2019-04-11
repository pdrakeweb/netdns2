#!/bin/bash

export VERSION=1.3.2
export BUILD_NUMBER=acquia2
export TARGET=Net_DNS2-${VERSION}
export ARTIFACT=${TARGET}-${BUILD_NUMBER}.tgz

set -ex

# Set up the proper directory structure
rm -fr ./build
mkdir -p ./build/Net_DNS2-$VERSION
cd build

# Move source files into place.
cp -rv ../Net ./${TARGET}/
cp -rv ../tests ./${TARGET}/
cp ../LICENSE ./${TARGET}
cp ../package.xml ./

# Create the output file
tar -cvzf $ARTIFACT ./*
