-- All the users
CREATE TABLE users (
	userID INT AUTO_INCREMENT,
	vms_email varchar(255) NOT NULL,
	vms_apiPassword varchar(255) NOT NULL,
	userPassword varchar(255) NOT NULL,
	name varchar(255) NOT NULL,
	userType ENUM('U', 'A') NOT NULL,

	PRIMARY KEY (userID)
);

-- All the phone numbers from a user
CREATE TABLE dids(
	didID INT AUTO_INCREMENT,
	ownerID INT NOT NULL, 
	did VARCHAR(12) NOT NULL,

	PRIMARY KEY (didID),
	FOREIGN KEY (ownerID) REFERENCES users(userID)
);

-- All of the contacts owned by a voipSMS user
CREATE TABLE contacts(
	contactID INT AUTO_INCREMENT,
	ownerID INT NOT NULL,
	firstName VARCHAR(128),
	lastName VARCHAR(128),
	did VARCHAR(12) NOT NULL,
	notes VARCHAR(1024),

	PRIMARY KEY (contactID),
	FOREIGN KEY (ownerID) REFERENCES users(userID)
);
