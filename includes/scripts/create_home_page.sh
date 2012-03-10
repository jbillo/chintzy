#!/bin/bash

if [ ! `whoami` == "postgres" ]
then
    echo "You must be the postgres user to execute this script."
    exit
fi

if [ -z $1 ]
then
    echo "Usage: $0 dbname"
    echo "Default database name is chintzydb"
    exit
fi

echo "postgres user detected, now loading schema."