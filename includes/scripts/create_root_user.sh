#!/bin/bash

if [ ! `whoami` == "postgres" ]
then
    echo "You must be the postgres user to execute this script."
    exit 1
fi

if [ ! $# == 3 ]
then
    echo "Usage: $0 dbname root_user_name root_password"
    echo "Default dbname is chintzydb"
    exit 1
fi

echo "postgres user detected. Now creating root user $2 in database $1"

PASSWORD=`echo -n $3 | sha256sum | cut -d ' ' -f 1`

# Delete existing user account
psql -d $1 -c "DELETE FROM user_roles WHERE user_id = (SELECT id FROM users WHERE email = '$2');"
psql -d $1 -c "DELETE FROM user_cookies WHERE user_id = (SELECT id FROM users WHERE email = '$2');"
psql -d $1 -c "DELETE FROM user_recovery WHERE user_id = (SELECT id FROM users WHERE email = '$2');"
psql -d $1 -c "DELETE FROM users WHERE email = '$2';"

# Insert new user account
psql -d $1 -c "INSERT INTO users (email, password, created_on) VALUES ('$2', '${PASSWORD}', CURRENT_TIMESTAMP);"

# Insert permissions and roles, do not output errors if they exist
psql -d $1 -q -f create_root_user.pgsql 2> /dev/null

# Update permissions for root account
psql -d $1 -q -c "INSERT INTO role_permissions (role_id, permission_id) VALUES ((SELECT id FROM roles WHERE name = 'Site Administrator (root)'), (SELECT id FROM permissions WHERE name = '$2'));" 2> /dev/null
psql -d $1 -q -c "INSERT INTO user_roles (user_id, role_id) VALUES ((SELECT id FROM users WHERE email = '$2'), (SELECT id FROM roles WHERE name = 'Site Administrator (root)'));" 2> /dev/null

