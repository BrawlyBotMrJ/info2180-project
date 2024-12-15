CREATE database dolphin_crm;
use dolphin_crm;

Create table Users (
	id INT PRIMARY KEY auto_increment,
	firstname varchar(50),
	lastname varchar(50),
	passwrd varchar(20),
	email varchar(30),
	user_role ENUM("Admin", "Member"),
	created_at datetime
);

Insert into users (id,passwrd,email,user_role) values (1, "password123","admin@project2.com","Admin");

create table contacts (
	id INT PRIMARY KEY auto_increment,
	title varchar(70),
	firstname varchar(50),
	lastname varchar(50),
	email varchar(30),
	telephone varchar(15),
	company varchar(25),
	contact_type ENUM('Sales Lead', 'Support'),
	assigned_to int,
	created_by int,
	created_at datetime,
	updated_at datetime,
	FOREIGN KEY (assigned_to) REFERENCES Users(id),
	FOREIGN KEY (created_by) REFERENCES Users(id)
);

create table notes(
	id int primary key auto_increment,
	contact_id int,
	message varchar(255),
	created_by int,
	created_at datetime,
    FOREIGN KEY (created_by) REFERENCES Users(id)
);

