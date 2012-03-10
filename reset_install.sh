#!/bin/bash

USERNAME=jbillo
WEBGROUP=www-data

sudo chown -R ${USERNAME}:${WEBGROUP} *
chgrp -R ${WEBGROUP} cache log
chmod -R g+w cache log
rm -rf cache/*
rm -rf log/*
chgrp -R ${WEBGROUP} libs/htmlpurifier/standalone/HTMLPurifier/DefinitionCache/Serializer
chmod -R g+w libs/htmlpurifier/standalone/HTMLPurifier/DefinitionCache/Serializer

echo Chintzy has been reset!
