#!/bin/bash

if [ ! `whoami` == "postgres" ]
then
    echo "You must be the postgres user to execute this script."
    exit
fi

if [ ! $# == 5 ]
then
    echo "Usage: $0 dbname dbuser dbpass root_user root_pass"
    exit
fi

echo "postgres user detected, now running all create scripts"

./create_db.sh $1 $2 $3 && \
 ./create_schema.sh $1 && \
 ./create_root_user.sh $1 $4 $5 && \
 ./create_home_page.sh $1
 
 