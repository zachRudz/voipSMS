-- Setup script --
-- This script will be run to setup the database before the application can be used.
-- NOTE: Make sure to edit the bottom two lines before running!

-- Creating the database --
CREATE DATABASE	voipSMS;
USE voipSMS;

-- Creating the tables --
-- All the users
CREATE TABLE users (
	userID INT AUTO_INCREMENT,
	vms_email varchar(255) NOT NULL,
	vms_apiPassword varchar(255) NOT NULL,
	userPassword varchar(255) NOT NULL,
	userType ENUM('U', 'A') NOT NULL,

	PRIMARY KEY (userID)
);

-- All the phone numbers from a user
CREATE TABLE dids(
	didID INT AUTO_INCREMENT,
	ownerID INT NOT NULL, 
	did VARCHAR(15) NOT NULL,

	PRIMARY KEY (didID),
	FOREIGN KEY (ownerID) REFERENCES users(userID)
);

-- All of the contacts owned by a voipSMS user
CREATE TABLE contacts(
	contactID INT AUTO_INCREMENT,
	ownerID INT NOT NULL,
	firstName VARCHAR(128),
	lastName VARCHAR(128),
	did VARCHAR(15) NOT NULL,
	notes VARCHAR(1024),

	PRIMARY KEY (contactID),
	FOREIGN KEY (ownerID) REFERENCES users(userID)
);


-- Creating a user for the application to access the database --
-- NOTE: EDIT THESE TWO LINES BEFORE RUNNING! There are 2 variables to change in 3 locations:
--	INSERT_DATABASE_ADDRESS_HERE
--	INSERT_PASSWORD_HERE
CREATE USER `voipSMS_user`@`INSERT_DATABASE_ADDRESS_HERE` IDENTIFIED BY 'INSERT_PASSWORD_HERE';
GRANT DELETE,INSERT,INSERT,SELECT,UPDATE ON `voipSMS` . * TO `voipSMS_user`@`INSERT_DATABASE_ADDRESS_HERE`;

FLUSH PRIVILEGES;
