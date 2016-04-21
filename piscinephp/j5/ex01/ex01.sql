CREATE TABLE ft_table (
	id  INT NOT NULL AUTO_INCREMENT,
	login VARCHAR(255) DEFAULT 'toto' NOT NULL,
	groupe ENUM('staff', 'student', 'other') NOT NULL,
	date_de_creation DATE NOT NULL,
	PRIMARY KEY (id)
)
ENGINE=INNODB;
