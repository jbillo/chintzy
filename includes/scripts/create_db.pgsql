DROP DATABASE IF EXISTS chintzydb;
DROP USER IF EXISTS chintzy;

CREATE USER chintzy WITH PASSWORD 'ch1ntzyCMS!';
CREATE DATABASE chintzydb;
GRANT ALL PRIVILEGES ON DATABASE chintzydb to chintzy;