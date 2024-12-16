CREATE DATABASE dolphin_crm;
USE dolphin_crm;

CREATE TABLE Users (
	id INT PRIMARY KEY AUTO_INCREMENT,
	firstname VARCHAR(50),
	lastname VARCHAR(50),
	passwrd VARCHAR(255), -- Increased length to accommodate hashed passwords
	email VARCHAR(30),
	user_role ENUM("Admin", "Member"),
	created_at DATETIME
);

-- Insert user with hashed password
INSERT INTO Users (id, passwrd, email, user_role) 
VALUES (1, '$2y$10$tYMNLOE.Y6gW4zYOP4icd.sQOblsVCtKJJjvB0jQ8xlhPc1GM.nsC', 'admin@project2.com', 'Admin');

CREATE TABLE contacts (
	id INT PRIMARY KEY AUTO_INCREMENT,
	title VARCHAR(70),
	firstname VARCHAR(50),
	lastname VARCHAR(50),
	email VARCHAR(30),
	telephone VARCHAR(15),
	company VARCHAR(25),
	contact_type ENUM('Sales Lead', 'Support'),
	assigned_to INT,
	created_by INT,
	created_at DATETIME,
	updated_at DATETIME,
	FOREIGN KEY (assigned_to) REFERENCES Users(id),
	FOREIGN KEY (created_by) REFERENCES Users(id)
);

CREATE TABLE notes (
	note_id INT PRIMARY KEY AUTO_INCREMENT,
	contact_id INT,
	message VARCHAR(255),
	created_by INT,
	created_at DATETIME,
    FOREIGN KEY (created_by) REFERENCES Users(id)
);
