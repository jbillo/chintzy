#!/bin/bash
TMPDIR=/tmp

if [ ! `whoami` == "postgres" ]
then
    echo "You must be the postgres user to execute this script."
    exit 1
fi

if [ ! $# == 3 ]
then
    echo "Usage: $0 dbname dbuser dbpass"
    exit 1
fi

echo "postgres user detected, now beginning database creation"

if [ -f $TMPDIR/create_db.pgsql ]
then
    rm -rf $TMPDIR/create_db.pgsql
fi

cp create_db.example.pgsql $TMPDIR/create_db.pgsql
sed -i "s/__DBNAME__/$1/g" $TMPDIR/create_db.pgsql
sed -i "s/__DBUSER__/$2/g" $TMPDIR/create_db.pgsql
sed -i "s/__DBPASS__/$3/g" $TMPDIR/create_db.pgsql 
psql -f $TMPDIR/create_db.pgsql
rm -rf $TMPDIR/create_db.pgsql

exit 0