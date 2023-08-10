CREATE DATABASE bank;
CREATE DATABASE address;
CREATE DATABASE FIXED_DEPOSIT;
CREATE DATABASE CREDIT;
CREATE DATABASE codeception;
CREATE DATABASE instantpayment;
CREATE DATABASE tapas;
CREATE DATABASE codeception;
CREATE DATABASE post_address;
CREATE DATABASE cap;
CREATE DATABASE capcodecept;
CREATE DATABASE bamboo;

CREATE USER core;
CREATE USER codeception;
CREATE USER instantpayment;
CREATE USER tapas;
CREATE USER bamboo;

GRANT ALL PRIVILEGES ON *.* TO 'core'@'%' IDENTIFIED BY 'localsecretpassword';
GRANT ALL PRIVILEGES ON *.* TO 'codeception'@'%' IDENTIFIED BY 'codeception';
GRANT ALL PRIVILEGES ON *.* TO 'instantpayment'@'%' IDENTIFIED BY 'localsecretpassword';
GRANT ALL PRIVILEGES ON *.* TO 'tapas'@'%' IDENTIFIED BY 'localsecretpassword';
GRANT ALL PRIVILEGES ON *.* TO 'bamboo'@'%' IDENTIFIED BY 'localsecretpassword';

FLUSH PRIVILEGES;