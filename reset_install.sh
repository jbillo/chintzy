#!/bin/bash

# Check for db.inc.php and reset installation if required

# rm db.inc.php
sudo chown -R jbillo:www-data *
chgrp -R www-data cache log
chmod -R g+w cache log
rm -rf cache/*
rm -rf log/*
chgrp -R www-data libs/htmlpurifier/standalone/HTMLPurifier/DefinitionCache/Serializer
chmod -R g+w libs/htmlpurifier/standalone/HTMLPurifier/DefinitionCache/Serializer

echo Chintzy has been reset!
