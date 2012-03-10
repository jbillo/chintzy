#!/bin/bash

if [ ! `whoami` == "postgres" ]
then
    echo "You must be the postgres user to execute this script."
    exit 1
fi

if [ -z $1 ]
then
    echo "Usage: $0 dbname"
    echo "Default database name is chintzydb"
    exit 1
fi

echo "postgres user detected, now creating home page."

psql -d $1 -f create_home_page.pgsql

exit 0