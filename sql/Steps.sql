CREATE TABLE IF NOT EXISTS Steps (
		id           INT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
		rId          INT UNSIGNED NOT NULL,
		command      VARCHAR(100),
		errorMessage VARCHAR(250),
		status       VARCHAR(20)  NOT NULL,
		INDEX (id, rId),
		PRIMARY KEY (id, rId),
		FOREIGN KEY (rId) REFERENCES Releases (id)
)
		ENGINE = InnoDB
