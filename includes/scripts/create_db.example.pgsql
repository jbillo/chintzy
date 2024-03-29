DROP DATABASE IF EXISTS __DBNAME__;
DROP USER IF EXISTS __DBUSER__;

CREATE USER __DBUSER__ WITH PASSWORD '__DBPASS__';
CREATE DATABASE __DBNAME__;
GRANT ALL PRIVILEGES ON DATABASE __DBNAME__ to __DBUSER__;