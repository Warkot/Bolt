CREATE TABLE IF NOT EXISTS Tests (
		id     INT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
		rId    INT UNSIGNED NOT NULL,
		pass   INT                   DEFAULT 0,
		skip   INT                   DEFAULT 0,
		fail   INT                   DEFAULT 0,
		status VARCHAR(20)  NOT NULL,
		INDEX (id, rId),
		PRIMARY KEY (id, rId),
		FOREIGN KEY (rId) REFERENCES Releases (id)
)
		ENGINE = InnoDB
