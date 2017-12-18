#!/bin/bash

## This script initializes the sql/dbinfo.php file, which contains information about the 
## connection to the SQL server. 

## Usage: ./setup.sh
## Follow the prompts, which will ask information about the DB user

# Getting the DB user's info
echo "Please answer the following questions about your MySQL database."

echo -n "What is the address/hostname of the database? "
read dbHost

echo -n "What is the name of the database? "
read dbName

echo -n "What is the username of the DB user? "
read dbUser 

echo -n "What is the password of the DB user? "
read -s dbPass
echo


# Buidling the php file
cat >> sql/dbinfo.php << EOF
<?php
	// Connects to the database, and returns a PDO object
	function connectToDB() {
		\$db = new PDO("mysql:host=$dbHost;dbname={$dbName}", 
		$dbUser, $dbPass);
		
		// Enable the printing of errors when things bork
		//\$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		return \$db;
	}
?>
EOF
